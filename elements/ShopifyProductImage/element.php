<?php

namespace BreakdanceShopify;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\ElementStudio\registerElementForEditing(
    "BreakdanceShopify\\ShopifyProductImage",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ShopifyProductImage extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'ImageIcon';
    }

    static function tag()
    {
        return 'div';
    }

    static function tagOptions()
    {
        return [];
    }

    static function tagControlPath()
    {
        return false;
    }

    static function name()
    {
        return 'Product Image';
    }

    static function className()
    {
        return 'bde-shopify-product-image';
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

    static function template()
    {
        return file_get_contents(__DIR__ . '/html.twig');
    }

    static function defaultCss()
    {
        return file_get_contents(__DIR__ . '/default.css');
    }

    static function defaultProperties()
    {
        return [
            'design' => [
                'width' => ['number' => 100, 'unit' => '%'],
                'height' => ['number' => 'auto', 'unit' => 'px'],
            ],
            'content' => []
        ];
    }

    static function defaultChildren()
    {
        return false;
    }

    static function cssTemplate()
    {
        return file_get_contents(__DIR__ . '/css.twig');
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

    static function contentControls()
    {
         return [
            c(
                "content",
                "Content",
                [],
                ['type' => 'section', 'layout' => 'vertical'],
                false,
                false,
                []
            )
        ];
    }

    static function settingsControls()
    {
        return [];
    }

    static function dependencies()
    {
        return false;
    }

    static function settings()
    {
        return false;
    }

    static function addPanelRules()
    {
        return ['alwaysHide' => false];
    }

    static public function actions()
    {
        return false;
    }

    static function nestingRule()
    {
        return ["type" => "final", "restrictedToBeADescendantOf" => ['BreakdanceShopify\ShopifyProductBuilder']];
    }

    static function spacingBars()
    {
        return [];
    }

    static function attributes()
    {
        return false;
    }

    static function experimental()
    {
        return false;
    }

    static function availableIn()
    {
        return ['breakdance'];
    }

    static function order()
    {
        return 0;
    }

    static function dynamicPropertyPaths()
    {
        return [];
    }

    static function additionalClasses()
    {
        return false;
    }

    static function projectManagement()
    {
        return false;
    }

    static function propertyPathsToWhitelistInFlatProps()
    {
        return [];
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return [
            'design.object_fit'
        ];
    }
}
