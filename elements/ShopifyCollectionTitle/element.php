<?php

namespace BreakdanceShopify\Elements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

class ShopifyCollectionTitle extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'TypographyIcon';
    }

    static function tag()
    {
        return 'h1';
    }

    static function tagOptions()
    {
        return [
            'h1',
            'h2',
            'h3',
            'div',
            'span',
        ];
    }

    static function tagControlPath()
    {
        return 'design.typography.tag';
    }

    static function name()
    {
        return 'Collection Title';
    }

    static function className()
    {
        return 'bde-shopify-collection-title';
    }

    static function category()
    {
        return 'Shopify';
    }

    static function badge()
    {
        return ['backgroundColor' => 'var(--green500)', 'textColor' => 'var(--white)', 'label' => 'Shopify'];
    }

    static function slug()
    {
        return __CLASS__;
    }

    static function defaultProperties()
    {
        return [
            'design' => [
                'typography' => [
                    'tag' => 'h1'
                ]
            ]
        ];
    }

    static function designControls()
    {
        return [
            getPresetSection(
                "EssentialElements\\typography_with_hoverable_color",
                "Typography",
                "typography",
                ['type' => 'popout']
            )
        ];
    }

    static function properties()
    {
        return [
            'design' => [
                'typography' => [
                    'tag' => 'h1'
                ]
            ]
        ];
    }
}
