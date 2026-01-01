<?php

namespace Shoplink\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Synchronize Settings between Shoplink Admin and Breakdance
 */
class SettingsSync {

    private static $is_syncing = false;

    public static function init() {
        // Sync Shoplink -> Breakdance via Filter (On Load)
        // This injects Shoplink settings into Breakdance whenever Breakdance reads its settings.
        add_filter( 'option_breakdance_global_settings_json_string', [ __CLASS__, 'filter_breakdance_settings' ] );
        
        // Sync Breakdance -> Shoplink via Save Action (On Save)
        // When Breakdance saves settings, we update our Shoplink option.
        add_action( 'updated_option', [ __CLASS__, 'sync_to_shoplink' ], 10, 3 );
    }

    /**
     * Filter Breakdance Settings on Load
     * Injects Shoplink styles into the Breakdance settings JSON.
     */
    public static function filter_breakdance_settings( $value ) {
        // Avoid infinite loops or unecessary processing
        if ( self::$is_syncing ) {
            return $value;
        }
        
        // If Breakdance is not active, do nothing (though this filter wouldn't matter much)
        if ( ! defined( 'BREAKDANCE_VERSION' ) && ! class_exists( '\Breakdance\Breakdance' ) ) {
            return $value;
        }

        $shoplink_styles = get_option( 'shoplink_styles', [] );
        if ( empty( $shoplink_styles ) ) {
            return $value;
        }

        $bd_data = json_decode( $value, true );
        if ( ! is_array( $bd_data ) ) {
            $bd_data = [];
        }

        // Ensure structure
        if ( ! isset( $bd_data['settings'] ) ) {
            $bd_data['settings'] = [];
        }
        
        // Inject Shoplink Styles
        // We overwrite the 'shoplink' section with our master values from WP Admin
        $bd_data['settings']['shoplink'] = $shoplink_styles;
        
        return json_encode( $bd_data );
    }

    /**
     * When Breakdance Global Settings update, update Shoplink
     */
    public static function sync_to_shoplink( $option, $old_value, $value ) {
        if ( $option !== 'breakdance_global_settings_json_string' ) {
            return;
        }
        
        // Prevent recursion if needed (though modifying option vs filtering option are distinct)
        if ( self::$is_syncing ) {
            return;
        }

        self::$is_syncing = true;

        $bd_data = json_decode( $value, true );
        
        if ( isset( $bd_data['settings']['shoplink'] ) && is_array( $bd_data['settings']['shoplink'] ) ) {
            $new_styles = $bd_data['settings']['shoplink'];
            
            // Update Shoplink option
            // We trust Breakdance's save as the latest user intent
            update_option( 'shoplink_styles', $new_styles );
        }

        self::$is_syncing = false;
    }
}
