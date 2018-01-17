<?php

/**
 * Class ControllerExtensionModuleCloudKassir
 *
 * @property Loader                          $load
 * @property Document                        $document
 * @property ModelSettingSetting             $model_setting_setting
 * @property ModelSettingEvent               $model_setting_event
 * @property ModelSettingExtension           $model_setting_extension
 * @property Request                         $request
 * @property Response                        $response
 * @property Session                         $session
 * @property Language                        $language
 * @property Url                             $url
 * @property Config                          $config
 * @property ModelLocalisationOrderStatus    $model_localisation_order_status
 * @property ModelExtensionModuleCloudKassir $model_extension_module_cloudkassir
 * @property Cart\User                       $user
 */
class ControllerExtensionModuleCloudKassir extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/cloudkassir');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_cloudkassir', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension',
				'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data = array(
			'error_public_id'  => '',
			'error_secret_key' => '',
			'error_inn'        => '',
		);
		foreach ($this->error as $f => $v) {
			$data['error_' . $f] = $v;
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension',
				'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/cloudkassir',
				'user_token=' . $this->session->data['user_token'],
				true)
		);

		$data['action'] = $this->url->link('extension/module/cloudkassir',
			'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension',
			'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$fields = array(
			'module_cloudkassir_status',
			'module_cloudkassir_public_id',
			'module_cloudkassir_secret_key',
			'module_cloudkassir_inn',
			'module_cloudkassir_taxation_system',
			'module_cloudkassir_vat',
			'module_cloudkassir_vat_delivery',
			'module_cloudkassir_order_status_for_pay',
			'module_cloudkassir_order_status_for_refund',
			'module_cloudkassir_enabled_for_payments',
		);

		foreach ($fields as $f) {
			if (isset($this->request->post[$f])) {
				$data[$f] = $this->request->post[$f];
			} else {
				$data[$f] = $this->config->get($f);
			}
		}
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->load->model('setting/extension');
		$payments_codes   = $this->model_setting_extension->getInstalled('payment');
		$data['payments'] = $this->preparePayments($payments_codes);

		$data['taxation_systems'] = array();
		for ($i = 0; $i <= 5; $i++) {
			$data['taxation_systems'][$i] = $this->language->get('text_taxation_system_' . $i);
		}

		$data['vat_values'] = array();
		foreach (array('18', '10', '0', '110', '118') as $vat) {
			$data['vat_values'][$vat] = $this->language->get('text_vat_' . $vat);
		}
//		$data['text_vat_none'] = $this->language->get('text_vat_none');

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/cloudkassir', $data));
	}

	/**
	 * @param $payments_codes
	 * @return array
	 */
	private function preparePayments($payments_codes) {
		$payments = array();
		foreach ($payments_codes as $extension) {
			$this->load->language('extension/payment/' . $extension, 'extension');
			$payments[] = array(
				'name' => $this->language->get('extension')->get('heading_title'),
				'code' => $extension
			);
		}

		return $payments;
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/cloudkassir')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$required_fields = array(
			'public_id',
			'secret_key',
			'inn'
		);

		foreach ($required_fields as $f) {
			if (!$this->request->post['module_cloudkassir_' . $f]) {
				$this->error[$f] = $this->language->get('error_' . $f);
			}
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('setting/event');
		$this->model_setting_event->addEvent('cloudkassir', 'catalog/model/checkout/order/addOrderHistory/after',
			'extension/module/cloudkassir/changeOrderStatus');
		$this->load->model('extension/module/cloudkassir');
		$this->model_extension_module_cloudkassir->install();
	}

	public function uninstall() {
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('cloudkassir');
		$this->load->model('extension/module/cloudkassir');
		$this->model_extension_module_cloudkassir->uninstall();
	}
}