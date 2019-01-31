<?php

use Gengo\Config;
use Gengo\Jobs;

# Require our composer dependencies
require 'vendor/autoload.php';

return [
    'author' => 'Gareth Davies',
    'author_url' => 'https://garethtdavies.com',
    'name' => 'Translate',
    'description' => 'Translates channel entries via gengo translation service.',
    'version' => '1.0',
    'namespace' => 'Garethtdavies\Translate',
    'settings_exist' => true,
    'services' => [
        'jobs' => function ($addon) {

            Config::setAPIkey(ee('Config')->getFile('translate:config')->get('api_key'));
            Config::setPrivateKey(ee('Config')->getFile('translate:config')->get('private_key'));
            if (ee('Config')->getFile('translate:config')->get('production')) {
                Config::useProduction();
            }

            return new Jobs();
        },
    ],
    'docs_url' => 'https://example.com/hello_world/docs',
];
