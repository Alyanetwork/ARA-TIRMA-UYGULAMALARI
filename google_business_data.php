<?php
class ControllerExtensionFeedGoogleBusinessData extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/feed/google_business_data');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('feed_google_business_data', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if ((int)str_replace('.','',VERSION)>=3000)
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true));

		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_file'] = $this->language->get('entry_file');
		$data['entry_data_feed'] = $this->language->get('entry_data_feed');
		$data['entry_file_location'] = $this->language->get('entry_file_location');

		$data['entry_google_business_data_description'] = $this->language->get('entry_google_business_data_description');
		$data['entry_google_business_data_sold_out'] = $this->language->get('entry_google_business_data_sold_out');
		$data['entry_google_business_data_description_html'] = $this->language->get('entry_google_business_data_description_html');
		$data['entry_google_business_data_feed_id1'] = $this->language->get('entry_google_business_data_feed_id1');
		$data['entry_google_business_data_feed_id2'] = $this->language->get('entry_google_business_data_feed_id2');
		$data['entry_google_business_data_use_taxes'] = $this->language->get('entry_google_business_data_use_taxes');
		$data['entry_image_cache'] = $this->language->get('entry_image_cache');
		$data['help_google_business_data_feed_id1'] = $this->language->get('help_google_business_data_feed_id1');
		$data['help_google_business_data_feed_id2'] = $this->language->get('help_google_business_data_feed_id2');
		$data['help_google_business_data_use_taxes'] = $this->language->get('help_google_business_data_use_taxes');
		$data['help_data_feed'] = $this->language->get('help_data_feed');
		$data['help_image_cache'] = $this->language->get('help_image_cache');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');

		$data['help_file'] = $this->language->get('help_file');
		$data['help_file_location'] = $this->language->get('help_file_location');

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
			'href' => $this->url->link('extension/feed/google_business_data', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/feed/google_business_data', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);

		if (isset($this->request->post['feed_google_business_data_status'])) {
			$data['feed_google_business_data_status'] = $this->request->post['feed_google_business_data_status'];
		} else {
			$data['feed_google_business_data_status'] = $this->config->get('feed_google_business_data_status');
		}

		if (isset($this->request->post['feed_google_business_data_file'])) {
			$data['feed_google_business_data_file'] = $this->request->post['feed_google_business_data_file'];
		} else {
			$data['feed_google_business_data_file'] = $this->config->get('feed_google_business_data_file');
		}

		if (isset($this->request->post['feed_google_business_data_file_location'])) {
			$data['feed_google_business_data_file_location'] = $this->request->post['feed_google_business_data_file_location'];
		} elseif ($this->config->get('feed_google_business_data_file_location')!='') {
			$data['feed_google_business_data_file_location'] = $this->config->get('feed_google_business_data_file_location');
		} else {
			$data['feed_google_business_data_file_location'] = 'feeds';
		}

		if (isset($this->request->post['feed_google_business_data_sold_out'])) {
			$data['feed_google_business_data_sold_out'] = $this->request->post['feed_google_business_data_sold_out'];
		} else {
			$data['feed_google_business_data_sold_out'] = $this->config->get('feed_google_business_data_sold_out');
		}

		if (isset($this->request->post['feed_google_business_data_description'])) {
			$data['feed_google_business_data_description'] = $this->request->post['feed_google_business_data_description'];
		} else {
			$data['feed_google_business_data_description'] = $this->config->get('feed_google_business_data_description');
		}

		if (isset($this->request->post['feed_google_business_data_description_html'])) {
			$data['feed_google_business_data_description_html'] = $this->request->post['feed_google_business_data_description_html'];
		} else {
			$data['feed_google_business_data_description_html'] = $this->config->get('feed_google_business_data_description_html');
		}

		if (isset($this->request->post['feed_google_business_data_image_cache'])) {
			$data['feed_google_business_data_image_cache'] = $this->request->post['feed_google_business_data_image_cache'];
		} elseif ($this->config->get('feed_google_business_data_image_cache')!='') {
			$data['feed_google_business_data_image_cache'] = $this->config->get('feed_google_business_data_image_cache');
		} else {
			$data['feed_google_business_data_image_cache'] = '1';
		}

		if (isset($this->request->post['feed_google_business_data_feed_id1'])) {
			$data['feed_google_business_data_feed_id1'] = $this->request->post['feed_google_business_data_feed_id1'];
		} elseif ($this->config->get('feed_google_business_data_feed_id1')!='') {
			$data['feed_google_business_data_feed_id1'] = $this->config->get('feed_google_business_data_feed_id1');
		} else {
			$data['feed_google_business_data_feed_id1'] = 'model';
		}
		if (isset($this->request->post['feed_google_business_data_feed_id2'])) {
			$data['feed_google_business_data_feed_id2'] = $this->request->post['feed_google_business_data_feed_id2'];
		} elseif ($this->config->get('feed_google_business_data_feed_id2')!='') {
			$data['feed_google_business_data_feed_id2'] = $this->config->get('feed_google_business_data_feed_id2');
		} else {
			$data['feed_google_business_data_feed_id2'] = '';
		}

		if (isset($this->request->post['feed_google_business_data_use_taxes'])) {
			$data['feed_google_business_data_use_taxes'] = $this->request->post['feed_google_business_data_use_taxes'];
		} else {
			$data['feed_google_business_data_use_taxes'] = $this->config->get('feed_google_business_data_use_taxes');
		}

		$data['data_feed'] = HTTP_CATALOG . 'index.php?route=extension/feed/google_business_data&lang='.$this->config->get('config_language').'&curr='.$this->config->get('config_currency').'&store='.(int)$this->config->get('config_store_id');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/feed/google_business_data', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/feed/google_business_data')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
