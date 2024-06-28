<?php

class ControllerExtensionPaymentShkeeper extends Controller
{

    public function index()
    {
        $this->load->language('extension/payment/shkeeper');

        $data['getAddressAction'] = $this->url->link('extension/payment/shkeeper/getAddress');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['redirect'] = $this->url->link('extension/payment/shkeeper/confirm');

        // fetching available currencies from the seller
        $available_currencies = $this->sendGetCurl('api/v1/crypto');

        $data['shkeeper'] = $available_currencies['crypto_list'];
        $data['shkeeper_instructions'] = $this->config->get('payment_shkeeper_instructions');
        
        return $this->load->view('extension/payment/shkeeper', $data);
    }

    public function getAddress()
    {
        $json = [];

        if (isset($this->request->post['currency'])) {
            $this->load->model('checkout/order');

            $currency = $this->request->post['currency'];
            $order_id = $this->session->data['order_id'];

            $order_info = $this->model_checkout_order->getOrder($order_id);

            $order_data = [
                "external_id"   => $order_id,
                "fiat"          => $this->session->data['currency'],
                "amount"        => $order_info['total'],
                "callback_url"  => HTTPS_SERVER . "index.php?route=extension/payment/shkeeper/callback",
            ];

            $json['response'] = $this->sendPostCurl("/api/v1/$currency/payment_request", $order_data);
            
        }

        if ('success' == $json['response']['status']) {
            // save payment info
            $this->session->data['shkeeper'] = [
                "wallet" => $json['response']['wallet'],
                "amount" => $json['response']['amount'],
                "currency" => $currency,
            ];
        }

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }

    public function callback()
    {
        $data = file_get_contents('php://input');
        $data_collected = json_decode($data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Handle the error
            $error_message = json_last_error_msg();
            // Return a JSON response with the error message
            $this->log->write($error_message);
            exit;
        }

        $headers = getallheaders();

        // stop request in case NOT AUTH.
        if (! isset($headers['X-Shkeeper-Api-Key']) || $headers['X-Shkeeper-Api-Key'] != $this->config->get('payment_shkeeper_apiKey')) {
			
			$this->log->write('[SHKeeper] ApiKey mismatch. Recieved headers: ' . print_r($headers, 1));
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('HTTP/1.1 401 Unauthorized');
            $this->response->setOutput(json_encode([]));
            return;
        }

        // collect data form request
        $order_id = $data_collected['external_id'];
        $transaction_info = "";

        // collect new transactions and save data on order update
        foreach ($data_collected['transactions'] as $transaction) {
            if ($transaction['trigger']) {

                $transaction_id = $transaction['txid'];
                $amount = $transaction['amount_crypto'] . ' ' . $transaction['crypto'];
                $date = $transaction['date'];

                $transaction_info .= "Transaction: # $transaction_id - Amount: $amount - Date: $date" . PHP_EOL;
            }
        }

        // handle duplicated callback requests
        if (empty($transaction_info)) {
			$this->log->write("[SHKeeper] Can't get transaction_info. Raw request: " . print_r($data, 1));
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('HTTP/1.1 202 Accepted');
            $this->response->setOutput(json_encode([]));
            return;
        }

        // update order status in case of full paid
		$order_status = $data_collected['paid'] ? $this->config->get('payment_shkeeper_order_status_id') : $this->config->get('config_order_status_id');
        $this->load->model('checkout/order');		
		$this->model_checkout_order->addOrderHistory($order_id, $order_status, $transaction_info, true);
        

        // shows confirmation
        $json = [
            "success" => true,
            "message" => "order status confirmed.",
        ];

		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('HTTP/1.1 202 Accepted');
		$this->response->setOutput(json_encode($json));
    }

    public function confirm()
    {

        // validate 
        if (isset($this->session->data['order_id'])) {

            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            if (!empty($this->session->data['shkeeper'])) {
    
                $comment = 'Wallet: ' . $this->session->data['shkeeper']['wallet'];
                $comment .= ' - Amount: ' . $this->session->data['shkeeper']['amount'];
                $comment .= ' - Currency: ' . $this->session->data['shkeeper']['currency'];

                if (0 == $order_info['order_status_id']) {

                    // update order and confirm payment
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'), $comment, true);
                }


            }

        }

        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    public function sendGetCurl(string $url)
    {
        
        $headers = [
            "X-Shkeeper-Api-Key: " . $this->config->get('payment_shkeeper_apiKey'),
        ];

        $base_url = $this->config->get('payment_shkeeper_apiURL');

        if ( substr($this->config->get('payment_shkeeper_apiURL'), -1) != DIRECTORY_SEPARATOR) {
            $base_url = $this->config->get('payment_shkeeper_apiURL') . DIRECTORY_SEPARATOR;
        }

        $options = [
            CURLOPT_URL => $base_url . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);

    }

    public function sendPostCurl(string $url, array $data = [])
    {
        
        $headers = [
            "X-Shkeeper-Api-Key: " . $this->config->get('payment_shkeeper_apiKey'),
        ];

        $base_url = $this->config->get('payment_shkeeper_apiURL');

        if ( substr($this->config->get('payment_shkeeper_apiURL'), -1) != DIRECTORY_SEPARATOR) {
            $base_url = $this->config->get('payment_shkeeper_apiURL') . DIRECTORY_SEPARATOR;
        }

        $options = [
            CURLOPT_URL => $base_url . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_POST => true,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);

    }

}