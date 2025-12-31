<?php

namespace Shoplink;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "Shoplink\\ShopifyProductBuilder",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ShopifyProductBuilder extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'SquareIcon';
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
        return 'Shopify Product';
    }

    static function className()
    {
        return 'bde-shoplink-product-builder';
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
        return ['content' => ['context' => ['manual_handle' => '']]];
    }

    static function defaultChildren()
    {
        return [['slug' => 'EssentialElements\Columns', 'children' => [['slug' => 'EssentialElements\Column', 'children' => [['slug' => 'Shoplink\ShopifyProductGallery']], 'defaultProperties' => ['design' => ['size' => ['width' => ['number' => 50, 'unit' => '%', 'style' => '50%']]]]], ['slug' => 'EssentialElements\Column', 'children' => [['slug' => 'Shoplink\ShopifyProductTitle'], ['slug' => 'Shoplink\ShopifyProductPrice'], ['slug' => 'Shoplink\ShopifyVariantSelector'], ['slug' => 'Shoplink\ShopifyQuantityInput'], ['slug' => 'Shoplink\ShopifyAddToCart'], ['slug' => 'Shoplink\ShopifyBuyNowButton'], ['slug' => 'Shoplink\ShopifyProductDescription']], 'defaultProperties' => ['design' => ['size' => ['width' => ['number' => 50, 'unit' => '%', 'style' => '50%']]]]]]]];
    }

    static function cssTemplate()
    {
        $template = file_get_contents(__DIR__ . '/css.twig');
        return $template;
    }

    static function designControls()
    {
        return [getPresetSection(
      "EssentialElements\\simpleLayout",
      "Layout",
      "layout",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\typography_with_hoverable_color",
      "Typography",
      "typography",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\borders",
      "Borders",
      "borders",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_padding_all",
      "Padding",
      "padding",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\spacing_margin_y",
      "Margin",
      "margin",
       ['type' => 'popout']
     )];
    }

    static function contentControls()
    {
        return [c(
        "context",
        "Context",
        [c(
        "elements",
        "Elements",
        [],
        ['type' => 'add_registered_children', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "manual_handle",
        "Manual Handle",
        [],
        ['type' => 'dropdown', 'layout' => 'vertical', 'items' => [['value' => '', 'text' => '-- Select Product --'], ['value' => 'vintage-attraction-poster', 'text' => 'Vintage Attraction Poster'], ['value' => 'example-t-shirt', 'text' => 'Example T-Shirt'], ['value' => 'example-perfume', 'text' => 'Example Perfume']], 'placeholder' => 'Auto-detect or Select Product'],
        false,
        false,
        [],
        
      ), c(
        "fluffy",
        "Fluffy",
        [],
        ['type' => 'text', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical'],
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
        return ['sharePropsWithSSRChildren' => true, 'proOnly' => false];
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
        return ['type' => 'container-excluding-self'];
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
        return ['content'];
    }
}
