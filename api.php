<?php

namespace BreakdanceShopify;

use WP_Error;
use Shopify_Wp_GraphQL;

class ShopifyApi {
    
    public static $current_context_handle = null; // Kept for backward compatibility/internal pointer
    public static $context_stack = [];

    /**
     * Push a handle onto the context stack.
     * @param string $handle
     */
    public static function push_context($handle) {
        if ($handle) {
            array_push(self::$context_stack, $handle);
            self::$current_context_handle = $handle; // Update current pointer
        }
    }

    /**
     * Pop a handle from the context stack.
     */
    public static function pop_context() {
        array_pop(self::$context_stack);
        self::$current_context_handle = end(self::$context_stack) ?: null; // Update current pointer
    }

    /**
     * Get the current context handle from stack or static property.
     */
    public static function get_active_handle() {
        if (!empty(self::$context_stack)) {
            return end(self::$context_stack);
        }
        return self::$current_context_handle;
    }

    /**
     * Set the current product context.
     */
    public static function set_context($handle) {
        self::$current_context_handle = $handle;
    }

    /**
     * Reset the current product context.
     */
    public static function reset_context() {
        self::$current_context_handle = null;
    }

    /**
     * Render content with a specific product context.
     * Mimics WooCommerce's renderProductPart.
     * 
     * @param string|null $handle
     * @param callable $callback
     */
    public static function render_product_context($handle, $callback) {
        // Compatibility wrapper
        if ($handle) {
            self::push_context($handle);
        }
        
        if (is_callable($callback)) {
            call_user_func($callback);
        }

        if ($handle) {
            self::pop_context();
        }
    }

    /**
     * Initialize the API credentials.
     */
    public static function init() {
        if ( ! class_exists( 'Shopify_Wp_GraphQL' ) ) {
            if ( defined( 'SHOPIFY_PLUGIN_DIR' ) && file_exists( SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php' ) ) {
                require_once SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php';
            } else {
                return new WP_Error( 'shopify_plugin_missing', 'Shopify Plugin not found.' );
            }
        }

        $store_url = get_option( 'shopify_store_url' );
        $api_key   = get_option( 'shopify_api_key' );

        if ( empty( $store_url ) || empty( $api_key ) ) {
            return new WP_Error( 'shopify_credentials_missing', 'Shopify credentials missing.' );
        }

        Shopify_Wp_GraphQL::init( $store_url, $api_key );
        return true;
    }

    /**
     * Get product data by handle.
     * 
     * @param string $handle
     * @return array|WP_Error
     */
    public static function get_product($handle) {
        if (empty($handle)) {
             return new WP_Error('invalid_handle', 'Handle is empty');
        }

        $query = '{
            productByHandle(handle: "' . $handle . '") {
                id
                title
                descriptionHtml
                handle
                onlineStoreUrl
                featuredImage { url altText }
                priceRange {
                    minVariantPrice { amount currencyCode }
                    maxVariantPrice { amount currencyCode }
                }
                variants(first: 10) {
                    edges {
                        node {
                            id
                            title
                            price { amount currencyCode }
                            compareAtPrice { amount currencyCode }
                            image { url altText }
                        }
                    }
                }
                images(first: 10) {
                    edges {
                        node {
                            url
                            altText
                        }
                    }
                }
            }
        }';

        $cache_key = 'shopify_product_' . md5($handle);
        $cached = get_transient($cache_key);
        if ($cached) {
            return $cached;
        }

        $init = self::init();
        if (is_wp_error($init)) return $init;

        $result = Shopify_Wp_GraphQL::execute_query($query);

        if (is_wp_error($result)) return $result;
        if (isset($result['errors'])) return new WP_Error('graphql_error', 'Shopify GraphQL Error: ' . json_encode($result['errors']));
        
        $product = $result['data']['productByHandle'] ?? null;

        if (!$product) {
            return new WP_Error('not_found', 'Product not found');
        }

        set_transient($cache_key, $product, HOUR_IN_SECONDS);
        return $product;
    }

