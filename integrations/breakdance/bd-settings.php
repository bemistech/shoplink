<?php

namespace Shoplink\Integrations\Breakdance;

use function \Breakdance\Elements\controlSection;
use function \Breakdance\Elements\control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Breakdance Global Settings via Filter
 */
add_filter('breakdance_global_settings_control_sections_append', function ($appendedControlSections) {

    if ( ! function_exists( '\Breakdance\Elements\controlSection' ) ) {
        return $appendedControlSections;
    }

    // Fetch existing WordPress settings to use as defaults
    $saved_settings = get_option( 'shoplink_styles', [] );
    $defaults = [
        'primary_color'   => '#000000',
        'secondary_color' => '#ffffff',
        'heading_color'   => '#111111',
        'text_color'      => '#333333',
        'link_color'      => '#000000',
    ];
    
    // Merge saved with defaults
    $current = wp_parse_args( $saved_settings, $defaults );

    // Debug Log
    error_log('Shoplink Settings Sync from ID ' . get_current_user_id() . ': ' . print_r($current, true));

    $shoplinkSection = controlSection('shoplink', 'Shoplink', [
        control(
            'primary_color',
            'Primary Color (Buttons)',
            [
                'type' => 'color',
                'layout' => 'inline',
                'colorOptions' => ['type' => 'solidOnly'],
                'defaultValue' => $current['primary_color']
            ]
        ),
        control(
            'secondary_color',
            'Secondary Color (Button Text)',
            [
                'type' => 'color',
                'layout' => 'inline',
                'colorOptions' => ['type' => 'solidOnly'],
                'defaultValue' => $current['secondary_color']
            ]
        ),
        control(
            'heading_color',
            'Heading Color',
            [
                'type' => 'color',
                'layout' => 'inline',
                'colorOptions' => ['type' => 'solidOnly'],
                'defaultValue' => $current['heading_color']
            ]
        ),
        control(
            'text_color',
            'Text Color',
            [
                'type' => 'color',
                'layout' => 'inline',
                'colorOptions' => ['type' => 'solidOnly'],
                'defaultValue' => $current['text_color']
            ]
        ),
        control(
            'link_color',
            'Link Color',
            [
                'type' => 'color',
                'layout' => 'inline',
                'colorOptions' => ['type' => 'solidOnly'],
                'defaultValue' => $current['link_color']
            ]
        ),
    ]);

    return array_merge($appendedControlSections, [$shoplinkSection]);
});
