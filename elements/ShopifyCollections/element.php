<?php

namespace BreakdanceShopify;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\ElementStudio\registerElementForEditing(
    "BreakdanceShopify\\ShopifyCollections",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ShopifyCollections extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'ListIcon';
    }

    static function tag()
    {
        return 'div';
    }

    static function name()
    {
        return 'Collections';
    }

    static function className()
    {
        return 'bde-shopify-collections';
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

    static function properties()
    {
        return [
            'design' => [
                'list' => [
                    'layout' => 'grid',
                    'columns' => 3,
                    'gap' => 20,
                    'align' => 'left'
                ],
                'elements' => [
                    'image' => ['include' => 'enable', 'order' => 10],
                    'title' => ['include' => 'enable', 'order' => 20],
                    'description' => ['include' => 'disable', 'order' => 30]
                ]
            ],
            'content' => [
                'query' => [
                    'limit' => 8
                ]
            ]
        ];
    }

    static function designControls()
    {
        return [c(
        "list",
        "List",
        [c(
        "layout",
        "Layout",
        [],
        ['type' => 'button_bar', 'layout' => 'vertical', 'items' => [['value' => 'grid', 'text' => 'Grid', 'icon' => 'GridIcon'], ['value' => 'slider', 'text' => 'Slider', 'icon' => 'ListIcon']]],
        true,
        false,
        [],
        
      ), c(
        "columns",
        "Columns",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['min' => 1, 'max' => 6, 'step' => 1], 'condition' => ['path' => 'design.list.layout', 'operand' => 'not equals', 'value' => 'slider']],
        true,
        false,
        [],
        
      ), c(
        "gap",
        "Gap",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['min' => 0, 'max' => 100, 'step' => 1], 'condition' => ['path' => 'design.list.layout', 'operand' => 'not equals', 'value' => 'slider']],
        true,
        false,
        [],
        
      ), c(
        "align",
        "Align",
        [],
        ['type' => 'dropdown', 'layout' => 'inline', 'items' => [['value' => 'left', 'text' => 'Left'], ['value' => 'center', 'text' => 'Center'], ['value' => 'right', 'text' => 'Right']], 'condition' => ['path' => 'design.list.layout', 'operand' => 'not equals', 'value' => 'slider']],
        true,
        false,
        [],
        
      ), c(
        "slider",
        "Slider Settings",
        [getPresetSection(
      "EssentialElements\\slider_settings_advanced",
      "Settings",
      "settings",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\slider_pagination_design",
      "Pagination",
      "pagination",
       ['type' => 'popout']
     ), getPresetSection(
      "EssentialElements\\slider_arrows_design",
      "Arrows",
      "arrows",
       ['type' => 'popout']
     )],
        ['type' => 'section', 'layout' => 'vertical', 'condition' => ['path' => 'design.list.layout', 'operand' => 'equals', 'value' => 'slider']],
        false,
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
        "description",
        "Description",
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
        ['type' => 'repeater', 'groupLabel' => 'Area', 'layout' => 'vertical'],
      false,
      false,
      [],
      
    )],
      ['type' => 'section', 'layout' => 'inline', 'sectionOptions' => ['type' => 'popout']],
      false,
      false,
      [],
      
    ),
  ],
  ['type' => 'section', 'layout' => 'vertical'],
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
        "limit",
        "Limit",
        [],
        ['type' => 'number', 'layout' => 'inline', 'rangeOptions' => ['min' => 1, 'max' => 50, 'step' => 1]],
        true,
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
        return [
            [
                'scripts' => [
                    '%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/swiper-bundle.min.js',
                    '%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-swiper/breakdance-swiper.js'
                ],
                'styles' => [
                    '%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/swiper-bundle.min.css',
                    '%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/swiper@8/breakdance-swiper-preset-defaults.css'
                ],
                'builderCondition' => 'return {{ design.list.layout == \'slider\' }};',
                'frontendCondition' => 'return {{ design.list.layout == \'slider\' }};',
                'title' => 'Slider',
            ],
            [
                'title' => 'Slider - Frontend',
                'inlineScripts' => ['window.BreakdanceSwiper().update({
  id: \'%%UNIQUESLUG%%\', selector:\'%%SELECTOR%%\',
  settings:{{ design.list.slider.settings|json_encode }},
  paginationSettings:{{ design.list.slider.pagination|json_encode }},
});'],
                'frontendCondition' => 'return {{ design.list.layout == \'slider\' }};',
                'builderCondition' => 'return false;',
            ]
        ];
    }
    
    static public function actions()
    {
        return [
            'onPropertyChange' => [['script' => "{% if design.list.layout == 'slider' %}
window.BreakdanceSwiper().update({
  id: '%%ID%%', selector:'%%SELECTOR%%',
  settings:{{ design.list.slider.settings|json_encode }},
  paginationSettings:{{ design.list.slider.pagination|json_encode }},
});
{% else %}
window.swiperInstances && window.swiperInstances['%%ID%%'] && window.BreakdanceSwiper().destroy(
  '%%ID%%'
);
{% endif %}",
            'dependencies' => ['design.list.layout', 'design.list.slider'],
            ]],
            'onMovedElement' => [['script' => "{% if design.list.layout == 'slider' %}
window.BreakdanceSwiper().update({
  id: '%%ID%%', selector:'%%SELECTOR%%',
  settings:{{ design.list.slider.settings|json_encode }},
  paginationSettings:{{ design.list.slider.pagination|json_encode }},
});
{% endif %}",
            ]],
            'onBeforeDeletingElement' => [['script' => "{% if design.list.layout == 'slider' %}
window.swiperInstances && window.swiperInstances['%%ID%%'] && window.BreakdanceSwiper().destroy(
  '%%ID%%'
);
{% endif %}",
            ]],
            'onMountedElement' => [['script' => "{% if design.list.layout == 'slider' %}
window.BreakdanceSwiper().update({
  id: '%%ID%%', selector:'%%SELECTOR%%',
  settings:{{ design.list.slider.settings|json_encode }},
  paginationSettings:{{ design.list.slider.pagination|json_encode }},
});
{% endif %}",
            ]],
        ];
    }
}
