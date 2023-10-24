<?php

namespace src\View;

class SettingsView
{
    public function eag_settings_page(): void
    {
        ?>
        <div class="wrap">
            <h1>Eval&GO Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('eag_wordpress');
                do_settings_sections('eag_wordpress');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    function eag_wordpress_section_api_key_callback()
    {
        echo '<p>Enter your Eval&GO API key here. You can find or create it in your Eval&GO account, in the <a href=https://app.evalandgo.com/tokens/list" target="_blank">API section</a>.</p>';
    }

    function eag_api_key_field_callback()
    {
        $value = get_option('eag_wordpress_settings') ? get_option('eag_wordpress_settings')['eag_api_key'] ?? '' : '';
        echo '<input type="text" id="eag_api_key" name="eag_wordpress_settings[eag_api_key]" value="' . $value . '" />';
    }

    function eag_wordpress_section_host_keys_callback()
    {
        echo '<p>Enter the private key that will be used to sign the data transmitted to Eval&GO.</p>';
    }

    function eag_private_key_field_callback()
    {
        $value = get_option('eag_wordpress_settings') ? get_option('eag_wordpress_settings')['eag_host_private_key'] ?? '' : '';
        echo '<textarea style="width: 400px;" id="eag_host_private_key" name="eag_wordpress_settings[eag_host_private_key]">'. $value.  '</textarea>';
    }

    function display_last_error_message(string $error_message): void
    {
        echo "<div class='notice notice-error'><p>{$error_message}</p></div>";
    }

    function eag_wordpress_section_email_callback()
    {
        echo '
            <p>By default, this plugin does not send any identifiable user information to Eval&GO and thus keeps user responses anonymous. If you wish to link user responses with their respective identities, please enable the option below. <br> <small>When this option is enabled, the plugin will send the user\'s email address to Eval&GO and link it to the response. This will allow you to identify the user who submitted the response.</small></p>
            '
        ;
    }

    function eag_send_email_field_callback()
    {
        $value = get_option('eag_wordpress_settings') ? get_option('eag_wordpress_settings')['eag_send_email'] ?? '' : '';
        echo '<input type="checkbox" id="eag_send_email" name="eag_wordpress_settings[eag_send_email]" value="1" ' . checked(1, $value, false) . '/>';
    }
}