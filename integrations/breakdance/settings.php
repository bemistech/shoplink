<?php

namespace Shoplink\Integrations\Breakdance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Breakdance Global Settings
 */
function register_global_settings() {
    if ( ! function_exists( '\Breakdance\Settings\registerGlobalSettingsPage' ) ) {
        return;
    }

    \Breakdance\Settings\registerGlobalSettingsPage(
        'Shoplink',
        'shoplink', // Slug
        [
            'title' => 'Shoplink',
            'controls' => [
                [
                    'control' => 'color',
                    'name' => 'primary_color',
                    'label' => 'Primary Color (Buttons)',
                    'defaultValue' => '#000000',
                ],
                [
                    'control' => 'color',
                    'name' => 'secondary_color',
                    'label' => 'Secondary Color (Button Text)',
                    'defaultValue' => '#ffffff',
                ],
                [
                    'control' => 'color',
                    'name' => 'heading_color',
                    'label' => 'Heading Color',
                    'defaultValue' => '#111111',
                ],
                [
                    'control' => 'color',
                    'name' => 'text_color',
                    'label' => 'Text Color',
                    'defaultValue' => '#333333',
                ],
                [
                    'control' => 'color',
                    'name' => 'link_color',
                    'label' => 'Link Color',
                    'defaultValue' => '#000000',
                ],
            ]
        ]
    );

    // Sync Hook: When Breakdance settings are saved, update WP Option
    // Breakdance doesn't always automatically sync to specific WP options unless tailored.
    // However, our StyleController reads from `shoplink_styles` option.
    // We need to bridge Breakdance settings -> `shoplink_styles`.
    
    // For V1, we might rely on Breakdance's CSS generation if we were using it fully,
    // but our StyleController outputs CSS variables based on `shoplink_styles` option.
    // If we want Breakdance Global Settings to control this, we need to bind them.
    
    // Actually, simplifying:
    // If the user sets it in Breakdance, Breakdance saves it in its own global settings DB.
    // We should modify StyleController to look at Breakdance settings as well?
    // OR we hook into Breakdance save to update our option?
    // Let's stick to reading from Breakdance Global Settings in StyleController if available.
}

add_action( 'breakdance_register_global_settings', __NAMESPACE__ . '\\register_global_settings' );
