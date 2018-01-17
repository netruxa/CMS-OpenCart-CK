<?php

/**
 * Class ModelExtensionModuleCloudKassir
 *
 * @property Loader    $load
 * @property Config    $config
 * @property Language  $language
 * @property DB\MySQLi $db
 */
class ModelExtensionModuleCloudKassir extends Model {

	/**
	 * @param $order_id
	 * @param $request
	 * @return bool
	 */
	public function isOrderHasRequest($order_id, $request) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cloudkassir_request_history` WHERE order_id = '" . $order_id . "' AND request = '" . $request . "' LIMIT 1");

		return $query->num_rows > 0;
	}

	/**
	 * @param $data
	 */
	public function addRequestHistory($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "cloudkassir_request_history` SET 
			order_id = '" . intval($data['order_id']) . "',
			request = '" . $this->db->escape($data['request']) . "',
			date_added = CURRENT_TIMESTAMP
		");
	}
}
