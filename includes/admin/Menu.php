<?php

namespace Shoplink\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Admin Menu
 */
class Menu {
    
    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function register_menu() {
        add_menu_page(
            'Shoplink',
            'Shoplink',
            'manage_options',
            'shoplink',
            [ __CLASS__, 'render_page' ],
            'dashicons-cart',
            58
        );
    }

    public static function enqueue_assets( $hook ) {
        if ( 'toplevel_page_shoplink' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'shoplink-admin-js', plugins_url( 'assets/admin.js', __FILE__ ), [ 'jquery', 'wp-color-picker' ], '1.0.0', true );
    }

    public static function render_page() {
        SettingsPage::render();
    }
}
