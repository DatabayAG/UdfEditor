<?php

namespace ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI;

use ilFormPropertyGUI;
use ilPlugin;
use ilTableFilterItem;
use ilTemplate;
use ilToolbarItem;
use ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\PropertyFormGUI\Items\Items;

class TabsInputGUI extends ilFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
    public const int SHOW_INPUT_LABEL_ALWAYS = 3;
    public const int SHOW_INPUT_LABEL_AUTO = 2;
    public const int SHOW_INPUT_LABEL_NONE = 1;
    protected static bool $init = false;
    protected int $show_input_label = self::SHOW_INPUT_LABEL_AUTO;
    /**
     * @var TabsInputGUITab[]
     */
    protected array $tabs = [];
    protected array $value = [];


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

            $dir = __DIR__;
            $dir = "./" . substr($dir, strpos($dir, "/Customizing/") + 1);

            $DIC->ui()->mainTemplate()->addCss($dir . "/css/tabs_input_gui.css");
        }
    }


    public function __clone()
    {
        $this->tabs = array_map(static function (TabsInputGUITab $tab): TabsInputGUITab {
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
        $tpl = new ilTemplate(__DIR__ . "/templates/tabs_input_gui.html", true, true);

        foreach ($this->getTabs() as $tab) {
            $inputs = $tab->getInputs($this->getPostVar(), $this->getValue());

            $tpl->setCurrentBlock("tab_item");

            $post_var = str_replace(["[", "]"], "__", $this->getPostVar() . "_" . $tab->getPostVar());
            $tab_id = "tabsinputgui_tab_" . $post_var;
            $tab_content_id = "tabsinputgui_tab_content_" . $post_var;

            $tpl->setVariable("TAB_ID", htmlspecialchars($tab_id));
            $tpl->setVariable("TAB_CONTENT_ID", htmlspecialchars($tab_content_id));

            $tpl->setVariable("TITLE", htmlspecialchars($tab->getTitle()));

            if ($tab->isActive()) {
                $tpl->setVariable("ACTIVE", htmlspecialchars(" active"));
            }

            if ($this->getShowInputLabel() === self::SHOW_INPUT_LABEL_AUTO) {
                $tpl->setVariable("SHOW_INPUT_LABEL", htmlspecialchars((count($inputs) > 1 ? self::SHOW_INPUT_LABEL_ALWAYS : self::SHOW_INPUT_LABEL_NONE)));
            } else {
                $tpl->setVariable("SHOW_INPUT_LABEL", htmlspecialchars($this->getShowInputLabel()));
            }

            if ($tab->isActive()) {
                $tpl->setVariable("ACTIVE", htmlspecialchars(" active"));
            }

            $tpl->setVariable("TAB_ID", htmlspecialchars($tab_id));
            $tpl->setVariable("TAB_CONTENT_ID", htmlspecialchars($tab_content_id));

            if (!empty($tab->getInfo())) {
                $info_tpl = new ilTemplate(__DIR__ . "/../PropertyFormGUI/Items/templates/input_gui_input_info.html", true, true);

                $info_tpl->setVariable("INFO", htmlspecialchars($tab->getInfo()));

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
