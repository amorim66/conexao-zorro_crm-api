<?php
// lib/ZohoCRM.php

class ZohoCRM {
    private $auth;
    private $config;

    public function __construct($auth, $config) {
        $this->auth = $auth;
        $this->config = $config;
    }

    public function getProducts() {
        $access_token = $this->auth->getAccessToken();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->config['api_url'] . 'Products');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Zoho-oauthtoken ' . $access_token,
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $response_data = json_decode($response, true);

        // Verifica se houve erro na resposta da API
        if (isset($response_data['code']) && $response_data['code'] !== 'SUCCESS') {
            die("Erro ao acessar produtos: " . $response_data['message']);
        }

        return $response_data;
    }
}
