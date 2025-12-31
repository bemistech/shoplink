<?php

namespace Shoplink\Elements;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

class ShopifyCollectionImage extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'ImageIcon';
    }

    static function tag()
    {
        return 'div';
    }

    static function name()
    {
        return 'Collection Image';
    }

    static function className()
    {
        return 'bde-shoplink-collection-image';
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
                'width' => ['number' => 100, 'unit' => '%'],
                'height' => ['number' => 'auto', 'unit' => 'px'],
            ]
        ];
    }

    static function designControls()
    {
        return [
             c(
                "width",
                "Width",
                [],
                ['type' => 'unit', 'layout' => 'inline', 'rangeOptions' => ['min' => 0, 'max' => 100, 'step' => 1]],
                true,
                false,
                []
            ),
             c(
                "height",
                "Height",
                [],
                ['type' => 'unit', 'layout' => 'inline', 'rangeOptions' => ['min' => 0, 'max' => 1000, 'step' => 1]],
                true,
                false,
                []
            ),
             c(
                "object_fit",
                "Object Fit",
                [],
                ['type' => 'dropdown', 'layout' => 'inline', 'items' => [
                    ['value' => 'contain', 'text' => 'Contain'],
                    ['value' => 'cover', 'text' => 'Cover'],
                    ['value' => 'fill', 'text' => 'Fill'],
                ]],
                true,
                false,
                []
            ),
             getPresetSection(
                "EssentialElements\\borders",
                "Borders",
                "borders",
                ['type' => 'popout']
            ),
        ];
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return [
            'design.object_fit'
        ];
    }
}
