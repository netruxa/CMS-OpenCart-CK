<?php

/**
 * Class ModelExtensionModuleCloudKassir
 *
 * @property Loader              $load
 * @property ModelSettingSetting $model_setting_setting
 * @property DB\MySQLi           $db
 */
class ModelExtensionModuleCloudKassir extends Model {

	/**
	 *
	 */
	public function install() {

		$this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'cloudkassir_request_history` (
			`cloudkassir_request_history_id` int(11) NOT NULL AUTO_INCREMENT,
			`order_id` int(11) NOT NULL,
			`request` varchar(50) NOT NULL,
			`date_added` datetime NOT NULL,
			PRIMARY KEY (`cloudkassir_request_history_id`),
			INDEX `order_id` (`order_id`),
			INDEX `request` (`request`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');

		// Order Status defaults
		$defaults['module_cloudkassir_order_status_for_pay']    = array(1);
		$defaults['module_cloudkassir_order_status_for_refund'] = array(9);

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_cloudkassir', $defaults);
	}

	/**
	 *
	 */
	public function uninstall() {
	}
}
