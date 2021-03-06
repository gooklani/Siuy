<?php
/**
 * Theme Customizer.
 *
 * @author  	Mahdi Yazdani
 * @package 	Siuy
 * @since 	    1.0.0
 */
if (!defined('ABSPATH')):
	exit;
endif;
if (!class_exists('Siuy_Customizer')):
	/**
	 * The main Siuy Customizer class
	 */
	class Siuy_Customizer

	{
		/**
		 * Setup class.
		 *
		 * @since 1.0.0
		 */
		public function __construct()

		{
			add_action('customize_preview_init', array(
				$this,
				'preview_js'
			) , 0);
			add_action('customize_controls_enqueue_scripts', array(
				$this,
				'enqueue'
			) , 0);
			add_action('customize_register', array(
				$this,
				'customize_register'
			) , 10, 1);
		}
		/**
		 * Binds JS handlers to make theme Customizer preview reload changes asynchronously.
		 *
		 * @since 1.0.0
		 */
		public function preview_js()

		{
			wp_enqueue_script('siuy-customizer', get_theme_file_uri('/assets/admin/js/customizer.js') , array(
				'customize-preview'
			) , SIUY_THEME_VERSION, true);
		}
		/**
		 * Some extra JavaScript to improve the user experience in the Customizer for Siuy theme.
		 *
		 * @since 1.0.0
		 */
		public function enqueue()

		{
			wp_enqueue_style('siuy-panel-customizer-styles', get_theme_file_uri('/assets/admin/css/panel-customizer.css') , array() , SIUY_THEME_VERSION);
		}
		/**
		 * Theme Customizer along with several other settings.
		 *
		 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
		 * @since 1.0.0
		 */
		public function customize_register($wp_customize)

		{
			// Load Customizer custom controls.
			require_once dirname(__FILE__) . '/class-siuy-customizer-custom-controls.php';
			
			/**
			 * "Layout" section
			 *
			 * @since 1.0.0
			 */
			$wp_customize->add_section('siuy_layout_sec', array(
				'title' => __('Layout', 'siuy') ,
				'capability' => 'edit_theme_options',
				'priority' => 70
			));
			$wp_customize->add_setting('siuy_layout_sidebar', array(
				'default' => apply_filters('siuy_layout_sidebar_default_value', 'right-sidebar') ,
				'capability' => 'edit_theme_options',
				'sanitize_callback' => array(
					$this,
					'sanitize_choices'
				)
			));
			$wp_customize->add_control(new Siuy_Radio_Image_Control($wp_customize, 'siuy_layout_sidebar', array(
				'label' => __('General Layout', 'siuy') ,
				'description' => __('Reposition sidebar area from right to left or vice versa.', 'siuy') ,
				'section' => 'siuy_layout_sec',
				'settings' => 'siuy_layout_sidebar',
				'priority' => 10,
				'choices' => array(
					'left-sidebar' => get_theme_file_uri('/assets/admin/img/left-sidebar.png') ,
					'right-sidebar' => get_theme_file_uri('/assets/admin/img/right-sidebar.png')
				)
			)));
			/**
			 * Render updates by JavaScript without reloading the entire preview window
			 *
			 * @since 1.0.0
			 */
			$wp_customize->get_setting('blogname')->transport = 'postMessage';
			$wp_customize->get_setting('blogdescription')->transport = 'postMessage';
			$wp_customize->get_setting('header_textcolor')->transport = 'postMessage';
			if (isset($wp_customize->selective_refresh)):
				// Site title
				$wp_customize->selective_refresh->add_partial('blogname', array(
					'selector' => '.site-title a',
					'render_callback' => function ()
					{
						return bloginfo('name');
					}
				));
				// Tagline
				$wp_customize->selective_refresh->add_partial('blogdescription', array(
					'selector' => '.site-description',
					'render_callback' => function ()
					{
						return bloginfo('description');
					}
				));
			endif;
		}
		/**
		 * Sanitizes choices (selects/radios)
		 * Checks that the input matches one of the available choices
		 *
		 * @since 1.0.0
		 */
		public function sanitize_choices($input, $setting)

		{
			// Ensure input is a slug.
			$input = sanitize_key($input);
			// Get list of choices from the control associated with the setting.
			$choices = $setting->manager->get_control($setting->id)->choices;
			// If the input is a valid key, return it; otherwise, return the default.
			return (array_key_exists($input, $choices) ? $input : $setting->default);
		}
		/**
		 * Add extra CSS styles to a registered stylesheet.
		 *
		 * @since 1.1.0
		 */
		public static function inline_style()

		{
			$customizer_css = '';
			$display_header_text = (bool)display_header_text();
			$header_text_color = get_header_textcolor();
			// Has the text been hidden?
			if (!$display_header_text):
				$customizer_css.= "
		            .site-title, .site-description {
		        		position: absolute;
		        		clip: rect(1px, 1px, 1px, 1px);
		        	}
	        	";
			endif;
			/*
			* If no custom options for text are set, let's bail.
			* get_header_textcolor() options: Any hex value, 'blank' to hide text.
			*/
			if (get_theme_support('custom-header', 'default-text-color') !== $header_text_color):
				$customizer_css.= "
		            .site-title a, .site-description {
	            		color: #{$header_text_color};
	            	}
	        	";
			endif;
			return (!empty($customizer_css)) ? wp_strip_all_tags($customizer_css, false) : '';
		}
	}
endif;
return new Siuy_Customizer();