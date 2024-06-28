<?php

class ModelExtensionPaymentShkeeper extends Model
{

    public function getMethod($address, $total) {
		$this->load->language('extension/payment/shkeeper');

        return array(
            'code'       => 'shkeeper',
            'title'      => $this->language->get('text_title') . '  ' . '<img src="' . HTTPS_SERVER . 'image/catalog/shkeeper/SHKeeper.svg" height="25" />',
            'terms'      => '',
            'sort_order' => $this->config->get('payment_shkeeper_sort_order')
        );
	}

}