    /**
     * Get the current product context.
     * 
     * @param string|null $manual_handle
     * @return array|WP_Error|null
     */
    public static function get_current_product($manual_handle = null) {
        // 0. Manual Override (Directly passed)
        if (!empty($manual_handle)) {
            return self::get_product($manual_handle);
        }

        // 1. Stack Context (Builder override)
        $stack_handle = self::get_active_handle();
        if ($stack_handle) {
            return self::get_product($stack_handle);
        }

        // 2. URL Context (Query Param or Rewrite)
        $handle = get_query_var('shopify_product_handle');
        if (empty($handle) && isset($_GET['product_handle'])) {
            $handle = sanitize_text_field($_GET['product_handle']);
        }

        if ($handle) {
             return self::get_product($handle);
        }
        
        // 3. Fake data for builder if no context
        if (isset($_GET['breakdance_builder'])) {
             return [
                'title' => 'Sample Product',
                'handle' => 'sample-product',
                'description' => 'This is a sample product description for preview purposes.',
                'descriptionHtml' => '<p>This is a sample product description for preview purposes.</p>',
                'priceRange' => [
                    'minVariantPrice' => ['amount' => '19.99', 'currencyCode' => 'USD'],
                    'maxVariantPrice' => ['amount' => '29.99', 'currencyCode' => 'USD'],
                ],
                'featuredImage' => [
                    'url' => 'https://via.placeholder.com/600',
                    'altText' => 'Sample Image'
                ],
                'onlineStoreUrl' => '#',
                'variants' => [
                    'edges' => [
                        [
                            'node' => [
                                'price' => ['amount' => '19.99', 'currencyCode' => 'USD'],
                                'availableForSale' => true
                            ]
                        ]
                    ]
                ]
            ];
        }

        return new WP_Error('no_context', 'No product context found');
    }

    /**
     * Hook into Breakdance render process to manage context stack.
     */
    public static function register_render_hooks() {
        // Before Node Render: Push Context
        add_filter('breakdance_before_render_node', function($node) {
            if (isset($node['data']['type']) && $node['data']['type'] === 'BreakdanceShopify\Elements\ShopifyProductBuilder') {
                $manual_handle = $node['data']['properties']['content']['context']['manual_handle'] ?? null;
                if ($manual_handle) {
                    self::push_context($manual_handle);
                }
            }
            return $node;
        });

        // After Node Render (HTML generation): Pop Context
        add_filter('breakdance_render_element_html', function($html, $node) {
            if (isset($node['data']['type']) && $node['data']['type'] === 'BreakdanceShopify\Elements\ShopifyProductBuilder') {
                 $manual_handle = $node['data']['properties']['content']['context']['manual_handle'] ?? null;
                 if ($manual_handle) {
                     self::pop_context();
                 }
            }
            return $html;
        }, 10, 2);
    }

    /**
     * Get list of products formatted for Breakdance dropdown options.
     * Caches results in a transient for 1 hour.
     * 
     * @return array
     */
    public static function get_products_options() {
        $options = get_transient('breakdance_shopify_product_options');

        if ($options !== false) {
            return $options;
        }

        $init = self::init();
        if (is_wp_error($init)) {
            return [['value' => '', 'text' => 'Error: Check Credentials']];
        }

        $query = '
        query GetProductsList {
            products(first: 50) {
                edges {
                    node {
                        handle
                        title
                    }
                }
            }
        }';

        $result = Shopify_Wp_GraphQL::execute_query($query);
        
        $items = [['value' => '', 'text' => '-- Select Product --']];

        if (!is_wp_error($result) && !isset($result['errors']) && !empty($result['data']['products']['edges'])) {
            foreach ($result['data']['products']['edges'] as $edge) {
                $items[] = [
                    'value' => $edge['node']['handle'],
                    'text'  => $edge['node']['title']
                ];
            }
        } else {
             $items[] = ['value' => '', 'text' => 'Error fetching products'];
        }

        set_transient('breakdance_shopify_product_options', $items, HOUR_IN_SECONDS);

        return $items;
    }

    // Helper functions for Elements and Dynamic Data
    public static function get_product_title($manual_handle = null) {
        $product = self::get_current_product($manual_handle);
        if (is_wp_error($product)) return '';
        return $product['title'] ?? '';
    }

    public static function get_product_description($manual_handle = null) {
        $product = self::get_current_product($manual_handle);
        if (is_wp_error($product)) return '';
        return $product['descriptionHtml'] ?? $product['description'] ?? '';
    }

    public static function get_product_price($manual_handle = null) {
        $product = self::get_current_product($manual_handle);
        if (is_wp_error($product)) return '';
        
        $minPrice = $product['priceRange']['minVariantPrice']['amount'] ?? 0;
        $currency = $product['priceRange']['minVariantPrice']['currencyCode'] ?? 'USD';
        $maxPrice = $product['priceRange']['maxVariantPrice']['amount'] ?? 0;
        
        $currencies = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥'];
        $symbol = $currencies[$currency] ?? $currency . ' ';
        
        if ($minPrice != $maxPrice) {
            return $symbol . $minPrice . ' - ' . $symbol . $maxPrice;
        }
        return $symbol . $minPrice;
    }

