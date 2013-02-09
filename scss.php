<?php
/*
Plugin Name: SCSS
Plugin URI: https://github.com/funkjedi/SCSS
Description: Adds SCSS support to Wordpress.
Version: 0.1.0
Author: Tim Robertson
Author URI: http://github.com/funkjedi
License: GPLv2 or later
*/

require_once dirname(__FILE__) . '/vendor/scssphp/scss.inc.php';



$wp_scss_plugin = new Wordpress_SCSS_Plugin();

class Wordpress_SCSS_Plugin
{
	private $instance;

	protected $upload_dir;
	protected $upload_url;
	protected $template_dir;
	protected $template_url;
	protected $site_url;

	protected $parser;
	protected $filemtimes;
	protected $filemtimes_flagged_for_update;
	protected $force_stylesheet_compilation;


	public function __construct()
	{
		$wp_upload_dir = wp_upload_dir();
		$this->upload_dir = $wp_upload_dir['path'];
		$this->upload_url = $wp_upload_dir['url'];
		$this->template_dir = get_stylesheet_directory();
		$this->template_url = get_stylesheet_directory_uri();
		$this->site_url = site_url();

		$this->filemtimes = get_option('scss_filemtimes', array());

		$this->parser = new scssc();
		$this->parser->setImportPaths($this->template_dir);
		$this->parser->registerFunction('get_option', array($this, 'function_get_option'));
		$this->parser->registerFunction('theme_url',  array($this, 'function_theme_url'));

		add_action('wp_footer', array($this, 'wp_footer'), 1000);
		add_filter('style_loader_src', array($this, 'style_loader_src'), 10, 2);
	}


	public function wp_footer()
	{
		if ($this->filemtimes_flagged_for_update) {
			update_option('scss_filemtimes', $this->filemtimes);
		}
		if ($this->force_stylesheet_compilation) {
			update_option('scss_force_stylesheet_compilation', 0);
		}
	}

	public function style_loader_src($src, $handle)
	{
		// quick check for scss files
		if (strpos($src, 'scss') === false) {
			return $src;
		}

		$in = preg_replace("#^" . preg_quote($this->site_url) . "#i", "", $src);

		$parts = parse_url($in);
		$paths = pathinfo($parts['path']);

		// detailed check for scss files
		if ($paths['extension'] !== 'scss') {
			return $src;
		}

		$in = $parts['path'];
		$out = $this->upload_dir . '/' . $paths['filename'] . '.css';

		// construct a complete path
		if (strpos($in, '/') === 0) {
			$in = $_SERVER['DOCUMENT_ROOT'] . $in;
		}
		else {
			$in = $this->template_dir . '/' . $in;
		}

		// check file modification times
		$filemtime_in = filemtime($in);
		$filemtime_out = -1;
		if (file_exists($out)) {
			if (isset($this->filemtimes[$out])) {
				$filemtime_out = $this->filemtimes[$out];
			}
			else {
				$filemtime_out = filemtime($out);
				$this->filemtimes[$out] = $filemtime_out;
				$this->filemtimes_flagged_for_update = true;
			}
		}

		if (!$this->force_stylesheet_compilation) {
			$this->force_stylesheet_compilation = get_option('scss_force_stylesheet_compilation', 0);
		}

		// compile scss
		if ($filemtime_in > $filemtime_out || $this->force_stylesheet_compilation) {
			try {
				$data = file_get_contents($in);
				file_put_contents($out, $this->parser->compile($data));
			}
			catch (Exception $e) {
				print "\n<!--\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n-->";
				return $src;
			}
		}

		return $this->upload_url . '/' . $paths['filename'] . '.css?' . $parts['query'];
	}



	public function function_get_option($value)
	{
		$option = get_option($value[0][2][0], "");

		// cast color values to scss colors
		if (preg_match('/^\s*(#([0-9a-f]{6})|#([0-9a-f]{3}))\s*$/Ais', $option, $matches)) {
			$color = array('color');
			if (isset($matches[3])) {
				$num = $matches[3];
				$width = 16;
			} else {
				$num = $matches[2];
				$width = 256;
			}
			$num = hexdec($num);
			foreach (array(3,2,1) as $i) {
				$t = $num % $width;
				$num /= $width;
				$color[$i] = $t * (256/$width) + $t * floor(16/$width);
			}
			$option = $color;
		}

		return $option;
	}

	public function function_theme_url($value)
	{
		return "url({$this->theme_url}/" . $value[0][2][0][2][0] . ")";
	}

}



function scss_force_stylesheet_compilation()
{
	update_option('scss_force_stylesheet_compilation', 1);
}
