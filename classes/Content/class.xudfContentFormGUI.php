<?php

use srag\Plugins\UdfEditor\Exception\UDFNotFoundException;
use srag\DIC\UdfEditor\DICTrait;
use srag\Plugins\UdfEditor\Exception\UnknownUdfTypeException;

/**
 * Class xudfContentFormGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xudfContentFormGUI extends ilPropertyFormGUI {

	use DICTrait;
	const PLUGIN_CLASS_NAME = ilUdfEditorPlugin::class;

    /**
     * @var xudfContentGUI
     */
    protected $parent_gui;
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * xudfContentFormGUI constructor.
     * @param xudfContentGUI $parent_gui
     * @param boolean $editable
     */
    public function __construct(xudfContentGUI $parent_gui, $editable = true) {
        $this->parent_gui = $parent_gui;
        $this->obj_id = $parent_gui->getObjId();

//        $this->setTitle(self::dic()->language()->txt('settings'));
        $this->setFormAction(self::dic()->ctrl()->getFormAction($parent_gui));
        $this->initForm($editable);
    }

    /**
     *
     */
    protected function initForm($editable) {
        /** @var xudfContentElement $element */
        foreach (xudfContentElement::where(array('obj_id' => $this->obj_id))->orderBy('sort')->get() as $element) {
            if ($element->isSeparator()) {
                $input = new ilFormSectionHeaderGUI();
                $input->setTitle($element->getTitle());
                $input->setInfo($element->getDescription());
                $this->addItem($input);
            } else {
               	try {
					$definition = $element->getUdfFieldDefinition();
				} catch (UDFNotFoundException $e) {
               		self::dic()->logger()->root()->alert($e->getMessage());
					self::dic()->logger()->root()->alert($e->getTraceAsString());
					continue;
				}

                switch ($definition['field_type']) {
                    case 1:
                        $input = new ilTextInputGUI($element->getTitle(), $element->getUdfFieldId());
                        break;
                    case 2:
                        $input = new ilSelectInputGUI($element->getTitle(), $element->getUdfFieldId());
                        $options = array('' => self::dic()->language()->txt('please_choose'));
                        foreach ($definition['field_values'] as $key => $values) {
                            $options[$values] = $values;
                        }
                        $input->setOptions($options);
                        break;
                    case 3:
                        $input = new ilTextAreaInputGUI($element->getTitle(), $element->getUdfFieldId());
                        break;
					default:
						throw new UnknownUdfTypeException('field_type ' . $definition['field_type'] . ' of udf field with id ' . $element->getUdfFieldId() . ' is unknown to the udfeditor plugin');
                }

                $input->setInfo($element->getDescription());
                $input->setRequired($definition['required']);
                $input->setDisabled(!$editable);
                $this->addItem($input);
            }
        }

        if ($editable) {
            $this->addCommandButton(xudfSettingsGUI::CMD_UPDATE, self::dic()->language()->txt('save'));
        }
    }

    /**
     *
     */
    public function fillForm() {
        $udf_data = self::dic()->user()->getUserDefinedData();
        $values = array();
        /** @var xudfContentElement $element */
        foreach (xudfContentElement::where(array('obj_id' => $this->obj_id, 'is_separator' => false))->get() as $element) {
            $values[$element->getUdfFieldId()] = $udf_data['f_' . $element->getUdfFieldId()];
        }
        $this->setValuesByArray($values);
    }


    /**
     * @return bool
     */
    public function saveForm() {
        if (!$this->checkInput()) {
            return false;
        }

        $udf_data = self::dic()->user()->getUserDefinedData();
        /** @var xudfContentElement $element */
        foreach (xudfContentElement::where(array('obj_id' => $this->obj_id, 'is_separator' => false))->get() as $element) {
            $udf_data[$element->getUdfFieldId()] = $this->getInput($element->getUdfFieldId());
        }
        self::dic()->user()->setUserDefinedData($udf_data);
        self::dic()->user()->update();

        return true;
    }
}