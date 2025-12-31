<?php

namespace Shoplink;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Search Integration
 */
class SearchIntegration {

    /**
     * Store mapping of Virtual Post ID -> Virtual Attachment ID
     */
    private static $post_thumbnails = [];

    /**
     * Store mapping of Virtual Attachment ID -> Image URL
     */
    private static $attachment_images = [];

    public static function init() {
        add_filter( 'the_posts', [ __CLASS__, 'inject_shopify_products_into_search' ], 10, 2 );
        add_filter( 'post_type_link', [ __CLASS__, 'filter_post_type_link' ], 10, 2 );
        
        // Metadata filtering for _thumbnail_id
        add_filter( 'get_post_metadata', [ __CLASS__, 'filter_post_metadata' ], 10, 4 );
        
        // Image handling
        add_filter( 'image_downsize', [ __CLASS__, 'filter_image_downsize' ], 10, 3 );
        add_filter( 'wp_get_attachment_url', [ __CLASS__, 'filter_attachment_url' ], 10, 2 );
        
        // Register virtual post type for compatibility (optional, but good practice)
        add_action( 'init', [ __CLASS__, 'register_virtual_post_type' ] );
    }

    /**
     * Inject Shopify products into search results
     */
    public static function inject_shopify_products_into_search( $posts, $query ) {
        if ( is_admin() || ! $query->is_search() ) {
            return $posts;
        }

        // Only fetch on page 1 to avoid complexity (or deep pagination issues)
        if ( $query->get( 'paged' ) > 1 ) {
            return $posts;
        }

        $search_term = $query->get( 's' );
        if ( empty( $search_term ) ) {
            return $posts;
        }

        // Fetch from Shopify
        $products = ShopifyApi::get_products_filtered( [
            'limit' => 6, // Limit injection count
            'q'     => $search_term
        ] );

        if ( is_wp_error( $products ) || empty( $products ) ) {
            return $posts;
        }

        // Convert to WP_Post objects
        foreach ( $products as $product ) {
            $post = self::create_virtual_post( $product );
            $posts[] = $post;
        }

        return $posts;
    }

    /**
     * Create a virtual WP_Post object
     */
    private static function create_virtual_post( $product ) {
        $post = new \stdClass();
        
        // Generate a negative ID based on handle checksum to minimize collisions
        $post->ID = -1 * abs( crc32( $product['handle'] ) );
        
        $post->post_author = 1;
        $post->post_date = current_time( 'mysql' );
        $post->post_date_gmt = current_time( 'mysql', 1 );
        $post->post_content = $product['description'] ?? '';
        $post->post_title = $product['title'];
        $post->post_excerpt = '';
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->post_password = '';
        $post->post_name = $product['handle']; // Critical for URL filter
        $post->to_ping = '';
        $post->pinged = '';
        $post->post_modified = $post->post_date;
        $post->post_modified_gmt = $post->post_date_gmt;
        $post->post_content_filtered = '';
        $post->post_parent = 0;
        $post->guid = home_url( '/products/' . $product['handle'] ); // Actual permalink
        $post->menu_order = 0;
        $post->post_type = 'shopify_product';
        $post->post_mime_type = '';
        $post->comment_count = 0;
        $post->filter = 'raw';
        
        // Handle Image
        if ( ! empty( $product['featuredImage']['url'] ) ) {
            // Generate a virtual attachment ID (just shift the post ID to avoid collision with itself, though post IDs are unique)
            // Using a slightly different negative range or just offsetting. 
            // Let's multiply by 10 or just subtract a large constant.
            // Post ID is already negative. Let's make attachment ID also negative but different.
            // Actually, just storing it mapped to Post ID is enough primarily, but for the Attachment ID itself 
            // we need a unique ID.
            $att_id = $post->ID - 100000; 
            
            self::$post_thumbnails[ $post->ID ] = $att_id;
            self::$attachment_images[ $att_id ] = $product['featuredImage']['url'];
        }
        
        return new \WP_Post( $post );
    }

    /**
     * Filter post metadata to return virtual thumbnail ID
     */
    public static function filter_post_metadata( $value, $object_id, $meta_key, $single ) {
        if ( $meta_key === '_thumbnail_id' && isset( self::$post_thumbnails[ $object_id ] ) ) {
            return self::$post_thumbnails[ $object_id ];
        }
        return $value;
    }

    /**
     * Filter image downsize to return Shopify URL for virtual attachments
     */
    public static function filter_image_downsize( $downsize, $id, $size ) {
        if ( isset( self::$attachment_images[ $id ] ) ) {
            // Return [url, width, height, is_intermediate]
            // We fake width/height as we don't have them easily, or set to 0/0 or a reasonable default
            return [ self::$attachment_images[ $id ], 1024, 1024, false ];
        }
        return $downsize;
    }

    /**
     * Filter attachment URL to return Shopify URL
     */
    public static function filter_attachment_url( $url, $attachment_id ) {
        if ( isset( self::$attachment_images[ $attachment_id ] ) ) {
            return self::$attachment_images[ $attachment_id ];
        }
        return $url;
    }

    /**
     * Ensure correct permalink for virtual posts
     */
    public static function filter_post_type_link( $link, $post ) {
        if ( $post->post_type === 'shopify_product' ) {
            return home_url( '/products/' . $post->post_name );
        }
        return $link;
    }

    /**
     * Register virtual post type
     */
    public static function register_virtual_post_type() {
        register_post_type( 'shopify_product', [
            'public' => false, // Don't expose UI
            'publicly_queryable' => false, // Don't allow main query directly
            'exclude_from_search' => true, // We inject manually
            'label' => 'Shopify Products',
            'supports' => ['title', 'editor', 'thumbnail']
        ] );
    }
}

SearchIntegration::init();
