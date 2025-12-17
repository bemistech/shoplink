<?php

namespace BreakdanceShopify;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\ElementStudio\registerElementForEditing(
    "BreakdanceShopify\\ShopifyProductPrice",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ShopifyProductPrice extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'DollarIcon';
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
        return 'Product Price';
    }

    static function className()
    {
        return 'bde-shopify-product-price';
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
                'layout' => 'inline',
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
                "layout",
                "Layout",
                [],
                ['type' => 'dropdown', 'layout' => 'inline', 'items' => [
                    ['value' => 'inline', 'text' => 'Inline (Price - Sale)'],
                    ['value' => 'stacked', 'text' => 'Stacked'],
                ]],
                true,
                false,
                []
            ),
            getPresetSection(
                "EssentialElements\\typography_with_hoverable_color",
                "Price Typography",
                "price_typography",
                ['type' => 'popout']
            ),
             getPresetSection(
                "EssentialElements\\typography_with_hoverable_color",
                "Sale Price Typography",
                "sale_price_typography",
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
            'design.layout'
        ];
    }
}
