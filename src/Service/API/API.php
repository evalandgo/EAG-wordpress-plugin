<?php

namespace src\Service\API;

use WP_Error;

class API
{
    private const API_URL = 'https://app.evalandgo.com/api/v3/domains';
    public function __construct(private string $api_key) {
    }

    public function getDomain(): WP_Error|array
    {
        return wp_remote_get(self::API_URL. '?domainName='. parse_url(get_site_url(), PHP_URL_HOST), [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json',
            ],
//            'sslverify' => false,
        ]);
    }
    public function performUpdateDomain($id, string $public_key): array|WP_Error
    {
        return wp_remote_post(self::API_URL. '/' . $id, [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            'body' => json_encode([
                'domainName' => parse_url(get_site_url(), PHP_URL_HOST),
                'publicKey' => $public_key,
                'active' => true
            ], JSON_THROW_ON_ERROR),
            'method' => 'PUT',
            'data_format' => 'body',
//            'sslverify' => false,
        ]);
    }

    public function performCreateDomain(string $public_key): array|WP_Error
    {
        return wp_remote_post(self::API_URL, [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            'body' => json_encode([
                //domainName should be something like example.com or subdomain.example.com
                'domainName' => parse_url(get_site_url(), PHP_URL_HOST),
                'publicKey' => $public_key,
                'active' => true
            ], JSON_THROW_ON_ERROR),
            'method' => 'POST',
            'data_format' => 'body',
//            'sslverify' => false,
        ]);
    }
}