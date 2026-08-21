<?php

namespace ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI;

use ilFormPropertyGUI;
use ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\PropertyFormGUI\Items\Items;

class TabsInputGUITab
{
    /**
     * @var bool
     */
    protected bool $active = false;
    /**
     * @var string
     */
    protected string $info = "";
    /**
     * @var ilFormPropertyGUI[]
     */
    protected array $inputs = [];
    /**
     * @var ilFormPropertyGUI[]|null
     */
    protected ?array $inputs_generated = null;
    /**
     * @var string
     */
    protected string $post_var = "";
    /**
     * @var string
     */
    protected string $title = "";


    public function __construct(string $title = "", string $post_var = "")
    {
        $this->title = $title;
        $this->post_var = $post_var;
    }


    public function __clone()
    {
        if ($this->inputs_generated !== null) {
            $this->inputs_generated = array_map(function (ilFormPropertyGUI $input): ilFormPropertyGUI {
                return clone $input;
            }, $this->inputs_generated);
        }
    }


    public function addInput(ilFormPropertyGUI $input): void
    {
        $this->inputs[] = $input;
        $this->inputs_generated = null;
    }


    public function getInfo(): string
    {
        return $this->info;
    }


    public function setInfo(string $info): void
    {
        $this->info = $info;
    }


    /**
     * @return ilFormPropertyGUI[]
     */
    public function getInputs(string $post_var, array $init_value): array
    {
        if ($this->inputs_generated === null) {
            $this->inputs_generated = [];

            foreach ($this->inputs as $input) {
                $input = clone $input;

                $org_post_var = $input->getPostVar();

                if (isset($init_value[$this->post_var][$org_post_var])) {
                    Items::setValueToItem($input, $init_value[$this->post_var][$org_post_var]);
                }

                $input->setPostVar($post_var . "[" . $this->post_var . "][" . $org_post_var . "]");

                $this->inputs_generated[$org_post_var] = $input;
            }
        }

        return $this->inputs_generated;
    }


    /**
     * @param ilFormPropertyGUI[] $inputs
     */
    public function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
        $this->inputs_generated = null;
    }


    public function getPostVar(): string
    {
        return $this->post_var;
    }


    public function setPostVar(string $post_var): void
    {
        $this->post_var = $post_var;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    public function isActive(): bool
    {
        return $this->active;
    }


    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
