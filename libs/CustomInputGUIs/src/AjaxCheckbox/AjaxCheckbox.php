<?php

namespace srag\Plugins\UdfEditor\Libs\CustomInputGUIs\AjaxCheckbox;

use ilPlugin;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\src\Utils\PluginVersionParameter;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\Template\Template;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\Waiter\Waiter;

class AjaxCheckbox
{
    public const GET_PARAM_CHECKED = "checked";

    protected static bool $init = false;

    protected string $ajax_change_link = "";

    protected bool $checked = false;


    public function __construct(?ilPlugin $plugin = null)
    {
        self::init($plugin);
    }


    public static function init(ilPlugin $plugin = null): void
    {
        if (self::$init === false) {
            global $DIC;
            self::$init = true;

            $version_parameter = PluginVersionParameter::getInstance();
            if ($plugin !== null) {
                $version_parameter = $version_parameter->withPlugin($plugin);
            }

            Waiter::init(Waiter::TYPE_WAITER, null, $plugin);

            $dir = __DIR__;
            $dir = "./" . substr($dir, strpos($dir, "/Customizing/") + 1);

            $DIC->ui()->mainTemplate()->addJavaScript($version_parameter->appendToUrl($dir . "/js/ajax_checkbox.min.js", $dir . "/js/ajax_checkbox.js"));
        }
    }


    public function getAjaxChangeLink(): string
    {
        return $this->ajax_change_link;
    }


    public function isChecked(): bool
    {
        return $this->checked;
    }


    public function render(): string
    {
        $tpl = new Template(__DIR__ . "/templates/ajax_checkbox.html");

        if ($this->checked) {
            $tpl->setVariableEscaped("CHECKED", " checked");
        }

        $config = [
            "ajax_change_link" => $this->ajax_change_link
        ];

        $tpl->setVariableEscaped("CONFIG", base64_encode(json_encode($config, JSON_THROW_ON_ERROR)));

        return $tpl->get();
    }


    public function withAjaxChangeLink(string $ajax_change_link): self
    {
        $this->ajax_change_link = $ajax_change_link;

        return $this;
    }


    public function withChecked(bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }
}
