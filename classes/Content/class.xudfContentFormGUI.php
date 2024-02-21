<?php


use ILIAS\DI\Container;
use srag\Plugins\UdfEditor\Exception\UDFNotFoundException;
use srag\Plugins\UdfEditor\Exception\UnknownUdfTypeException;

class xudfContentFormGUI extends ilPropertyFormGUI
{
    

    public const PLUGIN_CLASS_NAME = ilUdfEditorPlugin::class;

    protected xudfContentGUI $parent_gui;

    protected int $obj_id;
    private Container $dic;


    /**
     * @throws UnknownUdfTypeException
     */
    public function __construct(xudfContentGUI $parent_gui, bool $editable = true)
    {
        parent::__construct();
        $this->parent_gui = $parent_gui;
        $this->obj_id = $parent_gui->getObjId();
        global $DIC;
        $this->dic = $DIC;
        $this->setFormAction($this->dic->ctrl()->getFormAction($parent_gui));
        $this->initForm($editable);
    }

    protected function initForm($editable): void
    {
        /** @var xudfContentElement $element */
        foreach (xudfContentElement::where(['obj_id' => $this->obj_id])->orderBy('sort')->get() as $element) {
            if ($element->isSeparator()) {
                $input = new ilFormSectionHeaderGUI();
                $input->setTitle($element->getTitle());
                $input->setInfo($element->getDescription());
                $this->addItem($input);
            } else {
                try {
                    $definition = $element->getUdfFieldDefinition();
                } catch (UDFNotFoundException $e) {
                    $this->dic->logger()->root()->alert($e->getMessage());
                    $this->dic->logger()->root()->alert($e->getTraceAsString());
                    continue;
                }

                switch ($definition['field_type']) {
                    case 1:
                        $input = new ilTextInputGUI($element->getTitle(), $element->getUdfFieldId());
                        break;
                    case 2:
                        $input = new ilSelectInputGUI($element->getTitle(), $element->getUdfFieldId());
                        $options = ['' => $this->dic->language()->txt('please_choose')];
                        foreach ($definition['field_values'] as $key => $values) {
                            $options[$values] = $values;
                        }
                        $input->setOptions($options);
                        break;
                    case 3:
                        $input = new ilTextAreaInputGUI($element->getTitle(), $element->getUdfFieldId());
                        break;
                    case 51:
                        $input = ilCustomUserFieldsHelper::getInstance()->getFormPropertyForDefinition($definition, true);
                        break;
                    default:
                        throw new UnknownUdfTypeException('field_type ' . $definition['field_type'] . ' of udf field with id ' . $element->getUdfFieldId() . ' is unknown to the udfeditor plugin');
                }

                if ($input === null) {
                    continue;
                }

                $input->setInfo($element->getDescription());
                $input->setRequired($element->isRequired());
                $input->setDisabled(!$editable);
                $this->addItem($input);
            }
        }

        if ($editable) {
            $this->addCommandButton(xudfSettingsGUI::CMD_UPDATE, $this->dic->language()->txt('save'));
        }
    }

    public function fillForm(): void
    {
        $udf_data = $this->dic->user()->getUserDefinedData();
        $values = [];

        /** @var xudfContentElement $element */
        foreach (xudfContentElement::where(['obj_id' => $this->obj_id, 'is_separator' => false])->get() as $element) {
            $values[$element->getUdfFieldId()] = $udf_data['f_' . $element->getUdfFieldId()];

            if ($element->getUdfFieldDefinition()['field_type'] === "51") {
                $values["udf_" . $element->getUdfFieldId()] = $udf_data['f_' . $element->getUdfFieldId()];
            }
        }
        $this->setValuesByArray($values);
    }

    public function saveForm(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $log_values = [];
        $udf_data = $this->dic->user()->getUserDefinedData();

        /** @var xudfContentElement $element */
        foreach (xudfContentElement::where(['obj_id' => $this->obj_id, 'is_separator' => false])->get() as $element) {
            $value = $this->getInput($element->getUdfFieldId());

            if ($value === null) {
                $value = $this->getInput("udf_" . $element->getUdfFieldId());
            }

            $udf_data[$element->getUdfFieldId()] = $value;
            $log_values[$element->getTitle()] = $value;
        }
        $this->dic->user()->setUserDefinedData($udf_data);
        $this->dic->user()->update();

        xudfLogEntry::createNew($this->obj_id, $this->dic->user()->getId(), $log_values);

        return true;
    }
}
