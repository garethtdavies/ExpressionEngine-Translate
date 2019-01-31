<?php

/**
 * Class Translate_ext
 */
class Translate_ext
{

    public function __construct()
    {
        $addon = ee('Addon')->get('translate');
        $this->version = $addon->getVersion();
    }

    /**
     * @param $entry
     * @param $values
     */
    public function createTranslation($entry, $values)
    {
        // Build our callback URL we want to do this dynamically as may change
        $action = ee('Model')->get('Action')->filter('class', 'Translate')->filter('method', 'incomingWebhook')->first();
        $site_url = ee()->config->item('site_url');
        $token = ee('Config')->get('translate:config.token');
        // I had to remove the trailing slash on the URL as Gengo doesn't follow any redirects
        $url = rtrim($site_url, '/') . "?ACT=" . $action->action_id . "&token=" . $token;

        // Build the job array
        $job1 = array(
            "type" => "text", // Text or a file
            "slug" => "{$entry->entry_id}: {$entry->title}", // Useful for us to browse control panel but can be anything
            "body_src" => $entry->{ee('Config')->get('translate:config.fields.from')},
            "lc_src" => ee('Config')->get('translate:config.options.lc_src'), // Our source language which we'll set from the config file
            "lc_tgt" => ee('Config')->get('translate:config.options.lc_tgt'), // Our target language which we'll set from the config file
            "tier" => ee('Config')->get('translate:config.options.tier'), //standard or pro
            "auto_approve" => 1, // We want to process this automatically
            "force" => 0,
            "callback_url" => $url,
            "as_group" => 1, // this is set by default,
            'custom_data' => $entry->entry_id, // We'll use this to retrieve the model later to update
            'tone' => ee('Config')->get('translate:config.options.tone'), // Get from a config file
        );

        // Pass the jobs. Need more jobs? Just add to the array...
        $jobs = array("job_01" => $job1);

        // Send to Gengo
        $client = ee("translate:jobs");
        $client->postJobs($jobs);

        $code = $client->getResponseCode();
        $response = json_decode($client->getResponseBody(), true);

        if ($response['opstat'] === "ok") {
            // Translation underway!
            $entry->{ee('Config')->get('translate:config.fields.order_id')} = $response['response']['order_id']; // Save the order id to a custom field if we need it
            $entry->save();
        } else {
            // Something went wrong so log some stuff
            ee()->load->library('logger');
            ee()->logger->developer("Error saving {$entry->entry_id}");
        }
    }

    /**
     * Activate Extension
     */
    public function activate_extension()
    {
        ee('Model')->make('Extension', [
            'class' => __CLASS__,
            'method' => 'createTranslation',
            'hook' => 'after_channel_entry_insert',
            'settings' => [],
            'version' => $this->version,
            'enabled' => 'y',
        ])->save();
    }

    /**
     * Disable Extension
     */
    public function disable_extension()
    {
        ee('Model')->get('Extension')
            ->filter('class', __CLASS__)
            ->delete();
    }

}
