<?php
/**
 * Plugin Name: Shoplink
 * Description: Improved Shopify Integration for WordPress.
 * Version: 0.6.0
 * Author: Luxibay
 * Requires Plugins: shopify-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHOPLINK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHOPLINK_URL', plugin_dir_url( __FILE__ ) );

require_once SHOPLINK_PATH . 'api.php';
require_once SHOPLINK_PATH . 'includes/search.php'; // Search Integration
require_once SHOPLINK_PATH . 'includes/styles/StyleController.php';
require_once SHOPLINK_PATH . 'includes/admin/Menu.php';
require_once SHOPLINK_PATH . 'includes/admin/SettingsPage.php';

// Initialize Global Components
\Shoplink\StyleController::init();
if ( is_admin() ) {
    \Shoplink\Admin\Menu::init();
}

/**
 * Load Integrations
 */
/**
 * Load Integrations
 */
function shoplink_load_integrations() {
    require_once SHOPLINK_PATH . 'integrations/breakdance/init.php';
}
add_action( 'breakdance_loaded', 'shoplink_load_integrations' );

/**
 * Query Shopify Products Helper (Migrated from mu-plugin)
 * 
 * @param array $args Arguments for the query.
 * @return array|WP_Error List of products or error.
 */
function shoplink_query_products( $args = [] ) {
    // Ensure Shopify plugin is active and class exists
	if ( ! class_exists( 'Shopify_Wp_GraphQL' ) ) {
		// Try to load it if it's not loaded yet
		if ( defined( 'SHOPIFY_PLUGIN_DIR' ) && file_exists( SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php' ) ) {
			require_once SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php';
		} else {
			return new WP_Error( 'shopify_plugin_missing', 'Shopify Plugin not found or active.' );
		}
	}

	$defaults = [
		'limit'             => 10,
		'collection_handle' => '',
		'query'             => '',
		'sortKey'           => 'BEST_SELLING',
		'reverse'           => false,
	];

	$args = wp_parse_args( $args, $defaults );

	$store_url = get_option( 'shopify_store_url' );
	$api_key   = get_option( 'shopify_api_key' );

	if ( empty( $store_url ) || empty( $api_key ) ) {
		return new WP_Error( 'shopify_credentials_missing', 'Shopify credentials missing.' );
	}

	Shopify_Wp_GraphQL::init( $store_url, $api_key );

	$query_string = '
    query GetProducts($first: Int!, $query: String, $sortKey: ProductSortKeys, $reverse: Boolean) {
        products(first: $first, query: $query, sortKey: $sortKey, reverse: $reverse) {
            edges {
                node {
                    id
                    title
                    handle
                    description
                    priceRange {
                        minVariantPrice {
                            amount
                            currencyCode
                        }
                    }
                    featuredImage {
                        url
                        altText
                    }
                    onlineStoreUrl
                }
            }
        }
    }';

	if ( ! empty( $args['collection_handle'] ) ) {
		$query_string = '
        query GetCollectionProducts($handle: String!, $first: Int!, $sortKey: ProductCollectionSortKeys, $reverse: Boolean) {
            collection(handle: $handle) {
                products(first: $first, sortKey: $sortKey, reverse: $reverse) {
                    edges {
                        node {
                            id
                            title
                            handle
                            description
                            priceRange {
                                minVariantPrice {
                                    amount
                                    currencyCode
                                }
                            }
                            featuredImage {
                                url
                                altText
                            }
                            onlineStoreUrl
                        }
                    }
                }
            }
        }';
	}

	$variables = [
		'first'   => (int) $args['limit'],
		'sortKey' => $args['sortKey'],
		'reverse' => (bool) $args['reverse'],
	];

	if ( ! empty( $args['collection_handle'] ) ) {
		$variables['handle'] = $args['collection_handle'];
		if ( $variables['sortKey'] === 'CREATED_AT' ) {
			$variables['sortKey'] = 'CREATED';
		}
	} else {
		$variables['query'] = $args['query'];
	}

	$result = Shopify_Wp_GraphQL::execute_query( $query_string, $variables );

	if ( is_wp_error( $result ) ) {
		return $result;
	}
    
    // Safety check for empty data
    if ( empty($result['data']) ) {
        return [];
    }

	$products_data = [];
	if ( ! empty( $args['collection_handle'] ) ) {
		if ( isset( $result['data']['collection']['products']['edges'] ) ) {
			$products_data = $result['data']['collection']['products']['edges'];
		}
	} else {
		if ( isset( $result['data']['products']['edges'] ) ) {
			$products_data = $result['data']['products']['edges'];
		}
	}

	$products = array_map( function( $edge ) {
		return $edge['node'];
	}, $products_data );

	return $products;
}
