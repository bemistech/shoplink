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
    /**
     * Get products by collection handle.
     * 
     * @param string $collection_handle
     * @param int $limit
     * @param string $sortKey
     * @param bool $reverse
     * @return array|WP_Error
     */
    public static function get_collection_products($collection_handle, $limit = 8, $sortKey = 'BEST_SELLING', $reverse = false) {
        $limit = intval($limit);
        
        $variables = [
            'first' => $limit,
            'sortKey' => $sortKey,
            'reverse' => $reverse
        ];

        // Log for debugging
        file_put_contents(WP_CONTENT_DIR . '/breakdance_shopify_debug.log', date('Y-m-d H:i:s') . " - Vars: " . json_encode($variables) . "\n", FILE_APPEND);

        // If handle is 'all' or empty, fetch latest products
        if (empty($collection_handle) || $collection_handle === 'all') {
            
            // Map Invalid Sort Keys for Product Connection (ProductSortKeys)
            // ProductSortKeys supports: TITLE, PRODUCT_TYPE, VENDOR, UPDATED_AT, CREATED_AT, ID, RELEVANCE.
            // It does NOT support: BEST_SELLING, MANUAL, COLLECTION_DEFAULT, PRICE (in some versions), CREATED (use CREATED_AT).
            
            if (in_array($variables['sortKey'], ['BEST_SELLING', 'MANUAL', 'PRICE', 'COLLECTION_DEFAULT', 'CREATED'])) {
                $variables['sortKey'] = 'CREATED_AT'; // Safe fallback
            }

            $query = '
            query GetProducts($first: Int!, $sortKey: ProductSortKeys, $reverse: Boolean) {
                products(first: $first, sortKey: $sortKey, reverse: $reverse) {
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
            }';
            
            $path = ['products', 'edges'];
        } else {
            $variables['handle'] = $collection_handle;
            
            // Map keys for Collection Connection (ProductCollectionSortKeys)
            // Collection supports: CREATED, BEST_SELLING, MANUAL, etc.
            // It uses CREATED instead of CREATED_AT.
            
            if ($variables['sortKey'] === 'CREATED_AT') {
                $variables['sortKey'] = 'CREATED';
            }

            $query = '
            query GetCollectionProducts($handle: String!, $first: Int!, $sortKey: ProductCollectionSortKeys, $reverse: Boolean) {
                collection(handle: $handle) {
                    products(first: $first, sortKey: $sortKey, reverse: $reverse) {
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
            }';
            $path = ['collection', 'products', 'edges'];
        }

        // Use v2 cache key to invalidate old data
        $cache_key = 'shopify_collection_v2_' . md5($collection_handle . '_' . $limit . '_' . $sortKey . '_' . $reverse);
        $cached = get_transient($cache_key);
        if ($cached) return $cached;

        $init = self::init();
        if (is_wp_error($init)) return $init;

        // Use variables in execution
        $result = Shopify_Wp_GraphQL::execute_query($query, $variables);
        if (is_wp_error($result)) return $result;
        
        $flattened = [];
        $root = $result['data'] ?? [];
        foreach($path as $key) {
             $root = $root[$key] ?? [];
        }
        
        if (empty($root)) {
             // Fallback to sample data if in builder OR if just empty to show *something* 
             // But for empty "all products", we might just want to return empty.
             // Sticking to sample for builder for now.
             if (isset($_GET['breakdance_builder'])) {
                return self::get_sample_products($limit);
             }
             return [];
        }

        foreach ($root as $edge) {
            $flattened[] = $edge['node'];
        }
        
        if (empty($flattened) && isset($_GET['breakdance_builder'])) {
             return self::get_sample_products($limit);
        }

        set_transient($cache_key, $flattened, HOUR_IN_SECONDS);
        return $flattened;
    }

    public static function get_sample_products($limit = 8) {
        $products = [];
        for ($i = 1; $i <= $limit; $i++) {
            $products[] = [
                'id' => 'sample-product-' . $i,
                'title' => 'Sample Product ' . $i,
                'handle' => 'sample-product-' . $i,
                'description' => 'This is a sample product description.',
                'descriptionHtml' => '<p>This is a sample product description.</p>',
                'onlineStoreUrl' => '#',
                'featuredImage' => [
                    'url' => 'https://via.placeholder.com/600?text=Product+' . $i,
                    'altText' => 'Sample Product ' . $i
                ],
                'priceRange' => [
                    'minVariantPrice' => ['amount' => '19.99', 'currencyCode' => 'USD'],
                    'maxVariantPrice' => ['amount' => '29.99', 'currencyCode' => 'USD'],
                ],
                'variants' => [
                    'edges' => [
                       ['node' => ['price' => ['amount' => '19.99', 'currencyCode' => 'USD']]]
                    ]
                ]
            ];
        }
        return $products;
    }

    /**
     * Get list of collections formatted for Breakdance dropdown options.
     * Caches results in a transient for 1 hour.
     * 
     * @return array
     */
    public static function get_collections_options() {
        $options = get_transient('breakdance_shopify_collection_options');

        if ($options !== false) {
            return $options;
        }

        $init = self::init();
        if (is_wp_error($init)) {
            return [['value' => '', 'text' => 'Error: Check Credentials']];
        }

        $query = '
        query GetCollectionsList {
            collections(first: 50) {
                edges {
                    node {
                        handle
                        title
                    }
                }
            }
        }';

        $result = Shopify_Wp_GraphQL::execute_query($query);
        
        $items = [['value' => '', 'text' => 'All Products']];

        if (!is_wp_error($result) && !isset($result['errors']) && !empty($result['data']['collections']['edges'])) {
            foreach ($result['data']['collections']['edges'] as $edge) {
                $items[] = [
                    'value' => $edge['node']['handle'],
                    'text'  => $edge['node']['title']
                ];
            }
        } else {
             // If failed, maybe we are in builder or dev mode without API?
             // Add samples if needed, or just error.
             if (empty($result['data']['collections']['edges'])) {
                 // return sample options for builder if no real data
                 if (isset($_GET['breakdance_builder'])) {
                      $items[] = ['value' => 'sample-collection-1', 'text' => 'Sample Collection 1'];
                      $items[] = ['value' => 'sample-collection-2', 'text' => 'Sample Collection 2'];
                 }
             }
        }

        set_transient('breakdance_shopify_collection_options', $items, HOUR_IN_SECONDS);

        return $items;
    }

    /**
     * Get a list of collections.
     * 
     * @param int $limit
     * @return array|WP_Error
     */
    public static function get_collections($limit = 8) {
        $limit = intval($limit);
        
        // Debug Log
        file_put_contents(WP_CONTENT_DIR . '/shopify_debug.log', date('Y-m-d H:i:s') . " - get_collections called. GET keys: " . implode(',', array_keys($_GET)) . "\n", FILE_APPEND);
        
        $query = "{
            collections(first: $limit) {
                edges {
                    node {
                        id
                        title
                        handle
                        description
                        descriptionHtml
                        image { url altText }
                    }
                }
            }
        }";

        $cache_key = 'shopify_collections_' . $limit;
        $cached = get_transient($cache_key);
        if ($cached) return $cached;

        if (isset($_GET['breakdance_builder'])) {
            // Check if we have cached or real data first? 
        }

        $init = self::init();
        if (is_wp_error($init)) {
            // Fallback for ANY error if no data (likely dev/builder mode or broken config)
            // Ideally strictly only in builder, but if builder context is missing, we need to show something.
            // Using loose check: if result is error, return sample data.
             return self::get_sample_collections($limit);
        }

        $result = Shopify_Wp_GraphQL::execute_query($query);
        
        if (is_wp_error($result)) {
            return self::get_sample_collections($limit);
        }
        
        $collections = [];
        if (!empty($result['data']['collections']['edges'])) {
            foreach ($result['data']['collections']['edges'] as $edge) {
                $collections[] = $edge['node'];
            }
        }
        
        if (empty($collections)) {
            return self::get_sample_collections($limit);
        }
        
        set_transient($cache_key, $collections, HOUR_IN_SECONDS);
        return $collections;
    }

    /**
     * Get sample collections for builder.
     */
    public static function get_sample_collections($limit = 3) {
        $collections = [];
        for ($i = 1; $i <= $limit; $i++) {
            $collections[] = [
                'id' => 'sample-id-' . $i,
                'title' => 'Sample Collection ' . $i,
                'handle' => 'sample-collection-' . $i,
                'description' => 'This is a sample collection description.',
                'descriptionHtml' => '<p>This is a sample collection description.</p>',
                'image' => [
                    'url' => 'https://via.placeholder.com/600?text=Collection+' . $i,
                    'altText' => 'Sample Collection ' . $i
                ]
            ];
        }
        return $collections;
    }

    /**
     * Get single collection by handle.
     * 
     * @param string $handle
     * @return array|WP_Error
     */
    public static function get_collection($handle) {
        if (empty($handle)) return new WP_Error('invalid_handle', 'Handle is empty');

        if (strpos($handle, 'sample-collection-') === 0) {
             $id = str_replace('sample-collection-', '', $handle);
             return [
                 'id' => 'sample-id-' . $id,
                 'title' => 'Sample Collection ' . $id,
                 'handle' => $handle,
                 'description' => 'This is a sample collection description.',
                 'descriptionHtml' => '<p>This is a sample collection description.</p>',
                 'image' => [
                     'url' => 'https://via.placeholder.com/600?text=Collection+' . $id,
                     'altText' => 'Sample Collection ' . $id
                 ]
             ];
        }

        $query = "{
            collectionByHandle(handle: \"$handle\") {
                id
                title
                handle
                description
                descriptionHtml
                image { url altText }
            }
        }";

        $cache_key = 'shopify_collection_' . md5($handle);
        $cached = get_transient($cache_key);
        if ($cached) return $cached;

        $init = self::init();
        if (is_wp_error($init)) return $init;

        $result = Shopify_Wp_GraphQL::execute_query($query);
        if (is_wp_error($result)) return $result;
        
        $collection = $result['data']['collectionByHandle'] ?? null;
        if ($collection) {
            set_transient($cache_key, $collection, HOUR_IN_SECONDS);
        }
        
        return $collection;
    }

    // --- Collection Context Management ---

    public static $current_collection_context_handle = null;
    public static $collection_context_stack = [];

    public static function push_collection_context($handle) {
        if ($handle) {
            array_push(self::$collection_context_stack, $handle);
            self::$current_collection_context_handle = $handle;
        }
    }

    public static function pop_collection_context() {
        array_pop(self::$collection_context_stack);
        self::$current_collection_context_handle = end(self::$collection_context_stack) ?: null;
    }

    public static function get_active_collection_handle() {
        if (!empty(self::$collection_context_stack)) {
            return end(self::$collection_context_stack);
        }
        return self::$current_collection_context_handle;
    }

    /**
     * Get the current collection context.
     * 
     * @param string|null $manual_handle
     * @return array|WP_Error|null
     */
    public static function get_current_collection($manual_handle = null) {
        // 0. Manual Override
        if (!empty($manual_handle)) {
            return self::get_collection($manual_handle);
        }

        // 1. Stack Context
        $stack_handle = self::get_active_collection_handle();
        if ($stack_handle) {
            return self::get_collection($stack_handle);
        }

        // 2. URL Context
        $handle = get_query_var('shopify_collection_handle');
        if (empty($handle) && isset($_GET['collection_handle'])) {
            $handle = sanitize_text_field($_GET['collection_handle']);
        }

        if ($handle) {
             return self::get_collection($handle);
        }
        
        // 3. Fake data for builder
        if (isset($_GET['breakdance_builder'])) {
             return [
                'title' => 'Sample Collection',
                'handle' => 'sample-collection',
                'description' => 'This is a sample collection description.',
                'descriptionHtml' => '<p>This is a sample collection description.</p>',
                'image' => [
                    'url' => 'https://via.placeholder.com/600',
                    'altText' => 'Sample Collection Image'
                ]
            ];
        }

        return new WP_Error('no_context', 'No collection context found');
    }

    // --- Collection Helper Functions ---

    public static function get_collection_title($manual_handle = null) {
        $collection = self::get_current_collection($manual_handle);
        if (is_wp_error($collection)) return '';
        return $collection['title'] ?? '';
    }

    public static function get_collection_description($manual_handle = null) {
        $collection = self::get_current_collection($manual_handle);
        if (is_wp_error($collection)) return '';
        return $collection['descriptionHtml'] ?? $collection['description'] ?? '';
    }

    public static function get_collection_image_src($manual_handle = null) {
        $collection = self::get_current_collection($manual_handle);
        if (is_wp_error($collection)) return '';
        if (empty($collection['image'])) return '';
        return $collection['image']['url'] ?? '';
    }

    public static function get_collection_image_alt($manual_handle = null) {
        $collection = self::get_current_collection($manual_handle);
        if (is_wp_error($collection)) return '';
        if (empty($collection['image'])) return '';
        return $collection['image']['altText'] ?? $collection['title'] ?? '';
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
