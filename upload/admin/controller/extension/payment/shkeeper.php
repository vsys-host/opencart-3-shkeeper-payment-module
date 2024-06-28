<?php

class ControllerExtensionPaymentShkeeper extends Controller
{
    private array $error = array();

    public function index()
    {
        $this->load->language('extension/payment/shkeeper');
        $this->load->model('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_shkeeper', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_apiKey'])) {
			$data['error_apiKey'] = $this->error['error_apiKey'];
		} else {
			$data['error_apiKey'] = '';
		}

		if (isset($this->error['error_apiURL'])) {
			$data['error_apiURL'] = $this->error['error_apiURL'];
		} else {
			$data['error_apiURL'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/shkeeper', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/shkeeper', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_shkeeper_apiKey'])) {
			$data['payment_shkeeper_apiKey'] = $this->request->post['payment_shkeeper_apiKey'];
		} else if ($this->config->get('payment_shkeeper_apiKey')) {
			$data['payment_shkeeper_apiKey'] = $this->config->get('payment_shkeeper_apiKey');
		} else {
            $data['payment_shkeeper_apiKey'] = '';
        }

		if (isset($this->request->post['payment_shkeeper_apiURL'])) {
			$data['payment_shkeeper_apiURL'] = $this->request->post['payment_shkeeper_apiURL'];
		} else if ($this->config->get('payment_shkeeper_apiKey')) {
			$data['payment_shkeeper_apiURL'] = $this->config->get('payment_shkeeper_apiURL');
		} else {
            $data['payment_shkeeper_apiURL'] = '';
        }

		if (isset($this->request->post['payment_shkeeper_instructions'])) {
			$data['payment_shkeeper_instructions'] = $this->request->post['payment_shkeeper_instructions'];
		} else if ($this->config->get('payment_shkeeper_instructions')) {
			$data['payment_shkeeper_instructions'] = $this->config->get('payment_shkeeper_instructions');
		} else {
            $data['payment_shkeeper_instructions'] = '';
        }

        $this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
		if (isset($this->request->post['payment_shkeeper_order_status_id'])) {
			$data['payment_shkeeper_order_status_id'] = $this->request->post['payment_shkeeper_order_status_id'];
		} else {
			$data['payment_shkeeper_order_status_id'] = $this->config->get('payment_shkeeper_order_status_id');
		}

		if (isset($this->request->post['payment_shkeeper_sort_order'])) {
			$data['payment_shkeeper_sort_order'] = (int) $this->request->post['payment_shkeeper_sort_order'];
		} else {
			$data['payment_shkeeper_sort_order'] = (int) $this->config->get('payment_shkeeper_sort_order');
		}

		if (isset($this->request->post['payment_shkeeper_status'])) {
			$data['payment_shkeeper_status'] = $this->request->post['payment_shkeeper_status'];
		} else {
			$data['payment_shkeeper_status'] = $this->config->get('payment_shkeeper_status');
		}

        $data['header']         = $this->load->controller('common/header');
        $data['footer']         = $this->load->controller('common/footer');
        $data['column_left']    = $this->load->controller('common/column_left');

        $this->response->setOutput($this->load->view('extension/payment/shkeeper', $data));
    }

    public function validate(){
        
		if (!$this->user->hasPermission('modify', 'extension/payment/shkeeper')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (empty($this->request->post['payment_shkeeper_apiKey'])) {
			$this->error['error_apiKey'] = $this->language->get('error_apiKey');
		}

		if (empty($this->request->post['payment_shkeeper_apiURL'])) {
			$this->error['error_apiURL'] = $this->language->get('error_apiURL');
		}
        
        return !$this->error;
    }

}