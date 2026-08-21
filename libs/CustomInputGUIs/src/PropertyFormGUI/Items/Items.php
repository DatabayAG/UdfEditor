<?php

namespace srag\Plugins\UdfEditor\Libs\CustomInputGUIs\PropertyFormGUI\Items;

use ilDateTime;
use ilFormPropertyGUI;
use ilFormSectionHeaderGUI;
use ilNumberInputGUI;
use ilPlugin;
use ilRadioOption;
use ilTemplate;
use ilUtil;
use TypeError;

/**
 *
 *
 *
 * @access  namespace
 */
final class Items
{
    /**
     * @var bool
     */
    protected static $init = false;


    private function __construct()
    {
    }

    /**
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI|ilRadioOption $item
     * @return mixed
     * @deprecated
     */
    public static function getValueFromItem($item)
    {
        if (method_exists($item, "getChecked")) {
            return boolval($item->getChecked());
        }

        if (method_exists($item, "getDate")) {
            return $item->getDate();
        }

        if (method_exists($item, "getImage")) {
            return $item->getImage();
        }

        if (method_exists($item, "getValue") && !($item instanceof ilRadioOption)) {
            if ($item->getMulti()) {
                return $item->getMultiValues();
            } else {
                $value = $item->getValue();

                if ($item instanceof ilNumberInputGUI) {
                    $value = floatval($value);
                } else {
                    if (empty($value) && !is_array($value)) {
                        $value = "";
                    }
                }

                return $value;
            }
        }

        return null;
    }


    /**
     * @return mixed
     */
    public static function getter(object $object, string $property)
    {
        if (method_exists($object, $method = "get" . self::strToCamelCase($property))) {
            return $object->{$method}();
        }

        if (method_exists($object, $method = "is" . self::strToCamelCase($property))) {
            return $object->{$method}();
        }

        return null;
    }


    public static function init(?ilPlugin $plugin = null): void
    {
        if (self::$init === false) {
            global $DIC;
            self::$init = true;
            $dir = __DIR__;
            $dir = "./" . substr($dir, strpos($dir, "/Customizing/") + 1);

            $DIC->ui()->mainTemplate()->addCss($dir . "/css/input_gui_input.css");
        }
    }


    /**
     * @param ilFormPropertyGUI[] $inputs
     */
    public static function renderInputs(array $inputs): string
    {
        global $DIC;
        self::init(); // TODO: Pass $plugin

        $input_tpl = new ilTemplate(__DIR__ . "/templates/input_gui_input.html", true, true);

        $input_tpl->setCurrentBlock("input");

        foreach ($inputs as $input) {
            $input_tpl->setVariable("TITLE", htmlspecialchars($input->getTitle()));

            if ($input->getRequired()) {
                $input_tpl->setVariable("REQUIRED", (new ilTemplate(__DIR__ . "/templates/input_gui_input_required.html", true, false))->get());
            }

            $input_html = str_replace('<div class="help-block"></div>', "", $input->render());
            $input_tpl->setVariable("INPUT", $input_html);

            if ($input->getInfo()) {
                $input_info_tpl = new ilTemplate(__DIR__ . "/templates/input_gui_input_info.html", true, true);

                $input_info_tpl->setVariable("INFO", htmlspecialchars($input->getInfo()));

                $input_tpl->setVariable("INFO", $input_info_tpl->get());
            }

            if ($input->getAlert()) {
                $input_alert_tpl = new ilTemplate(__DIR__ . "/templates/input_gui_input_alert.html", true, true);
                $input_alert_tpl->setVariable(
                    "IMG_SRC",
                    ilUtil::getImagePath("standard/icon_alert.svg")
                );
                $input_alert_tpl->setVariable(
                    "IMG_ALT",
                    $DIC->language()->txt("alert")
                );
                $input_alert_tpl->setVariable("TXT", htmlspecialchars($input->getAlert()));
                $input_tpl->setVariable("ALERT", $input_alert_tpl->get());
            }

            $input_tpl->parseCurrentBlock();
        }

        return $input_tpl->get();
    }


    /**
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI|ilRadioOption $item
     * @param mixed $value
     * @deprecated
     */
    public static function setValueToItem($item, $value): void
    {
        if (method_exists($item, "setChecked")) {
            $item->setChecked($value);

            return;
        }

        if (method_exists($item, "setDate")) {
            if (is_string($value)) {
                $value = new ilDateTime($value, IL_CAL_DATE);
            }

            $item->setDate($value);

            return;
        }

        if (method_exists($item, "setImage")) {
            $item->setImage($value);

            return;
        }

        if (method_exists($item, "setValue") && !($item instanceof ilRadioOption)) {
            $item->setValue($value);
        }
    }


    /**
     * @param mixed $value
     * @return mixed
     */
    public static function setter(object $object, string $property, $value)
    {
        $res = null;

        if (method_exists($object, $method = "with" . self::strToCamelCase($property)) || method_exists($object, $method = "set" . self::strToCamelCase($property))) {
            try {
                $res = $object->{$method}($value);
            } catch (TypeError) {
                try {
                    $res = $object->{$method}(intval($value));
                } catch (TypeError) {
                    $res = $object->{$method}(boolval($value));
                }
            }
        }

        return $res;
    }


    public static function strToCamelCase(string $string): string
    {
        return str_replace("_", "", ucwords($string, "_"));
    }
}
