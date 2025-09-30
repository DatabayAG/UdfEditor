<?php

namespace srag\Plugins\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI;

use ilFormPropertyGUI;

class MultilangualTabsInputGUI
{
    private function __construct()
    {

    }


    public static function generate(array $items, bool $default_language = false, bool $default_required = true): array
    {
        global $DIC;
        foreach (self::getLanguages($default_language) as $lang_key => $lang_title) {
            $tab_items = [];

            foreach ($items as $item_key => $item) {
                $tab_item = $item;

                if ($default_required && $lang_key === "default") {
                    $tab_item["required"] = true;
                }

                $tab_items[$item_key] = $tab_item;
            }

            $tab = [
                "class" => TabsInputGUITab::class,
                "subitems" => $tab_items,
                "setTitle" => $lang_title,
                "setActive" => ($lang_key === ($default_language ? "default" : $DIC->language()->getLangKey()))
            ];

            $tabs[$lang_key] = $tab;
        }

        return $tabs;
    }


    /**
     * @param ilFormPropertyGUI[] $inputs
     */
    public static function generateLegacy(TabsInputGUI $tabs, array $inputs, bool $default_language = false, bool $default_required = true): void
    {
        global $DIC;
        foreach (self::getLanguages($default_language) as $lang_key => $lang_title) {
            $tab = new TabsInputGUITab($lang_title, $lang_key);
            $tab->setActive($lang_key === ($default_language ? "default" : $DIC->language()->getLangKey()));

            foreach ($inputs as $input) {
                $tab_input = clone $input;

                if ($default_required && $lang_key === "default") {
                    $tab_input->setRequired(true);
                }

                $tab->addInput($tab_input);
            }

            $tabs->addTab($tab);
        }
    }


    public static function getLanguages(bool $default = false): array
    {
        global $DIC;
        $lang_keys = $DIC->language()->getInstalledLanguages();

        if ($default) {
            array_unshift($lang_keys, "default");
        }

        return array_combine($lang_keys, array_map("strtoupper", $lang_keys));
    }


    /**
     * @return mixed
     */
    public static function getValueForLang(array $values, /*?*/ string $lang_key = null, string $sub_key = null, bool $use_default_if_not_set = true)
    {
        global $DIC;
        if (empty($lang_key)) {
            $lang_key = $DIC->language()->getLangKey();
        }

        $value = $values[$lang_key] ?? "";

        if (!empty($sub_key)) {
            if (!is_array($value)) {
                $value = [];
            }

            $value = $value[$sub_key] ?? "";
        }

        if (!empty($value)) {
            return $value;
        }

        if ($use_default_if_not_set) {
            $value = $values["default"];

            if (!empty($sub_key)) {
                if (!is_array($value)) {
                    $value = [];
                }

                $value = $value[$sub_key];
            }

            return $value;
        } else {
            return $value;
        }
    }


    /**
     * @param mixed $value
     */
    public static function setValueForLang(array &$values, $value, string $lang_key, string $sub_key = null): void
    {
        if (!empty($sub_key)) {
            if (!isset($values[$lang_key])) {
                $values[$lang_key] = [];
            }
            $values[$lang_key][$sub_key] = $value;
        } else {
            $values[$lang_key] = $value;
        }
    }
}
