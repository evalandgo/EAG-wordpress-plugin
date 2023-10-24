# Eval&GO Wordpress Plugin
## Overview
The Eval&GO WordPress plugin seamlessly integrates your WordPress website with your Eval&GO account. With this plugin, you can ensure that only authenticated users on your website can respond to your questionnaires, thereby enhancing security and data integrity.

## Features
### Questionnaire Protection
This plugin adds an extra layer of security to your questionnaires by requiring users to be authenticated on your WordPress website. This is particularly useful for:

- Internal company surveys
- Exclusive content for members
- Research studies requiring authenticated responses
- E-commerce sites looking for customer feedback from verified buyers

This feature works regardless of whether the questionnaire is embedded in a page on your site or accessed through a separate browser window.

## Installation
### Manual Installation
Clone this repository to your local machine.
Navigate to your WordPress admin panel.
Go to Plugins -> Add New -> Upload Plugin.
Upload the zipped plugin folder.
Activate the plugin through the 'Plugins' menu in WordPress.
Configuration
After installing the plugin, navigate to Settings -> Eval&GO Plugin Settings to configure your API keys and other settings.

## Usage
### Setting-up the protection:
After successful plugin configuration, you will find a new option labeled "Protect my Questionnaire with WordPress" on the 'Publish' page of your questionnaire within your Eval&GO account. Choose your domain and confirm. Congratulations, your questionnaire is now protected!
### Protecting an Embedded Questionnaire
To embed a questionnaire that's protected by user authentication, simply use the embed link provided on the "Publish" page of your Eval&GO questionnaire. The link should resemble the following format:
```html
<iframe src="https://app.evalandgo.com/f/12345/4pDLc5HDEeMbHd8teGVaGn?s=987d984d82b025?[eag_user_token]" 
        style="width:100%; height:100%;border:none" >
</iframe>
```

The [eag_user_token] shortcode will be dynamically translated by the plugin into an authentication token for the logged-in user. This ensures that only authenticated users will have access to the questionnaire.

## Support
For support or further questions, you can contact us at support@evalandgo.com.
