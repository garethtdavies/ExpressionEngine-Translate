<?php

/**
 * Translate Module control panel
 */
class Translate_mcp
{

    public $addon_name;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $addon = ee('Addon')->get('translate');
        $this->addon_name = $addon->get('name');
    }

    /**
     *  Pages Main page
     */
    public function index()
    {
        // Build up output table
        $table = ee('CP/Table');
        $table->setColumns(
            array(
                'job_id',
                'date',
                'slug',
                'status',
                'tier',
                'credits',
            )
        );

        // Send the request to Gengo
        $jobs = ee("translate:jobs");
        $jobs->getJobs();

        $code = $jobs->getResponseCode();
        $response = json_decode($jobs->getResponseBody(), true);

        // Do a basic check on the response
        if ($response['opstat'] === "ok") {

            // Is there any data? If not utilise the no_results tag
            $table->setNoResultsText('no_orders', 'create_entry', ee('CP/URL', 'entry/create'));

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

            foreach ($response['response']['jobs'] as $job) {

                $edit_url = ee('CP/URL', 'addons/settings/translate/order/' . $job['job_id']);

                $output[] = array(
                    array(
                        'content' => $job['job_id'],
                        'href' => $edit_url,
                    ),
                    date("Y-m-d H:i:s", $job['ctime']),
                    $job['slug'],
                    $job['status'],
                    $job['tier'],
                    $job['credits'] . " " . $job['currency'],

                );
            }

            ee()->cp->add_js_script('file', 'cp/sort_helper');
            ee()->cp->add_js_script('plugin', 'ee_table_reorder');

            $table->setData($output);

            $vars['table'] = $table->viewData();

            return array(
                'heading' => "All orders",
                'breadcrumb' => array(
                    ee('CP/URL')->make('addons/settings/translate')->compile() => $this->addon_name,
                ),
                'body' => ee('View')->make('translate:index')->render($vars),
            );

        }
    }

    public function order()
    {

        // Demonstration how to further extend this addon

        return array(
            'heading' => "Edit order",
            'breadcrumb' => array(
                ee('CP/URL')->make('addons/settings/translate')->compile() => $this->addon_name,
            ),
            'body' => ee('View')->make('translate:action')->render(["job_id" => ee()->uri->segment(6)]),
        );

    }

}
