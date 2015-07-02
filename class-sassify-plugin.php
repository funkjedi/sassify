<?php

require_once SASSIFY_PLUGIN_DIR . 'class-sassify-admin.php';
require_once SASSIFY_PLUGIN_DIR . 'vendor/scssphp/scss.inc.php';

class sassify_plugin {

	/**
	 * An array of any compilation errors.
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Create an instance.
	 * @return void
	 */
	public function __construct() {
		$this->admin = new sassify_admin;

		add_action('wp_footer', array($this, 'wp_footer'));
		add_filter('style_loader_src', array($this, 'style_loader_src'), 10, 2);
	}

	/**
	 * Hook into wp_enqueue_style to compile stylesheets
	 */
	public function style_loader_src($src, $handle) {

		// Quick check for SCSS files
		if (strpos($src, 'scss') === false) {
			return $src;
		}

		$url = parse_url($src);
		$pathinfo = pathinfo($url['path']);

		// Detailed check for SCSS files
		if ($pathinfo['extension'] !== 'scss') {
			return $src;
		}

		// Convert site URLs to absolute paths
		$in = preg_replace('/^' . preg_quote(site_url(), '/') . '/i', '', $src);

		// Ignore SCSS from CDNs, other domains and relative paths
		if (preg_match('#^//#', $in) || strpos($in, '/') !== 0) {
			return $src;
		}

		// Create a complete path
		$in = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $url['path'];

		// Check and make sure the file exists
		if (file_exists($in) === false) {
			array_push($this->errors, array(
				'file'    => basename($in),
				'message' => 'Source file not found.',
			));
			return $src;
		}

		// Generate unique filename for output
		$outName = sha1($src) . '.css';

		$wp_upload_dir = wp_upload_dir();
		$outputDir = $wp_upload_dir['basedir'] . '/sassify/';
		$outputUrl = $wp_upload_dir['baseurl'] . '/sassify/' . $outName;

		// Create the output directory if it doesn't exisit
		if (is_dir($outputDir) === false) {
			if (wp_mkdir_p($outputDir) === false) {
				array_push($this->errors, array(
					'file'    => 'Cache Directory',
					'message' => 'File Permissions Error, unable to create cache directory. Please make sure the Wordpress Uploads directory is writable.',
				));
				return $src;
			}
		}

		// Check that the output directory is writable
		if (is_writable($outputDir) === false) {
			array_push($this->errors, array(
				'file'    => 'Cache Directory',
				'message' => 'File Permissions Error, permission denied. Please make the cache directory writable.',
			));
			return $src;
		}

		// Full output path
		$out = $outputDir . '/' . $outName;

		// Flag if a compile is required
		$compileRequired = $this->admin->get_setting('always_compile', false);

		// Retrieve cached filemtimes
		if (($filemtimes = get_transient('sassify_filemtimes')) === false) {
			$filemtimes = array();
		}

		// Check if compile is required based on file modification times
		if ($compileRequired === false) {
			if (isset($filemtimes[$out]) === false || $filemtimes[$out] < filemtime($in)) {
				$compileRequired = true;
			}
		}

		// Retrieve variables
		$variables = apply_filters('sassify_compiler_variables', array(
			'template_directory_uri'   => get_template_directory_uri(),
			'stylesheet_directory_uri' => get_stylesheet_directory_uri(),
		));

		// If variables have been updated then recompile
		if ($compileRequired === false) {
			$signature = sha1(serialize($variables));
			if ($signature !== get_transient('sassify_variables_signature')) {
				$compileRequired = true;
				set_transient('sassify_variables_signature', $signature);
			}
		}

		// Check if the stylesheet needs to be recompiled
		if ($compileRequired) {
			$compiler = new Leafo_ScssPhp_Compiler;
			$compiler->setFormatter($this->admin->get_setting('compiling_mode', 'Leafo_ScssPhp_Formatter_Expanded'));
			$compiler->setVariables($variables);
			$compiler->setImportPaths(dirname($in));

			try {
				// Compile the SCSS to CSS
				$css = $compiler->compile(file_get_contents($in));
			}
			catch (Exception $e) {
				array_push($this->errors, array(
					'file'    => basename($in),
					'message' => $e->getMessage(),
				));
				return $src;
			}

			// Transform relative paths so they still work correctly
			$css = preg_replace('#(url\((?![\'"]?(?:https?:|/))[\'"]?)#miu', '$1' . dirname($url['path']) . '/', $css);

			// Save the CSS
			file_put_contents($out, $css);

			// Cache the filemtime for the destination file
			$filemtimes[$out] = filemtime($out);
			set_transient('sassify_filemtimes', $filemtimes);
		}

		// Build URL with query string
		return empty($url['query']) ? $outputUrl : $outputUrl . '?' . $url['query'];
	}

	/**
	 * Output any errors in the footer.
	 */
	public function wp_footer() {
		if (count($this->errors)) {
			switch ($this->admin->get_setting('errors_mode')) {
	 			case 'error_log':
					$this->logErrors();
					break;

				default:
					$this->displayErrors();
			}
		}
	}

	/**
	 * Display HTML formatted errors.
	 */
	protected function displayErrors() {
		?>
		<style>
		#sassify {
			position: fixed;
			top: 0;
			z-index: 99999;
			width: 100%;
			padding: 20px;
			overflow: auto;
			background: #f5f5f5;
			font-family: 'Source Code Pro', Menlo, Monaco, Consolas, monospace;
			font-size: 18px;
			color: #666;
			text-align: left;
			border-left: 5px solid #DD3D36;
		}
		body.admin-bar #sassify {
			top: 32px;
		}
		#sassify .sassify-title {
			margin-bottom: 20px;
			font-size: 120%;
		}
		#sassify .sassify-error {
			margin: 10px 0;
		}
		#sassify .sassify-file {
			font-weight: bold;
			white-space: pre;
			white-space: pre-wrap;
			word-wrap: break-word;
		}
		#sassify .sassify-message {
			white-space: pre;
			white-space: pre-wrap;
			word-wrap: break-word;
		}
		</style>
		<div id="sassify">
			<div class="sassify-title">Sass Compiling Error</div>
			<?php foreach($this->errors as $error): ?>
				<div class="sassify-error">
					<div class="sassify-file"><?php print $error['file'] ?></div>
					<div class="sassify-message"><?php print $error['message'] ?></div>
				</div>
			<?php endforeach ?>
		</div>
		<?php
	}

	/**
	 * Log errors.
	 */
	protected function logErrors() {
		foreach($this->errors as $error) {
			error_log($error['file'] . ': ' . $error['message']);
		}
	}

}
