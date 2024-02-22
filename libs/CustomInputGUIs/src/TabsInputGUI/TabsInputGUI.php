<?php

namespace srag\Plugins\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI;

use ilFormPropertyGUI;
use ilTableFilterItem;
use ilTemplate;
use ilToolbarItem;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\PropertyFormGUI\Items\Items;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\src\Utils\PluginVersionParameter;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\Template\Template;

class TabsInputGUI extends ilFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
    public const SHOW_INPUT_LABEL_ALWAYS = 3;
    public const SHOW_INPUT_LABEL_AUTO = 2;
    public const SHOW_INPUT_LABEL_NONE = 1;
    /**
     * @var bool
     */
    protected static $init = false;
    /**
     * @var int
     */
    protected $show_input_label = self::SHOW_INPUT_LABEL_AUTO;
    /**
     * @var TabsInputGUITab[]
     */
    protected $tabs = [];
    /**
     * @var array
     */
    protected $value = [];


    public function __construct(string $title = "", string $post_var = "")
    {
        parent::__construct($title, $post_var);

        self::init(); // TODO: Pass $plugin
    }


    public static function init(?ilPlugin $plugin = null): void
    {
        if (self::$init === false) {
            global $DIC;
            self::$init = true;

            $version_parameter = PluginVersionParameter::getInstance();
            if ($plugin !== null) {
                $version_parameter = $version_parameter->withPlugin($plugin);
            }

            $dir = __DIR__;
            $dir = "./" . substr($dir, strpos($dir, "/Customizing/") + 1);

            $DIC->ui()->mainTemplate()->addCss($version_parameter->appendToUrl($dir . "/css/tabs_input_gui.css"));
        }
    }


    public function __clone()
    {
        $this->tabs = array_map(function (TabsInputGUITab $tab): TabsInputGUITab {
            return clone $tab;
        }, $this->tabs);
    }


    public function addTab(TabsInputGUITab $tab): void
    {
        $this->tabs[] = $tab;
    }


    public function checkInput(): bool
    {
        $ok = true;

        foreach ($this->tabs as $tab) {
            foreach ($tab->getInputs($this->getPostVar(), $this->getValue()) as $org_post_var => $input) {
                $b_value = $_POST[$input->getPostVar()];

                $value = $_POST[$this->getPostVar()][$tab->getPostVar()][$org_post_var];
                //Unable to use checkInput of internal input object because internal inputs can't use array access for post data
                //$_POST[$input->getPostVar()] = $_POST[$this->getPostVar()][$tab->getPostVar()][$org_post_var];

                /*if ($this->getRequired()) {
                   $input->setRequired(true);
               }*/

                $input->checkInput();

                if ($this->getRequired() && trim($value) === "") {
                    $this->setAlert($this->lng->txt("msg_input_is_required"));
                    $ok = false;
                }

                /*
                if (!$input->checkInput()) {
                    $ok = false;
                }
                */
                //$_POST[$input->getPostVar()] = $b_value;
            }
        }

        if ($ok) {
            return true;
        } else {
            $this->setAlert($this->lng->txt("form_input_not_valid"));

            return false;
        }
    }

    public function getShowInputLabel(): int
    {
        return $this->show_input_label;
    }


    public function setShowInputLabel(int $show_input_label): void
    {
        $this->show_input_label = $show_input_label;
    }


    public function getTableFilterHTML(): string
    {
        return $this->render();
    }


    /**
     * @return TabsInputGUITab[]
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }


    /**
     * @param TabsInputGUITab[] $tabs
     */
    public function setTabs(array $tabs): void
    {
        $this->tabs = $tabs;
    }


    public function getToolbarHTML(): string
    {
        return $this->render();
    }


    public function getValue(): array
    {
        return $this->value;
    }



    public function setValue(array $value): void
    {
        if (is_array($value)) {
            $this->value = $value;
        } else {
            $this->value = [];
        }
    }


    public function insert(ilTemplate $tpl): void
    {
        $html = $this->render();

        $tpl->setCurrentBlock("prop_generic");
        $tpl->setVariable("PROP_GENERIC", $html);
        $tpl->parseCurrentBlock();
    }


    public function render(): string
    {
        $tpl = new Template(__DIR__ . "/templates/tabs_input_gui.html");

        foreach ($this->getTabs() as $tab) {
            $inputs = $tab->getInputs($this->getPostVar(), $this->getValue());

            $tpl->setCurrentBlock("tab");

            $post_var = str_replace(["[", "]"], "__", $this->getPostVar() . "_" . $tab->getPostVar());
            $tab_id = "tabsinputgui_tab_" . $post_var;
            $tab_content_id = "tabsinputgui_tab_content_" . $post_var;

            $tpl->setVariableEscaped("TAB_ID", $tab_id);
            $tpl->setVariableEscaped("TAB_CONTENT_ID", $tab_content_id);

            $tpl->setVariableEscaped("TITLE", $tab->getTitle());

            if ($tab->isActive()) {
                $tpl->setVariableEscaped("ACTIVE", " active");
            }

            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("tab_content");

            if ($this->getShowInputLabel() === self::SHOW_INPUT_LABEL_AUTO) {
                $tpl->setVariableEscaped("SHOW_INPUT_LABEL", (count($inputs) > 1 ? self::SHOW_INPUT_LABEL_ALWAYS : self::SHOW_INPUT_LABEL_NONE));
            } else {
                $tpl->setVariableEscaped("SHOW_INPUT_LABEL", $this->getShowInputLabel());
            }

            if ($tab->isActive()) {
                $tpl->setVariableEscaped("ACTIVE", " active");
            }

            $tpl->setVariableEscaped("TAB_ID", $tab_id);
            $tpl->setVariableEscaped("TAB_CONTENT_ID", $tab_content_id);

            if (!empty($tab->getInfo())) {
                $info_tpl = new Template(__DIR__ . "/../PropertyFormGUI/Items/templates/input_gui_input_info.html");

                $info_tpl->setVariableEscaped("INFO", $tab->getInfo());

                $tpl->setVariable("INFO", self::output()->getHTML($info_tpl));
            }

            $tpl->setVariable("INPUTS", Items::renderInputs($inputs));

            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }



    public function setValueByArray(array $values): void
    {
        $this->setValue($values[$this->getPostVar()]);
    }
}
