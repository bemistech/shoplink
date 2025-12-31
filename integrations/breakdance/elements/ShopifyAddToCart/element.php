<?php

namespace Shoplink;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "Shoplink\\ShopifyAddToCart",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ShopifyAddToCart extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'CartShoppingIcon';
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
        return 'Shopify Add To Cart';
    }

    static function className()
    {
        return 'shopify-add-to-cart';
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
        return ['design' => [], 'content' => ['text' => 'Add to Cart']];
    }

    static function defaultChildren()
    {
        return false;
    }

    static function cssTemplate()
    {
        $template = file_get_contents(__DIR__ . '/css.twig');
        return $template;
    }

    static function designControls()
    {
        return [getPresetSection(
      "EssentialElements\\AtomV1ButtonDesign",
      "Button Style",
      "button",
       ['type' => 'popout']
     )];
    }

    static function contentControls()
    {
        return [c(
        "text",
        "Button Text",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      )];
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
        return ['type' => 'final', 'restrictedToBeADescendantOf' => ['Shoplink\ShopifyProductBuilder']];
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
        return false;
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
        return false;
    }

    static function propertyPathsToSsrElementWhenValueChanges()
    {
        return ['content.text'];
    }
}
