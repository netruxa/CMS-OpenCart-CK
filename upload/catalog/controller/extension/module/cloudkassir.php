<?php

/**
 * Class ControllerExtensionModuleCloudKassir
 *
 * @property Language                        $language
 * @property \Cart\Currency                  $currency
 * @property Config                          $config
 * @property Url                             $url
 * @property Loader                          $load
 * @property Session                         $session
 * @property Request                         $request
 * @property Response                        $response
 * @property Log                             $log
 *
 * @property ModelCheckoutOrder              $model_checkout_order
 * @property ModelExtensionModuleCloudKassir model_extension_module_cloudkassir
 */
class ControllerExtensionModuleCloudKassir extends Controller {
	const RECEIPT_TYPE_INCOME = 'Income';
	const RECEIPT_TYPE_INCOME_RETURN = 'IncomeReturn';

	/** @var  resource */
	private $curl;

	/**
	 * Trigger after change order status
	 *
	 * @param $route
	 * @param $args
	 * @param $output
	 */
	public function changeOrderStatus($route, $args, $output) {
		if (!$this->config->get('module_cloudkassir_status')) {
			return;
		}

		if (count($args) < 2) {
			return;
		}

		$order_id  = $args[0];
		$status_id = $args[1];

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		if (!$order_info ||
			!in_array($order_info['payment_code'], $this->config->get('module_cloudkassir_enabled_for_payments'))
		) {
			return;
		}

		if (in_array($status_id, $this->config->get('module_cloudkassir_order_status_for_pay'))) {
			$this->requestOrderReceipt($order_info, self::RECEIPT_TYPE_INCOME);
		} elseif (in_array($status_id, $this->config->get('module_cloudkassir_order_status_for_refund'))) {
			$this->requestOrderReceipt($order_info, self::RECEIPT_TYPE_INCOME_RETURN);
		}
	}

	/**
	 * @param int    $order_info
	 * @param string $type
	 * @return bool
	 */
	public function requestOrderReceipt($order_info, $type) {
		$this->load->model('extension/module/cloudkassir');
		if ($this->model_extension_module_cloudkassir->isOrderHasRequest($order_info['order_id'], $type)) {
			return true;
		}

		if ($type == self::RECEIPT_TYPE_INCOME_RETURN
			&& !$this->model_extension_module_cloudkassir->isOrderHasRequest($order_info['order_id'],
				self::RECEIPT_TYPE_INCOME)
		) {
			//If don't generate Income receipt don't generate return receipt
			return true;
		}

		$response = $this->makeRequest('kkt/receipt', array(
			'Inn'             => $this->config->get('module_cloudkassir_inn'),
			'Type'            => $type,
			'CustomerReceipt' => $this->getReceiptData($order_info),
			'InvoiceId'       => $order_info['order_id'],
			'AccountId'       => $order_info['customer_id'],
		));
		if ($response !== false) {
			$this->saveOrderRequest($order_info['order_id'], $type);
		}

		return $response;
	}

	private function getReceiptData($order_info) {
		$receiptData = array(
			'Items'          => array(),
			'taxationSystem' => $this->config->get('module_cloudkassir_taxation_system'),
			'email'          => $order_info['email'],
			'phone'          => $order_info['telephone']
		);

		$order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);

		$vat = $this->config->get('module_cloudkassir_vat');
		foreach ($order_products as $order_product) {
			$item = array(
				'label'    => trim($order_product['name'] . ' ' . $order_product['model']),
				'price'    => $order_product['price'],
				'quantity' => $order_product['quantity'],
				'amount'   => $order_product['total'],
			);
			if (!empty($vat)) {
				$item['vat'] = $vat;
			}
			$receiptData['Items'][] = $item;
		}

		// Order Totals
		$order_totals = array();
		foreach ($this->model_checkout_order->getOrderTotals($order_info['order_id']) as $row) {
			$order_totals[$row['code']] = $row;
		};

		if (isset($order_totals['shipping']) && $order_totals['shipping']['value'] > 0) {
			$item = array(
				'label'    => $order_totals['shipping']['title'],
				'price'    => $order_totals['shipping']['value'],
				'quantity' => 1,
				'amount'   => $order_totals['shipping']['value']
			);

			$vat = $this->config->get('module_cloudkassir_vat_delivery');
			if (!empty($vat)) {
				$item['vat'] = $vat;
			}
			$receiptData['Items'][] = $item;
		}

		return $receiptData;
	}

	/**
	 * @param $order_id
	 * @param $request
	 */
	private function saveOrderRequest($order_id, $request) {
		$data = array(
			'order_id' => $order_id,
			'request'  => $request,
		);

		$this->load->model('extension/module/cloudkassir');
		$this->model_extension_module_cloudkassir->addRequestHistory($data);
	}

	/**
	 * @param string $location
	 * @param array  $request
	 * @return bool|array
	 */
	private function makeRequest($location, $request = array()) {
		if (!$this->curl) {
			$auth       = $this->config->get('module_cloudkassir_public_id') . ':' . $this->config->get('module_cloudkassir_secret_key');
			$this->curl = curl_init();
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($this->curl, CURLOPT_USERPWD, $auth);
		}

		curl_setopt($this->curl, CURLOPT_URL, 'https://api.cloudpayments.ru/' . $location);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
			"content-type: application/json"
		));
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($request));

		$response = curl_exec($this->curl);
		if ($response === false || curl_getinfo($this->curl, CURLINFO_HTTP_CODE) != 200) {
			$this->log->write('CloudKassir Failed API request' .
				' Location: ' . $location .
				' Request: ' . print_r($request, true) .
				' HTTP Code: ' . curl_getinfo($this->curl, CURLINFO_HTTP_CODE) .
				' Error: ' . curl_error($this->curl)
			);

			return false;
		}
		$response = json_decode($response, true);
		if (!isset($response['Success']) || !$response['Success']) {
			$this->log->write('CloudKassir Failed API request' .
				' Location: ' . $location .
				' Request: ' . print_r($request, true) .
				' HTTP Code: ' . curl_getinfo($this->curl, CURLINFO_HTTP_CODE) .
				' Error: ' . curl_error($this->curl)
			);

			return false;
		}

		return $response;
	}
}