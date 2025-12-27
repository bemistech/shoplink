<?php
/**
 * Plugin Name: Breakdance Shopify Integration
 * Description: Adds Shopify elements to Breakdance Builder.
 * Version: 0.5.0
 * Author: Luxibay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BREAKDANCE_SHOPIFY_PATH', plugin_dir_path( __FILE__ ) );
define( 'BREAKDANCE_SHOPIFY_URL', plugin_dir_url( __FILE__ ) );

require_once BREAKDANCE_SHOPIFY_PATH . 'api.php';

/**
 * Register Custom Elements
 */
function breakdance_shopify_register_elements() {
	if ( ! function_exists( 'Breakdance\\ElementStudio\\registerElementForEditing' ) ) {
		return;
	}



	require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyProducts/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyProductTitle/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyProductPrice/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyProductImage/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyProductDescription/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyAddToCart/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyProductBuilder/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyProductGallery/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyVariantSelector/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyQuantityInput/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyBuyNowButton/element.php';
    // require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyProductList/element.php'; // Deleted
    // require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyRepeater/element.php'; // Deleted
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyCollections/element.php';
    // require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyCollectionTitle/element.php';
    // require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyCollectionImage/element.php';
    // require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyCollectionDescription/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyPage/element.php';
    require_once BREAKDANCE_SHOPIFY_PATH . 'elements/ShopifyFilters/element.php';
}
add_action( 'breakdance_loaded', 'breakdance_shopify_register_elements' );

function breakdance_shopify_enqueue_scripts() {
    wp_enqueue_script( 'breakdance-shopify-frontend', BREAKDANCE_SHOPIFY_URL . 'frontend.js', [], '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'breakdance_shopify_enqueue_scripts' );



/**
 * Register Dynamic Data Fields
 */
function breakdance_shopify_register_dynamic_data() {
    if ( ! class_exists( '\Breakdance\DynamicData\DynamicDataController' ) ) {
        return;
    }

    require_once BREAKDANCE_SHOPIFY_PATH . 'dynamic-data.php';

    $fields = [
        new \BreakdanceShopify\DynamicData\ShopifyProductTitleField(),
        new \BreakdanceShopify\DynamicData\ShopifyProductDescriptionField(),
        new \BreakdanceShopify\DynamicData\ShopifyProductPriceField(),
        new \BreakdanceShopify\DynamicData\ShopifyProductHandleField(),
        new \BreakdanceShopify\DynamicData\ShopifyProductUrlField(),
        new \BreakdanceShopify\DynamicData\ShopifyProductImageField(),
    ];

    foreach ($fields as $field) {
        \Breakdance\DynamicData\DynamicDataController::getInstance()->registerField($field);
    }
}
add_action( 'breakdance_loaded', 'breakdance_shopify_register_dynamic_data' );

/**
 * Query Shopify Products Helper (Migrated from mu-plugin)
 * 
 * @param array $args Arguments for the query.
 * @return array|WP_Error List of products or error.
 */
function breakdance_shopify_query_products( $args = [] ) {
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
