<?php

/**
 * Translate Module
 */
class Translate
{

    public $return_data = '';

    /**
     *  {exp:translate:listOrders status="" fromdate="" limit=""}
     *
     *  Outputs all available orders matching the supplied parameters
     *  See https://developers.gengo.com/v2/api_methods/jobs/#jobs-get
     */
    public function listOrders()
    {

        // Should check these parameters are acceptable
        $status = ee()->TMPL->fetch_param('status', null); //"available", "pending", "reviewable", "approved", "rejected", or "canceled"
        $from_date = ee()->TMPL->fetch_param('from_date', null);
        $count = ee()->TMPL->fetch_param('limit', null);

        // We allow any datetime value and convert it here to a timestamp to send to Gengo
        if ($from_date) {
            $timestampafter = strtotime($from_date);
        }

        // Send the request to Gengo
        $jobs = ee("translate:jobs");
        $jobs->getJobs($status, $timestampafter, (int) $count);

        $code = $jobs->getResponseCode();
        $response = json_decode($jobs->getResponseBody(), true);

        // Do a basic check on the response
        if ($response['opstat'] === "ok") {

            // Is there any data? If not utilise the no_results tag
            if (empty($response['response'])) {
                return ee()->TMPL->no_results();
            }

            // Build up a second request to get full order details
            $order_ids = array();

            // Get the full detail for the order
            foreach ($response['response'] as $order) {
                array_push($order_ids, $order['job_id']);
            }

            // Make a second request for full order details
            $data = ee("translate:jobs");
            $data->getJobsById($order_ids);

            $code = $data->getResponseCode();
            $response = json_decode($data->getResponseBody(), true);

            // Should check the output here but we'll assume it is good...
            $vars = $response['response']['jobs'];

            // As vars is an array of jobs values it will magically be converted to tags via:
            return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $vars);

        } else {

            // Generic error message if we don't get a list of IDs returned e.g. parameters are malformed, or API error
            ee()->output->fatal_error('Something went wrong!');
        }

    }

	/*
	 * Stub of a potential method that would list a single order
     * Get the order id from the URL segment e.g. /orders/12345 and pass it to getJobsById([$order_id]) as above
     * See https://docs.expressionengine.com/latest/development/legacy/libraries/uri.html
	 */ 
    public function listIndividualOrder()
    {
        // TODO implement this
    }

	/*
	 * Handle the incoming webhook from Gengo
	 */
    public function incomingWebhook()
    {   
        // Check if token is in the token query parameter and matches our config token
        if (!isset($_GET["token"]) || $_GET["token"] !== ee('Config')->get('translate:config.token')) {
            exit("You are not permitted to access this page");
        }

        // Get POST data
        $data = json_decode($_POST['job']);

        // Debugging callbacks as webhook debugging can be painful... Comment out or remove
        ee()->load->library('logger');
        ee()->logger->developer("Translate: Webhook fired for {$data->status}");

        // We are only interested in approved status for now
        if ($data->status === "approved") {
            // Find the model based on the custom data field which maps to the entry id
            $entry = ee('Model')->get('ChannelEntry')->filter('entry_id', $data->custom_data)->first();
            $entry->{ee('Config')->get('translate:config.fields.to')} = $data->body_tgt;
            $entry->save();
        }
    }
}
