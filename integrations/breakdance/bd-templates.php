<?php

namespace Shoplink\Integrations\Breakdance;

use Shoplink\ShoplinkAPI;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Template Types
 */
add_action(
    'breakdance_register_template_types_and_conditions',
    __NAMESPACE__ . '\\register_shopify_templates'
);

function register_shopify_templates() {
    if ( ! function_exists( '\Breakdance\Themeless\registerTemplateType' ) ) {
        return;
    }

    \Breakdance\Themeless\registerTemplateType(
        'Shopify',
        [
            'slug' => 'shopify-single-product',
            'label' => 'Shopify Single Product',
            'callback' => function() {
                return get_query_var( 'is_shopify_product_template' );
            },
            'templatePreviewableItems' => function() {
                return get_shopify_product_preview_items();
            },
            'defaultPriority' => 120, // Arbitrary priority
        ]
    );
}

/**
 * Get Preview Items for Shopify Products
 */
function get_shopify_product_preview_items() {
    $products = ShoplinkAPI::get_products_filtered(['limit' => 20]);
    
    if ( is_wp_error( $products ) || empty( $products ) ) {
        return [];
    }

    $items = [];
    foreach ( $products as $product ) {
        $handle = $product['handle'];
        // Ensure URL matches the rewrite rule structure: /products/{handle}
        $url = home_url( '/products/' . $handle );

        $items[] = [
            'label' => $product['title'],
            'value' => $handle,
            'url'   => $url,
            'type'  => 'Shopify Product'
        ];
    }

    return $items;
}
