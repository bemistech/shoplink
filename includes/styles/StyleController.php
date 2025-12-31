<?php

namespace Shoplink;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Shoplink Global Styles
 */
class StyleController {
    
    /**
     * Default Styles
     */
    private static $defaults = [
        'primary_color'   => '#000000', // Button BG
        'secondary_color' => '#ffffff', // Button Text
        'text_color'      => '#333333',
        'heading_color'   => '#111111',
        'link_color'      => '#000000',
    ];

    public static function init() {
        add_action( 'wp_head', [ __CLASS__, 'render_css_variables' ], 10 );
        // Also render in admin for builder previews if needed, though they might have their own hooks
        add_action( 'admin_head', [ __CLASS__, 'render_css_variables' ], 10 );
    }

    /**
     * Get merged styles from DB and defaults
     */
    public static function get_styles() {
        $saved = get_option( 'shoplink_styles', [] );
        if ( ! is_array( $saved ) ) {
            $saved = [];
        }

        // Merge defaults
        $styles = array_merge( self::$defaults, $saved );

        // Breakdance Overrides
        if ( function_exists( '\Breakdance\Settings\getGlobalSettings' ) ) {
            $bd_settings = \Breakdance\Settings\getGlobalSettings('shoplink');
            if ( $bd_settings && is_array( $bd_settings ) ) {
                // Map fields if necessary, assuming keys match
                foreach ( $bd_settings as $key => $value ) {
                    if ( ! empty( $value ) && isset( $styles[ $key ] ) ) {
                        $styles[ $key ] = $value;
                    }
                }
            }
        }

        return $styles;
    }

    /**
     * Output CSS Variables to <head>
     */
    public static function render_css_variables() {
        $styles = self::get_styles();

        echo '<style id="shoplink-global-styles">:root {';
        
        echo '--shoplink-primary-color: ' . esc_attr( $styles['primary_color'] ) . ';';
        echo '--shoplink-secondary-color: ' . esc_attr( $styles['secondary_color'] ) . ';';
        echo '--shoplink-text-color: ' . esc_attr( $styles['text_color'] ) . ';';
        echo '--shoplink-heading-color: ' . esc_attr( $styles['heading_color'] ) . ';';
        echo '--shoplink-link-color: ' . esc_attr( $styles['link_color'] ) . ';';

        echo '}</style>';
    }
}
