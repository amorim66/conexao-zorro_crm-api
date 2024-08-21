<?php
// lib/ZohoAuth.php

class ZohoAuth {
    private $config;
    private $token_file;

    public function __construct($config) {
        $this->config = $config;
        $this->token_file = __DIR__ . '/../data/tokens.json';
    }

    public function getAccessToken() {
        $tokens = $this->loadTokens();

        if (!$tokens || time() >= $tokens['expires_at']) {
            return $this->refreshAccessToken($tokens['refresh_token']);
        }

        return $tokens['access_token'];
    }

    private function loadTokens() {
        if (file_exists($this->token_file)) {
            return json_decode(file_get_contents($this->token_file), true);
        }
        return null;
    }

    private function saveTokens($tokens) {
        file_put_contents($this->token_file, json_encode($tokens));
    }

    public function refreshAccessToken($refresh_token = null) {
        $postData = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'grant_type' => $refresh_token ? 'refresh_token' : $this->config['grant_type'],
        ];

        if ($refresh_token) {
            $postData['refresh_token'] = $refresh_token;
        } else {
            $postData['code'] = $this->config['code'];
            $postData['redirect_uri'] = $this->config['redirect_uri'];
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->config['token_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $response_data = json_decode($response, true);

        // Verificar se houve algum erro na resposta
        if (isset($response_data['error'])) {
            die("Erro na autenticação: " . $response_data['error'] . " - " . $response_data['error_description']);
        }

        if (isset($response_data['access_token'])) {
            $tokens = [
                'access_token' => $response_data['access_token'],
                'refresh_token' => $refresh_token ?: $response_data['refresh_token'],
                'expires_at' => time() + $response_data['expires_in'],
            ];
            $this->saveTokens($tokens);
            return $tokens['access_token'];
        }

        return null;
    }
}
