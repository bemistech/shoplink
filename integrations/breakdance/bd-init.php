<?php

namespace Shoplink\Integrations\Breakdance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize Breakdance Integration
 */
function init() {
    register_elements();
    register_dynamic_data();
    add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts' );
    
    // Load Templates
    require_once dir() . 'bd-templates.php';

    // Load Settings
    require_once dir() . 'bd-settings.php';
}

/**
 * Register Elements
 */
function register_elements() {
	if ( ! function_exists( 'Breakdance\\ElementStudio\\registerElementForEditing' ) ) {
		return;
	}

    $elements = glob( dir() . 'elements/*/element.php' );
    foreach ( $elements as $element ) {
        require_once $element;
    }
}

/**
 * Enqueue Scripts
 */
function enqueue_scripts() {
    wp_enqueue_script( 'shoplink-frontend', plugins_url( 'assets/frontend.js', __FILE__ ), [], '1.0.0', true );
    wp_enqueue_style( 'shoplink-frontend', plugins_url( 'assets/frontend.css', __FILE__ ), [], '1.0.0' );
}

/**
 * Register Dynamic Data
 */
function register_dynamic_data() {
    if ( ! class_exists( '\Breakdance\DynamicData\DynamicDataController' ) ) {
        return;
    }

    require_once dir() . 'dynamic-data.php';

    $fields = [
        new \Shoplink\DynamicData\ShopifyProductTitleField(),
        new \Shoplink\DynamicData\ShopifyProductDescriptionField(),
        new \Shoplink\DynamicData\ShopifyProductPriceField(),
        new \Shoplink\DynamicData\ShopifyProductHandleField(),
        new \Shoplink\DynamicData\ShopifyProductUrlField(),
        new \Shoplink\DynamicData\ShopifyProductImageField(),
    ];

    foreach ($fields as $field) {
        \Breakdance\DynamicData\DynamicDataController::getInstance()->registerField($field);
    }
}

/**
 * Helper to get current directory path with trailing slash
 */
function dir() {
    return plugin_dir_path( __FILE__ );
}

// Start Integration
init();
