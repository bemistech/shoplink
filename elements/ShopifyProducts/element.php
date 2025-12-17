<?php

namespace BreakdanceShopify;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;


\Breakdance\ElementStudio\registerElementForEditing(
    "BreakdanceShopify\\ShopifyProducts",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ShopifyProducts extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'Grid2Icon';
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
        return 'Products';
    }

    static function className()
    {
        return 'bde-shopify-products';
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
        return ['design' => ['layout' => ['columns' => 3, 'gap' => 20, 'align' => 'left'], 'elements' => ['image' => ['include' => 'enable', 'order' => 10], 'title' => ['include' => 'enable', 'order' => 20], 'price' => ['include' => 'enable', 'order' => 30], 'button' => ['include' => 'enable', 'order' => 40], 'custom_areas' => []]], 'content' => ['query' => ['limit' => 8, 'sort_key' => 'BEST_SELLING']]];
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
        return [c(
        "layout",
        "Layout",
        [c(
        "columns",
        "Columns",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['min' => 1, 'max' => 6, 'step' => 1]],
        true,
        false,
        [],
        
      ), c(
        "gap",
        "Gap",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['min' => 0, 'max' => 100, 'step' => 1]],
        true,
        false,
        [],
        
      ), c(
        "align",
        "Align",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'left', 'text' => 'Left'], ['value' => 'center', 'text' => 'Center'], ['value' => 'right', 'text' => 'Right']]],
        true,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "elements",
        "Elements",
        [c(
        "image",
        "Image",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
        
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      ), c(
        "title",
        "Title",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
        
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
        
      ), getPresetSection(
      "EssentialElements\\typography_with_hoverable_color",
      "Typography",
      "typography",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      ), c(
        "price",
        "Price",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
        
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
        
      ), getPresetSection(
      "EssentialElements\\typography_with_hoverable_color",
      "Typography",
      "typography",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      ), c(
        "button",
        "Button",
        [c(
        "include",
        "Include",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'enable', 'text' => 'Enable'], ['text' => 'Disable', 'value' => 'disable']], 'buttonBarOptions' => ['layout' => 'default', 'size' => 'small']],
        false,
        false,
        [],
        
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
        
      ), getPresetSection(
      "EssentialElements\\typography_with_hoverable_color",
      "Typography",
      "typography",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      ), c(
        "custom_areas",
        "Custom Areas",
        [c(
        "areas",
        "Areas",
        [c(
        "global_block",
        "Global Block",
        [],
        ['type' => 'global_block_chooser', 'layout' => 'vertical'],
        false,
        false,
        [],
        
      ), c(
        "order",
        "Order",
        [],
        ['type' => 'number', 'layout' => 'inline'],
        true,
        false,
        [],
        
      )],
        ['type' => 'repeater', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => 'Area {index}', 'defaultTitle' => 'Area', 'buttonName' => 'Add Area']],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'vertical', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      )],
        ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
        false,
        false,
        [],
        
      )];
    }

    static function contentControls()
    {
        return [c(
        "query",
        "Query",
        [c(
        "collection_handle",
        "Collection Handle",
        [],
        ['type' => 'text', 'layout' => 'vertical', 'placeholder' => 'e.g. frontpage'],
        false,
        false,
        [],
        
      ), c(
        "limit",
        "Limit",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['min' => 1, 'max' => 50, 'step' => 1]],
        true,
        false,
        [],
        
      ), c(
        "sort_key",
        "Sort By",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'BEST_SELLING', 'text' => 'Best Selling'], ['value' => 'TITLE', 'text' => 'Title'], ['value' => 'PRICE', 'text' => 'Price'], ['value' => 'CREATED_AT', 'text' => 'Created At']]],
        true,
        false,
        [],
        
      ), c(
        "reverse",
        "Reverse Order",
        [],
        ['type' => 'toggle', 'layout' => 'inline'],
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
        return ['type' => 'final'];
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
        return ['content.query.limit', 'content.query.collection_handle', 'content.query.sort_key', 'content.query.reverse', 'design.layout.columns', 'design.layout.gap', 'design.layout.align'];
    }
}
