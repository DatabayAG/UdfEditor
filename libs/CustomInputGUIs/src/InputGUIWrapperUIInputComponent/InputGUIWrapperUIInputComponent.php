<?php

namespace ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent;

use Closure;
use ilCheckboxInputGUI;
use ilDateTimeInputGUI;
use ilFormPropertyGUI;
use ILIAS\Data\Factory;
use ILIAS\DI\Container;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\Input\Field\FormInput;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ilRepositorySelector2InputGUI;
use ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\PropertyFormGUI\Items\Items;

class InputGUIWrapperUIInputComponent extends FormInput
{
    protected ilFormPropertyGUI $input;
    private Container $dic;


    public function __construct(ilFormPropertyGUI $input)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->input = $input;

        parent::__construct(new Factory(), $this->dic->refinery(), "");
    }


    public function getByline(): ?string
    {
        return $this->input->getInfo();
    }


    public function getError(): ?string
    {
        return $this->input->getAlert();
    }


    public function getInput(): ilFormPropertyGUI
    {
        return $this->input;
    }


    public function setInput(ilFormPropertyGUI $input): void
    {
        $this->input = $input;
    }


    public function getLabel(): string
    {
        return $this->input->getTitle();
    }


    public function getUpdateOnLoadCode(): Closure
    {
        return function (string $id): string {
            return "";
        };
    }


    public function getValue()
    {
        return Items::getValueFromItem($this->input);
    }


    public function isDisabled(): bool
    {
        return $this->input->getDisabled();
    }


    public function isRequired(): bool
    {
        return $this->input->getRequired();
    }


    public function withByline(string $info): self
    {
        $this->checkStringArg("byline", $info);

        $clone = clone $this;
        $clone->input = clone $this->input;

        $clone->input->setInfo($info);

        return $clone;
    }


    public function withDisabled(bool $disabled): self
    {
        $this->checkBoolArg("disabled", $disabled);

        $clone = clone $this;
        $clone->input = clone $this->input;

        $clone->input->setDisabled($disabled);

        return $clone;
    }


    public function withError(string $error): self
    {
        $clone = clone $this;
        $clone->input = clone $this->input;

        $clone->input->setAlert($error);

        return $clone;
    }


    public function withLabel(string $label): self
    {
        $this->checkStringArg("label", $label);

        $clone = clone $this;
        $clone->input = clone $this->input;

        $clone->input->setTitle($label);

        return $clone;
    }


    public function withNameFrom(NameSource $source, ?string $parent_name = null): self
    {
        $clone = parent::withNameFrom($source);
        $clone->input = clone $this->input;

        $clone->input->setPostVar($clone->getName());

        if ($clone->input instanceof ilRepositorySelector2InputGUI) {
            $clone->input->getExplorerGUI()->setSelectMode($clone->getName() . "_sel", $this->input->multi_nodes);
        }

        return $clone;
    }

    public function withRequired(bool $required, ?Constraint $requirement_constraint = null): FormInput
    {
        $this->checkBoolArg("is_required", $required);

        $clone = clone $this;
        $clone->input = clone $this->input;

        $clone->input->setRequired($required);

        return $clone;
    }


    public function withValue($value): self
    {
        if ($this->input instanceof ilDateTimeInputGUI && !$this->isRequired()) {
            $this->isClientSideValueOk($value);
        }

        if (!($value === null && $this->input instanceof ilCheckboxInputGUI && $this->isDisabled())) {
            Items::setValueToItem($this->input, $value);
        }

        return $this;
    }


    protected function getConstraintForRequirement(): ?Constraint
    {
        return new InputGUIWrapperConstraint($this->input, $this->data_factory, $this->dic->language());
    }


    public function isClientSideValueOk($value): bool
    {
        return $this->input->checkInput();
    }
}
