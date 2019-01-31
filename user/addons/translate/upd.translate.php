<?php

/**
 * Translate Module update class
 */

class Translate_upd {

	public $version;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$addon = ee('Addon')->get('translate');
		$this->version = $addon->getVersion();
	}

	/**
	 * Module Installer
	 *
	 * @return	bool
	 */
	function install()
	{
		ee('Model')->make('Module', [
			'module_name' => 'Translate',
			'module_version' => $this->version,
			'has_cp_backend' => TRUE,
			'has_publish_fields' => FALSE,
		])->save();

		// Insert into actions table

		 ee('Model')->make('Action', [
			'class' => 'Translate',
			'method' => 'incomingWebhook',
			'csrf_exempt' => 1
		 ])->save();

		return TRUE;
	}

	/**
	 * Module Uninstaller
	 *
	 * @return	bool
	 */
	function uninstall()
	{
		ee('Model')->get('Module')->filter('module_name', 'Translate')->delete();

		// delete actions model
		ee('Model')->get('Action')->filter('class', 'Translate')->delete();

		return TRUE;
	}

	/**
	 * Module Updater
	 *
	 * @return	bool
	 */
	public function update($current='')
	{
		return TRUE;
	}
}
// END CLASS

// EOF
