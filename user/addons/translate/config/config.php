<?php

/*
 * Config variables to configure translation options
 * Do not commit sensitive data to source code repositories
 * Utilise environment variables or suitable alternatives
 * e.g. set value below to $_ENV["gengo_api_key"]
 */

// Map the custom translation fields to field_ids
$config['fields'] = array(
    "from" => "field_id_6",
    "to" => "field_id_7",
    "order_id" => "field_id_8",
);

// Use production. Set to 0 to use sandbox
$config['production'] = 1;

// Set the options for job translations see https://developers.gengo.com/v2/api_methods/payloads/#job-payload-for-submissions
$config['options'] = array(
    "lc_src" => "en",
    "lc_tgt" => "ru",
    "tone" => "",
    "tier" => "standard", //standard or pro
);

// Set the secret token. Don't store it like this in production, use environment variables
$config['token'] = "";
$config['api_key'] = '';
$config['private_key'] = '';

