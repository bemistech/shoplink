<?php

namespace BreakdanceShopify;

use function Breakdance\Elements\c;
use function Breakdance\Elements\PresetSections\getPresetSection;

\Breakdance\ElementStudio\registerElementForEditing(
    "BreakdanceShopify\\ShopifyFilters",
    \Breakdance\Util\getdirectoryPathRelativeToPluginFolder(__DIR__)
);

class ShopifyFilters extends \Breakdance\Elements\Element
{
    static function uiIcon()
    {
        return 'SlidersIcon';
    }

    static function tag()
    {
        return 'div';
    }

    static function name()
    {
        return 'Shopify Filters';
    }

    static function className()
    {
        return 'bde-shopify-filters';
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
            'content' => [
                'widgets' => [
                    ['type' => 'sort', 'label' => 'Sort By'],
                    ['type' => 'price', 'label' => 'Price Range']
                ]
            ]
        ];
    }

    static function designControls()
    {
        return [
            getPresetSection(
                "EssentialElements\\typography",
                "Label Typography",
                "label_typography",
                ['type' => 'popout', 'selector' => '%%SELECTOR%% .bde-shopify-filter-label']
            ),
            c(
                "inputs",
                "Inputs & Selects",
                [
                    getPresetSection(
                        "EssentialElements\\typography",
                        "Typography",
                        "typography",
                        ['type' => 'popout', 'selector' => '%%SELECTOR%% input, %%SELECTOR%% select']
                    ),
                    getPresetSection(
                        "EssentialElements\\spacing_padding_all",
                        "Padding",
                        "padding",
                        ['type' => 'popout', 'selector' => '%%SELECTOR%% input, %%SELECTOR%% select']
                    ),
                    getPresetSection(
                        "EssentialElements\\borders",
                        "Borders",
                        "borders",
                        ['type' => 'popout', 'selector' => '%%SELECTOR%% input, %%SELECTOR%% select']
                    ),
                    c(
                        "background",
                        "Background",
                        [],
                        ['type' => 'color', 'layout' => 'inline', 'selector' => '%%SELECTOR%% input, %%SELECTOR%% select', 'property' => 'background-color'],
                        false,
                        false,
                        []
                    ),
                    c(
                        "gap",
                        "Gap",
                        [],
                        ['type' => 'number', 'layout' => 'inline', 'selector' => '%%SELECTOR%%', 'property' => 'gap', 'unitOptions' => ['types' => ['px', 'em', 'rem', '%']]],
                        true,
                        false,
                        []
                    )
                ],
                ['type' => 'section'],
                false,
                false,
                []
            ),
            getPresetSection(
                "EssentialElements\\atom_button",
                "Clear Button",
                "button",
                ['type' => 'popout', 'selector' => '%%SELECTOR%% .bde-shopify-filter-clear']
            ),
            getPresetSection(
                "EssentialElements\\spacing_padding_all",
                "Container Padding",
                "padding",
                ['type' => 'popout']
            )
        ];
    }

    static function contentControls()
    {
        return [c(
            "widgets",
            "Widgets",
            [c(
                "type",
                "Type",
                [],
                ['type' => 'dropdown', 'layout' => 'vertical', 'items' => [
                    ['text' => 'Sort By', 'value' => 'sort'],
                    ['text' => 'Price Range', 'value' => 'price'],
                    ['text' => 'Tags', 'value' => 'tags'],
                    ['text' => 'Collection', 'value' => 'collection'],
                    ['text' => 'Categories', 'value' => 'categories']
                ]],
                false,
                false,
                [],
            ), c(
                "label",
                "Label",
                [],
                ['type' => 'text', 'layout' => 'vertical'],
                false,
                false,
                [],
            )],
            ['type' => 'repeater', 'groupLabel' => 'Filter', 'layout' => 'vertical', 'repeaterOptions' => ['titleTemplate' => '{label} ({type})', 'defaultTitle' => 'Filter', 'buttonName' => 'Add Filter']],
            false,
            false,
            []
        )];
    }

    static function settingsControls()
    {
        return [];
    }
}
