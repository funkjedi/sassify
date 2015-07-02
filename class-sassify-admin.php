<?php

class sassify_admin {

	/**
	 * Create an instance.
	 * @return void
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_init', array($this, 'admin_init'));

		add_filter('plugin_action_links_' . plugin_basename(SASSIFY_PLUGIN), array($this, 'plugin_action_links'));
	}

	/**
	 * Add settings link on plugin page.
	 * @param array
	 * @return array
	 */
	public function plugin_action_links($links) {
		array_unshift($links, '<a href="options-general.php?page=sassify">Settings</a>');
		return $links;
	}

	/**
	 * Retrieve the value of a plugin setting.
	 * @return mixed
	 */
	function get_setting($name, $default = null) {
		$options = get_option('sassify');
		if (isset($options[$name]) === true) {
			return $options[$name];
		}
		return $default;
	}

	/**
	 * Register the options page with the Wordpress menu.
	 */
	function admin_menu() {
		add_options_page('Sassify', 'Sassify', 'manage_options', 'sassify', array($this, 'options_page'));
	}

	/**
	 * Register settings and default fields.
	 */
	function admin_init() {
		register_setting('sassify', 'sassify');

		// Compiling Options
		add_settings_section(
			'sassify_compile_section',
			'Compiling Options',
			array($this, 'section_compiling_options'),
			'sassify'
		);
		add_settings_field(
			'Always Compile',
			'Always Compile',
			array($this, 'field_always_compile'),
			'sassify',
			'sassify_compile_section'
		);
		add_settings_field(
			'Compiling Mode',
			'Compiling Mode',
			array($this, 'field_compiling_mode'),
			'sassify',
			'sassify_compile_section'
		);
		add_settings_field(
			'Error Display',
			'Error Display',
			array($this, 'field_errors_mode'),
			'sassify',
			'sassify_compile_section'
		);
	}

	/**
	 * Render the options page.
	 */
	function options_page() {
		?>
		<form action="options.php" method="post">
			<div class="wrap">
				<h2>Sassify Settings</h2>
				<p>
					Sassify works by automatically compiling and caching SCSS stylesheets added using <b>wp_enqueue_style</b>.<br>
					<em>Note: compiling stylesheets hosted on CDNs or other domains is <b><u>NOT</u></b> supported.</em>
				</p>
				<br>
				<?php
					settings_fields('sassify');
					do_settings_sections('sassify');
					submit_button();
				?>
			</div>
		</form>
		<?php
	}

	/**
	 * Render the compiling options section.
	 */
	public function section_compiling_options() {
	?>
	<?php
	}

	/**
	 * Render the always compile field.
	 */
	function field_always_compile() {
		$always_compile = $this->get_setting('always_compile', 0);
		?>
		<input type="checkbox" id="always_compile" name="sassify[always_compile]" <?php checked($always_compile, 1); ?> value="1">
		<label for="always_compile">Enabled</label>
		<div style="margin-top:8px;font-size:80%;font-style:italic;line-height:1.2;">
			When enabled stylesheets will always be compiled for each request<br>
			regardless of whether it has been updated or not.
		</div>
		<?php
	}

	/**
	 * Render the compiling mode field.
	 */
	public function field_compiling_mode() {
		$compiling_mode = $this->get_setting('compiling_mode', 'Leafo_ScssPhp_Formatter_Expanded');
		?>
		<select id="compiling_options" name="sassify[compiling_mode]">
			<option value="Leafo_ScssPhp_Formatter_Compact"    <?php selected($compiling_mode, 'Leafo_ScssPhp_Formatter_Compact')    ?>>Compact</option>
			<option value="Leafo_ScssPhp_Formatter_Compressed" <?php selected($compiling_mode, 'Leafo_ScssPhp_Formatter_Compressed') ?>>Compressed</option>
			<option value="Leafo_ScssPhp_Formatter_Crunched"   <?php selected($compiling_mode, 'Leafo_ScssPhp_Formatter_Crunched')   ?>>Crunched</option>
			<option value="Leafo_ScssPhp_Formatter_Expanded"   <?php selected($compiling_mode, 'Leafo_ScssPhp_Formatter_Expanded')   ?>>Expanded</option>
			<option value="Leafo_ScssPhp_Formatter_Nested"     <?php selected($compiling_mode, 'Leafo_ScssPhp_Formatter_Nested')     ?>>Nested</option>
		</select>
		<?php
	}

	/**
	 * Render the errors mode field.
	 */
	public function field_errors_mode() {
		$errors_mode = $this->get_setting('errors_mode', 'in_header');
		?>
		<select id="errors_mode" name="sassify[errors_mode]">
			<option value="in_header" <?php selected($errors_mode, 'in_header') ?>>Show in Header</option>
			<option value="error_log" <?php selected($errors_mode, 'error_log') ?>>Show in Error Log</option>
		</select>
		<?php
	}

}
