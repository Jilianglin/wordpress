<?php

class Themify_Tooltips {

	public static function init() {
		if ( current_user_can( 'publish_posts' ) && get_user_option( 'rich_editing' ) == 'true' ) {
			add_filter( 'mce_external_plugins', array( __CLASS__, 'mce_external_plugins' ) );
			add_filter( 'mce_buttons', array( __CLASS__, 'mce_buttons' ) );
			add_filter( 'mce_css', array( __CLASS__, 'mce_css' ) );
			add_action( 'wp_enqueue_editor', array( __CLASS__, 'wp_enqueue_editor' ) );
		}
	}

	/**
	 * Add button to WP Editor.
	 *
	 * @param array $mce_buttons
	 * @return mixed
	 */
	public static function mce_buttons( $mce_buttons ) {
		array_push( $mce_buttons, 'separator', 'btnthemifyTooltip' );
		return $mce_buttons;
	}

	public static function mce_css( $mce_css ) {
		if ( ! empty( $mce_css ) ) {
			$mce_css .= ',';
		}
		$mce_css .= themify_enque( Themify_Enqueue_Assets::$THEMIFY_CSS_MODULES_URI . 'tooltip.css' );

		return $mce_css;
	}

	/**
	 * Add plugin JS file to list of external plugins.
	 *
	 * @param array $mce_external_plugins
	 * @return mixed
	 */
	public static function mce_external_plugins( $mce_external_plugins ) {
		global $wp_version;
		$mce_external_plugins['themifyTooltip'] = themify_enque( THEMIFY_URI . '/js/admin/tooltips-tinymce.js' );

		return $mce_external_plugins;
	}

	/**
	 * Pass strings to JS to set the labels of the WP Editor shortcode button and menu.
	 *
	 * @since 1.8.9
	 */
	public static function wp_enqueue_editor() {
		wp_localize_script( 'editor', 'themifyTooltip', array(
			'icon' => THEMIFY_URI . '/img/tooltip.svg',
			'i18n' => array(
				'menuTooltip' => __( 'Tooltip', 'themify' ),
				'windowTitle' => __( 'Insert Tooltip', 'themify' ),
				'textLbl' => __( 'Text', 'themify' ),
				'tooltipLbl' => __( 'Tooltip Content', 'themify' ),
			)
		));
	}
}