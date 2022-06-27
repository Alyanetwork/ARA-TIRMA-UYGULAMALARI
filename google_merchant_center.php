<?php
class ControllerExtensionFeedGoogleMerchantCenter extends Controller {
	private $error = array();

	public function install() {
		$this->load->model('extension/feed/feed_manager_taxonomy');
		$this->model_extension_feed_feed_manager_taxonomy->install();
	}

	public function index() {
		$this->load->language('extension/feed/google_merchant_center');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$this->load->model('extension/feed/google_merchant_center');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('feed_google_merchant_center', $this->request->post);

			$this->model_extension_feed_google_merchant_center->saveSetting($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if ((int)str_replace('.','',VERSION)>=3000)
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true));

		}

		//$this->model_extension_feed_google_merchant_center->install();

		$data['google_merchant_base_category'] = array();

		$google_base = $this->model_extension_feed_google_merchant_center->getBaseCategory();

		foreach ($google_base as $base) {
			$data['google_merchant_base_category'][] = array (
				'taxonomy_id' => $base['taxonomy_id'],
				'status' => $base['status'],
				'name' => $base['name']
			);
		}


		$data['merchant_center_apparel_id'] = array();

		$apparel_id = $this->model_extension_feed_google_merchant_center->getOptionID();

		foreach ($apparel_id as $apparel) {
			$data['merchant_center_option'][] = array (
				'option_id' => $apparel['option_id'],
				'name' => $apparel['name']
			);
		}

		if (isset($this->request->post['feed_google_merchant_center_attribute'])) {
			$data['feed_google_merchant_center_attribute'] = $this->request->post['feed_google_merchant_center_attribute'];
		} else {
			$data['feed_google_merchant_center_attribute'] = $this->config->get('feed_google_merchant_center_attribute');
		}

		if (isset($this->request->post['feed_google_merchant_center_attribute_type'])) {
			$data['feed_google_merchant_center_attribute_type'] = $this->request->post['feed_google_merchant_center_attribute_type'];
		} else {
			$data['feed_google_merchant_center_attribute_type'] = $this->config->get('feed_google_merchant_center_attribute_type');
		}

		if (isset($this->request->post['feed_google_merchant_center_description'])) {
			$data['feed_google_merchant_center_description'] = $this->request->post['feed_google_merchant_center_description'];
		} else {
			$data['feed_google_merchant_center_description'] = $this->config->get('feed_google_merchant_center_description');
		}

		if (isset($this->request->post['feed_google_merchant_center_description_html'])) {
			$data['feed_google_merchant_center_description_html'] = $this->request->post['feed_google_merchant_center_description_html'];
		} else {
			$data['feed_google_merchant_center_description_html'] = $this->config->get('feed_google_merchant_center_description_html');
		}

		$attribute_id=$this->model_extension_feed_google_merchant_center->getAttributes();
		$data['merchant_center_attributes'][] = array (
			'attribute_id' => '-1',
			'name' => $this->language->get('entry_google_merchant_center_attribute_product')
		);
		$data['merchant_center_attributes_type'][] = array (
			'attribute_id' => '-1',
			'name' => $this->language->get('entry_google_merchant_center_attribute_product_type')
		);
		foreach ($attribute_id as $attribute) {
			$data['merchant_center_attributes'][] = array (
				'attribute_id' => $attribute['attribute_id'],
				'name' => $attribute['name']
			);
			$data['merchant_center_attributes_type'][] = array (
				'attribute_id' => $attribute['attribute_id'],
				'name' => $attribute['name']
			);
		}

		foreach ($apparel_id as $apparel) {
			$data['merchant_center_attributes'][] = array (
				'attribute_id' => 'o'.$apparel['option_id'],
				'name' => $apparel['name']
			);
		}

		$data['entry_google_merchant_base'] = $this->language->get('entry_google_merchant_base');
		$data['entry_google_merchant_center_attribute'] = $this->language->get('entry_google_merchant_center_attribute');
		$data['entry_google_merchant_center_attribute_type'] = $this->language->get('entry_google_merchant_center_attribute_type');
		$data['entry_google_merchant_center_option'] = $this->language->get('entry_google_merchant_center_option');
		$data['entry_google_merchant_center_availability'] = $this->language->get('entry_google_merchant_center_availability');
		$data['entry_google_merchant_center_shipping_flat'] = $this->language->get('entry_google_merchant_center_shipping_flat');
		$data['entry_google_merchant_center_description'] = $this->language->get('entry_google_merchant_center_description');
		$data['entry_google_merchant_center_description_html'] = $this->language->get('entry_google_merchant_center_description_html');
		$data['entry_google_merchant_center_feed_id1'] = $this->language->get('entry_google_merchant_center_feed_id1');
		$data['entry_google_merchant_center_use_taxes'] = $this->language->get('entry_google_merchant_center_use_taxes');

		$data['help_google_merchant_base'] = $this->language->get('help_google_merchant_base');
		$data['help_google_merchant_center_attribute'] = $this->language->get('help_google_merchant_center_attribute');
		$data['help_google_merchant_center_attribute_type'] = $this->language->get('help_google_merchant_center_attribute_type');
		$data['help_google_merchant_center_option'] = $this->language->get('help_google_merchant_center_option');
		$data['help_google_merchant_center_availability'] = $this->language->get('help_google_merchant_center_availability');
		$data['help_google_merchant_center_shipping_flat'] = $this->language->get('help_google_merchant_center_shipping_flat');
		$data['help_google_merchant_center_feed_id1'] = $this->language->get('help_google_merchant_center_feed_id1');
		$data['help_google_merchant_center_use_taxes'] = $this->language->get('help_google_merchant_center_use_taxes');
		$data['help_data_feed'] = $this->language->get('help_data_feed');
		$data['help_image_cache'] = $this->language->get('help_image_cache');

		$data['help_file'] = $this->language->get('help_file');
		$data['help_file_location'] = $this->language->get('help_file_location');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_enabled_default'] = $this->language->get('text_enabled_default');
		$data['text_skip_products'] = $this->language->get('text_skip_products');
		$data['text_in_stock'] = $this->language->get('text_in_stock');
		$data['text_out_of_stock'] = $this->language->get('text_out_of_stock');
		$data['text_preorder'] = $this->language->get('text_preorder');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_file'] = $this->language->get('entry_file');
		$data['entry_data_feed'] = $this->language->get('entry_data_feed');
		$data['entry_file_location'] = $this->language->get('entry_file_location');

		$data['entry_image_cache'] = $this->language->get('entry_image_cache');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_feed'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/feed/google_merchant_center', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/feed/google_merchant_center', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);

		if (isset($this->request->post['feed_google_merchant_center_status'])) {
			$data['feed_google_merchant_center_status'] = $this->request->post['feed_google_merchant_center_status'];
		} else {
			$data['feed_google_merchant_center_status'] = $this->config->get('feed_google_merchant_center_status');
		}

		if (isset($this->request->post['feed_google_merchant_center_file'])) {
			$data['feed_google_merchant_center_file'] = $this->request->post['feed_google_merchant_center_file'];
		} else {
			$data['feed_google_merchant_center_file'] = $this->config->get('feed_google_merchant_center_file');
		}

		if (isset($this->request->post['feed_google_merchant_center_file_location'])) {
			$data['feed_google_merchant_center_file_location'] = $this->request->post['feed_google_merchant_center_file_location'];
		} elseif ($this->config->get('feed_google_merchant_center_file_location')!='') {
			$data['feed_google_merchant_center_file_location'] = $this->config->get('feed_google_merchant_center_file_location');
		} else {
			$data['feed_google_merchant_center_file_location'] = 'feeds';
		}

		if (isset($this->request->post['feed_google_merchant_center_availability'])) {
			$data['feed_google_merchant_center_availability'] = $this->request->post['feed_google_merchant_center_availability'];
		} else {
			$data['feed_google_merchant_center_availability'] = $this->config->get('feed_google_merchant_center_availability');
		}

		if (isset($this->request->post['feed_google_merchant_center_option'])) {
			$data['feed_google_merchant_center_option'] = $this->request->post['feed_google_merchant_center_option'];
		} else {
			$data['feed_google_merchant_center_option'] = $this->config->get('feed_google_merchant_center_option');
		}

		if (isset($this->request->post['feed_google_merchant_center_shipping_flat'])) {
			$data['feed_google_merchant_center_shipping_flat'] = $this->request->post['feed_google_merchant_center_shipping_flat'];
		} else {
			$data['feed_google_merchant_center_shipping_flat'] = $this->config->get('feed_google_merchant_center_shipping_flat');
		}

		if (isset($this->request->post['feed_google_merchant_center_image_cache'])) {
			$data['feed_google_merchant_center_image_cache'] = $this->request->post['feed_google_merchant_center_image_cache'];
		} elseif ($this->config->get('feed_google_merchant_center_image_cache')!='') {
			$data['feed_google_merchant_center_image_cache'] = $this->config->get('feed_google_merchant_center_image_cache');
		} else {
			$data['feed_google_merchant_center_image_cache'] = '1';
		}

		if (isset($this->request->post['feed_google_merchant_center_feed_id1'])) {
			$data['feed_google_merchant_center_feed_id1'] = $this->request->post['feed_google_merchant_center_feed_id1'];
		} elseif ($this->config->get('feed_google_merchant_center_feed_id1')!='') {
			$data['feed_google_merchant_center_feed_id1'] = $this->config->get('feed_google_merchant_center_feed_id1');
		} else {
			$data['feed_google_merchant_center_feed_id1'] = 'model';
		}

		if (isset($this->request->post['feed_google_merchant_center_use_taxes'])) {
			$data['feed_google_merchant_center_use_taxes'] = $this->request->post['feed_google_merchant_center_use_taxes'];
		} else {
			$data['feed_google_merchant_center_use_taxes'] = $this->config->get('feed_google_merchant_center_use_taxes');
		}

		$data['data_feed'] = HTTP_CATALOG . 'index.php?route=extension/feed/google_merchant_center&lang='.$this->config->get('config_language').'&curr='.$this->config->get('config_currency').'&store='.(int)$this->config->get('config_store_id');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/feed/google_merchant_center', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/feed/google_merchant_center')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>
