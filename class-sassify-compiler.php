<?php

require_once SASSIFY_PLUGIN_DIR . 'vendor/scssphp/scss.inc.php';

class sassify_compiler extends Leafo_ScssPhp_Compiler {

	/**
	 * Create an instance.
	 * @return void
	 */
	public function __construct($importPath, $formatter = 'Leafo_ScssPhp_Formatter_Expanded') {

		$this->setImportPaths($importPath);
		$this->setFormatter($formatter);

		$this->setVariables(apply_filters('sassify_compiler_variables', array(
			'template_directory_uri'   => get_template_directory_uri(),
			'stylesheet_directory_uri' => get_stylesheet_directory_uri(),
		)));

	}

}
