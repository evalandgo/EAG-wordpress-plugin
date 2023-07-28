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
        if (!openssl_pkey_get_private($input['eag_host_private_key']))
        {
        $errors[] = 'Invalid private key. Please check the key and try again.';
        }

        if (!empty($errors)) {
            return $errors;
        }
        $private_key = openssl_pkey_get_private($input['eag_host_private_key']);
        $details = openssl_pkey_get_details($private_key);
        $public_key = $details['key'];


        $response = $this->API->getDomain();
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) === 500) {
            $errors[] = 'There was an error in the API request. Please try again later.';
        }
        else {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code > 299) {
                $errors[] = 'Error: ' . $response_code . '. Please check your API key or contact support.';
            } else {
                //if the domain already exists, update it, otherwise create it
                $response_body = json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
                if (empty($response_body)) {
                    //create the domain
                    $response = $this->API->performCreateDomain($public_key);
                } else {
                    //update the domain
                    $response = $this->API->performUpdateDomain($response_body[0]['id'], $public_key);
                }
            }
        }


        if (is_wp_error($response)) {
            $errors[] = 'There was an error in the API request. Please try again later.';
        } else {
            // Check the response code.
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code >= 299) {
                $errors[] = sprintf('Error: %s when trying to %s the domain. Please check your API key or contact support.', $response_code, empty($response_body) ? 'create' : 'update');
            } else {
                // Success. Show a success message.
                add_settings_error('eag_wordpress_settings', 'settings_updated', 'Settings saved successfully!', 'updated');
            }
        }
        return $errors;
    }
}