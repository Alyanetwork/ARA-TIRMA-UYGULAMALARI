<?php
class ControllerExtensionModuleXtensionsCustomField extends Controller {
	private $error = array();
	public $data;
	
	public function __construct($registry){
		parent::__construct($registry);
		if(!isset($this->xtensions_checkout)){
			$xtensions_checkout = new XtensionsCheckout($this->registry);
			$this->registry->set('xtensions_checkout', $xtensions_checkout);
		}	
	}

	public function index() {
		$this->load->language($this->config->get('xtensions_custom_fields_path'));

		$this->document->setTitle($this->language->get('heading_title'));		

		$this->getList();
	}

	public function add() {
		$this->load->language($this->config->get('xtensions_custom_fields_path'));

		$this->document->setTitle($this->language->get('heading_title'));		

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->addCustomField($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->xtensions_checkout->redirect($this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language($this->config->get('xtensions_custom_fields_path'));

		$this->document->setTitle($this->language->get('heading_title'));		

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->editCustomField($this->request->get['custom_field_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->xtensions_checkout->redirect($this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language($this->config->get('xtensions_custom_fields_path'));

		$this->document->setTitle($this->language->get('heading_title'));		

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $custom_field_id) {
				$this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->deleteCustomField($custom_field_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->xtensions_checkout->redirect($this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		
		$home = 'common/dashboard';
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'cfd.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'] = array(
		array('text'=>$this->language->get('text_home'),'href'=>$this->url->link($home,'user_token=' . $this->session->data['user_token'], 'SSL'),'separator'=>false),
		array('text'=>$this->language->get('heading_title'),'href'=> $this->url->link($this->config->get('xtensions_custom_fields_path'),'user_token=' . $this->session->data['user_token'], 'SSL'),'separator' => ' :: ')
		);

		$this->data['add'] = $this->url->link($this->config->get('xtensions_custom_fields_path').'/add', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link($this->config->get('xtensions_custom_fields_path').'/delete', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

		$this->data['custom_fields'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$custom_field_total = $this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->getTotalCustomFields();

		$results = $this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->getCustomFields($filter_data);

		foreach ($results as $result) {
			$type = '';

			switch ($result['type']) {
				case 'select':
					$type = $this->language->get('text_select');
					break;
				case 'radio':
					$type = $this->language->get('text_radio');
					break;
				case 'checkbox':
					$type = $this->language->get('text_checkbox');
					break;
				case 'input':
					$type = $this->language->get('text_input');
					break;
				case 'text':
					$type = $this->language->get('text_text');
					break;
				case 'textarea':
					$type = $this->language->get('text_textarea');
					break;
				case 'file':
					$type = $this->language->get('text_file');
					break;
				case 'date':
					$type = $this->language->get('text_date');
					break;
				case 'datetime':
					$type = $this->language->get('text_datetime');
					break;
				case 'time':
					$type = $this->language->get('text_time');
					break;
			}

			$this->data['custom_fields'][] = array(
				'custom_field_id' => $result['custom_field_id'],
				'name'            => $result['name'],
				'location'        => $this->language->get('text_' . $result['location']),
				'type'            => $type,
				'status'          => $result['status'],
				'sort_order'      => $result['sort_order'],
				'edit'            => $this->url->link($this->config->get('xtensions_custom_fields_path').'/edit', 'user_token=' . $this->session->data['user_token'] . '&custom_field_id=' . $result['custom_field_id'] . $url, 'SSL')
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_location'] = $this->language->get('column_location');
		$this->data['column_type'] = $this->language->get('column_type');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_add'] = $this->language->get('button_add');
		$this->data['button_edit'] = $this->language->get('button_edit');
		$this->data['button_delete'] = $this->language->get('button_delete');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$this->data['selected'] = (array)$this->request->post['selected'];
		} else {
			$this->data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_name'] = $this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . '&sort=cfd.name' . $url, 'SSL');
		$this->data['sort_location'] = $this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . '&sort=cf.location' . $url, 'SSL');
		$this->data['sort_type'] = $this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . '&sort=cf.type' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . '&sort=cf.status' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . '&sort=cf.sort_order' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $custom_field_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();
		$this->data['results'] = sprintf($this->language->get('text_pagination'), ($custom_field_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($custom_field_total - $this->config->get('config_limit_admin'))) ? $custom_field_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $custom_field_total, ceil($custom_field_total / $this->config->get('config_limit_admin')));
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->template =  $this->config->get('xtensions_custom_fields_view_path').'/xcustom_field_list';
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view($this->template, $this->data));
	}

	protected function getForm() {
		$this->data = $this->language->load($this->config->get('xtensions_custom_fields_path'));
		$this->data['text_form'] = !isset($this->request->get['custom_field_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$this->data['heading_title'] = $this->language->get('heading_title');

		foreach ($this->error as $key => $value){
			$this->data['error_'.$key] = $value;
		}

		if (isset($this->error['custom_field_value'])) {
			$this->data['error_custom_field_value'] = $this->error['custom_field_value'];
		} else {
			$this->data['error_custom_field_value'] = array();
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
			'separator' => ' :: '
			);

			if (!isset($this->request->get['custom_field_id'])) {
				$this->data['action'] = $this->url->link($this->config->get('xtensions_custom_fields_path').'/add', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
			} else {
				$this->data['action'] = $this->url->link($this->config->get('xtensions_custom_fields_path').'/edit', 'user_token=' . $this->session->data['user_token'] . '&custom_field_id=' . $this->request->get['custom_field_id'] . $url, 'SSL');
			}

			$this->data['cancel'] = $this->url->link($this->config->get('xtensions_custom_fields_path'), 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

			if (isset($this->request->get['custom_field_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
				$custom_field_info = $this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->getCustomField($this->request->get['custom_field_id']);
			}

			$this->data['user_token'] = $this->session->data['user_token'];

			$this->load->model('localisation/language');

			$this->data['languages'] = $this->model_localisation_language->getLanguages();

			if (isset($this->request->post['custom_field_description'])) {
				$this->data['custom_field_description'] = $this->request->post['custom_field_description'];
			} elseif (isset($this->request->get['custom_field_id'])) {
				$this->data['custom_field_description'] = $this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->getCustomFieldLanguage($this->request->get['custom_field_id']);
			} else {
				$this->data['custom_field_description'] = array();
			}
			$this->data['regional_validations'] = $this->config->get('xtensions_regional_validations');
			$fields = array('type','value','location','status','xsort_order','minimum','maximum','mask','identifier','isnumeric','email_display','order_display','list_display','invoice','validation','regional_validation');

			foreach ($fields as $field) {
				if (isset($this->request->post[$field])) {
					$this->data[$field] = $this->request->post[$field];
				} elseif (!empty($custom_field_info)) {
					$this->data[$field] = $custom_field_info[$field];
				} else {
					$this->data[$field] = '';
				}
			}
			if (isset($this->request->post['custom_field_value'])) {
				$custom_field_values = $this->request->post['custom_field_value'];
			} elseif (isset($this->request->get['custom_field_id'])) {
				$custom_field_values = $this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->getCustomFieldValueDescriptions($this->request->get['custom_field_id']);
			} else {
				$custom_field_values = array();
			}

			$this->data['custom_field_values'] = array();

			foreach ($custom_field_values as $custom_field_value) {
				$this->data['custom_field_values'][] = array(
				'custom_field_value_id'          => $custom_field_value['custom_field_value_id'],
				'custom_field_value_description' => $custom_field_value['custom_field_value_description'],
				'sort_order'                     => $custom_field_value['sort_order']
				);
			}

			if (isset($this->request->post['custom_field_customer_group'])) {
				$custom_field_customer_groups = $this->request->post['custom_field_customer_group'];
			} elseif (isset($this->request->get['custom_field_id'])) {
				$custom_field_customer_groups = $this->xtensions_checkout->getXtensionsModel($this->config->get('xtensions_custom_fields_path'))->getCustomFieldCustomerGroups($this->request->get['custom_field_id']);
			} else {
				$custom_field_customer_groups = array();
			}

			$this->data['custom_field_customer_group'] = array();

			foreach ($custom_field_customer_groups as $custom_field_customer_group) {
				$this->data['custom_field_customer_group'][] = $custom_field_customer_group['customer_group_id'];
			}

			$this->data['custom_field_required'] = array();

			foreach ($custom_field_customer_groups as $custom_field_customer_group) {
				if ($custom_field_customer_group['required']) {
					$this->data['custom_field_required'][] = $custom_field_customer_group['customer_group_id'];
				}
			}
			$this->data['best_checkout'] = $this->url->link($this->config->get('xtensions_admin_xcheckout_path'),'user_token=' . $this->session->data['user_token']);
			$this->load->model('customer/customer_group');
			$this->data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();			
			$this->template =  $this->config->get('xtensions_custom_fields_view_path').'/xcustom_field_form';
			$this->data['header'] = $this->load->controller('common/header');
			$this->data['column_left'] = $this->load->controller('common/column_left');
			$this->data['footer'] = $this->load->controller('common/footer');
			$this->response->setOutput($this->load->view($this->template, $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', $this->config->get('xtensions_custom_fields_path'))) {
			$this->error['warning'] = $this->language->get('xerror_permission');
		}

		foreach ($this->request->post['custom_field_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 128)) {
				$this->error['name'][$language_id] = $this->language->get('xerror_name');
			}
			if (isset($this->request->post['custom_field_customer_group'])) {
				foreach ($this->request->post['custom_field_customer_group'] as $custom_field_customer_group) {
					if( isset($custom_field_customer_group['required']) && utf8_strlen($value['error'])<1){
						$this->error['error'][$language_id] = $this->language->get('xerror_error');
					}
				}
			}
		}
		if ((utf8_strlen($this->request->post['identifier']) < 1) || ($this->request->post['identifier'] > 30)) {
			$this->error['identifier'] = $this->language->get('xerror_identifier');
		}
		if (($this->request->post['type'] == 'select' || $this->request->post['type'] == 'radio' || $this->request->post['type'] == 'checkbox')) {
			if (!isset($this->request->post['custom_field_value'])) {
				$this->error['warning'] = $this->language->get('xerror_type');
			}

			if (isset($this->request->post['custom_field_value'])) {
				foreach ($this->request->post['custom_field_value'] as $custom_field_value_id => $custom_field_value) {
					foreach ($custom_field_value['custom_field_value_description'] as $language_id => $custom_field_value_description) {
						if ((utf8_strlen($custom_field_value_description['name']) < 1) || (utf8_strlen($custom_field_value_description['name']) > 128)) {
							$this->error['custom_field_value'][$custom_field_value_id][$language_id] = $this->language->get('xerror_custom_value');
						}
					}
				}
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', $this->config->get('xtensions_custom_fields_path'))) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
