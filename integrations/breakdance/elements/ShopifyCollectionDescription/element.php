<?php

namespace Shoplink\Elements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

class ShopifyCollectionDescription extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'TypographyIcon';
    }

    static function tag()
    {
        return 'div';
    }

    static function tagOptions()
    {
        return [
            'div',
            'p',
            'span',
        ];
    }

    static function tagControlPath()
    {
        return 'design.typography.tag';
    }

    static function name()
    {
        return 'Collection Description';
    }

    static function className()
    {
        return 'bde-shoplink-collection-description';
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
                    'tag' => 'div'
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
}
