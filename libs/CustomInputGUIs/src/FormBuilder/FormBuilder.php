<?php

namespace ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\FormBuilder;

use ILIAS\UI\Component\Input\Container\Form\Form;

interface FormBuilder
{
    public function getForm(): Form;


    public function render(): string;


    public function storeForm(): bool;
}
