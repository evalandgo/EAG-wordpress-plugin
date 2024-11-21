<?php

namespace src\Service\Settings;

use JsonException;
use src\Service\API\API;

class SettingsProcessor
{

    public function __construct(private API $API)
    {
    }

    /**
     * @throws JsonException
     */
    public function handle_settings_update(array $errors, array $input): array
    {
        if (!$this->isValidPrivateKey($input['eag_host_private_key'], $privateKey, $publicKey)) {
            return $this->addError($errors, 'Invalid private key. Please check the key and try again.');
        }

        $response = $this->API->getDomain();
        if ($this->hasError($response)) {
            return $this->addError($errors, 'There was an error in the API request. Please try again later.');
        }

        [$action, $updateResponse] = $this->createOrUpdateDomain($this->getResponseBody($response), $publicKey);
        if ($this->hasError($updateResponse)) {
//            $this->logError($updateResponse);
            return $this->addError($errors, "Error when trying to $action the domain. Please check your API key or contact support.");
        }

        add_settings_error('eag_wordpress_settings', 'settings_updated', 'Settings saved successfully!', 'updated');
        return $errors;
    }

    private function isValidPrivateKey(string $privateKeyInput, &$privateKey, &$publicKey): bool
    {
        $privateKey = openssl_pkey_get_private($privateKeyInput);
        if (!$privateKey) {
            return false;
        }
        $publicKey = openssl_pkey_get_details($privateKey)['key'];
        return true;
    }

    private function hasError($response): bool
    {
        return $this->hasApiError($response) || $this->isErrorResponse($response);
    }

    private function hasApiError($response): bool
    {
        return is_wp_error($response) || wp_remote_retrieve_response_code($response) === 500;
    }

    private function addError(array $errors, string $errorMessage): array
    {
        $errors[] = $errorMessage;
        return $errors;
    }

    private function isErrorResponse($response): bool
    {
        return wp_remote_retrieve_response_code($response) > 299;
    }

    private function logError($response): void
    {
        error_log(json_encode(is_wp_error($response) ? $response->get_all_error_data() : $response));
    }

    private function createOrUpdateDomain(array $responseBody, mixed $publicKey): array
    {
        $action = empty($responseBody) ? 'create' : 'update';
        $response = empty($responseBody) ?
            $this->API->performCreateDomain($publicKey) :
            $this->API->performUpdateDomain($responseBody[0]['id'], $publicKey);
        return [$action, $response];
    }

    /**
     * @throws JsonException
     */
    private function getResponseBody(\WP_Error|array $response)
    {
        return json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
    }
}