<?php

namespace BreakdanceShopify;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\ElementStudio\registerElementForEditing(
    "BreakdanceShopify\\ShopifyProductTitle",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ShopifyProductTitle extends \Breakdance\Elements\Element
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
        return 'design.tag';
    }

    static function name()
    {
        return 'Product Title';
    }

    static function className()
    {
        return 'bde-shopify-product-title';
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
                'tag' => 'h1',
            ],
            'content' => [
                'context' => [
                    'manual_handle' => ''
                ]
            ]
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
            getPresetSection(
                "EssentialElements\\typography_with_hoverable_color",
                "Typography",
                "typography",
                ['type' => 'popout']
            ),
             c(
                "tag",
                "Tag",
                [],
                ['type' => 'dropdown', 'layout' => 'inline', 'items' => [
                    ['value' => 'h1', 'text' => 'H1'],
                    ['value' => 'h2', 'text' => 'H2'],
                    ['value' => 'h3', 'text' => 'H3'],
                    ['value' => 'div', 'text' => 'Div'],
                    ['value' => 'span', 'text' => 'Span'],
                ]],
                true,
                false,
                []
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
        return false;
    }

    static public function actions()
    {
        return false;
    }

    static function nestingRule()
    {
        return ["type" => "final", "restrictedToBeADescendantOf" => ['BreakdanceShopify\Elements\ShopifyProductBuilder']];
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
            'content.content.tag'
        ];
    }
}