    public static function get_product_compare_at_price($manual_handle = null) {
        $product = self::get_current_product($manual_handle);
        if (is_wp_error($product)) return '';
        
        $compareAt = $product['variants']['edges'][0]['node']['compareAtPrice']['amount'] ?? '';
        $currency = $product['variants']['edges'][0]['node']['compareAtPrice']['currencyCode'] ?? 'USD';
        
        $currencies = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥'];
        $symbol = $currencies[$currency] ?? $currency . ' ';
        
        if ($compareAt) {
            return $symbol . $compareAt;
        }
        return '';
    }

    public static function get_product_image_src($manual_handle = null) {
        $product = self::get_current_product($manual_handle);
        if (is_wp_error($product)) return '';
        return $product['featuredImage']['url'] ?? '';
    }

    public static function get_product_image_alt($manual_handle = null) {
        $product = self::get_current_product($manual_handle);
        if (is_wp_error($product)) return '';
        return $product['featuredImage']['altText'] ?? $product['title'] ?? '';
    }
    
    public static function get_product_handle($manual_handle = null) {
        $product = self::get_current_product($manual_handle);
        if (is_wp_error($product)) return '';
        return $product['handle'] ?? '';
    }

    public static function get_product_url($manual_handle = null) {
        $product = self::get_current_product($manual_handle);
        if (is_wp_error($product)) return '';
        return $product['onlineStoreUrl'] ?? '#';
    }

    /**
     * Get products by collection handle.
     * 
     * @param string $collection_handle
     * @param int $limit
     * @return array|WP_Error
     */
    public static function get_collection_products($collection_handle, $limit = 8) {
        $limit = intval($limit);
        
        // If handle is 'all' or empty, fetch latest products
        if (empty($collection_handle) || $collection_handle === 'all') {
            $query = "{
                products(first: $limit) {
                    edges {
                        node {
                            id
                            title
                            handle
                            description
                            descriptionHtml
                            onlineStoreUrl
                            featuredImage { url altText }
                            priceRange {
                                minVariantPrice { amount currencyCode }
                                maxVariantPrice { amount currencyCode }
                            }
                            variants(first: 1) {
                                edges {
                                    node {
                                        id
                                        price { amount currencyCode }
                                        compareAtPrice { amount currencyCode }
                                    }
                                }
                            }
                        }
                    }
                }
            }";
            $path = ['products', 'edges'];
        } else {
             $query = "{
                collectionByHandle(handle: \"$collection_handle\") {
                    products(first: $limit) {
                        edges {
                            node {
                                id
                                title
                                handle
                                description
                                descriptionHtml
                                onlineStoreUrl
                                featuredImage { url altText }
                                priceRange {
                                    minVariantPrice { amount currencyCode }
                                    maxVariantPrice { amount currencyCode }
                                }
                                variants(first: 1) {
                                    edges {
                                        node {
                                            id
                                            price { amount currencyCode }
                                            compareAtPrice { amount currencyCode }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }";
            $path = ['collectionByHandle', 'products', 'edges'];
        }

        $cache_key = 'shopify_collection_' . md5($collection_handle . '_' . $limit);
        $cached = get_transient($cache_key);
        if ($cached) return $cached;

        $init = self::init();
        if (is_wp_error($init)) return $init;

        $result = Shopify_Wp_GraphQL::execute_query($query);
        if (is_wp_error($result)) return $result;
        
        $flattened = [];
        $root = $result['data'] ?? [];
        foreach($path as $key) {
             $root = $root[$key] ?? [];
        }
        
        if (empty($root)) return [];

        foreach ($root as $edge) {
            $flattened[] = $edge['node'];
        }

        set_transient($cache_key, $flattened, HOUR_IN_SECONDS);
        return $flattened;
    }

    /**
     * Get page by handle.
     * 
     * @param string $handle
     * @return array|WP_Error
     */
    public static function get_page($handle) {
        if (empty($handle)) return [];
        
        $query = "{
            pageByHandle(handle: \"$handle\") {
                id
                title
                body
                bodySummary
            }
        }";

        $cache_key = 'shopify_page_' . md5($handle);
        $cached = get_transient($cache_key);
        if ($cached) return $cached;

        $init = self::init();
        if (is_wp_error($init)) return $init;

        $result = Shopify_Wp_GraphQL::execute_query($query);
        if (is_wp_error($result)) return $result;
        
        $page = $result['data']['pageByHandle'] ?? null;
        if ($page) {
            set_transient($cache_key, $page, HOUR_IN_SECONDS);
        }
        
        return $page;
    }
}
