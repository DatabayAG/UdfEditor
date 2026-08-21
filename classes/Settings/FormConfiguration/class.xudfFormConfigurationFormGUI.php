<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\Plugin\UdfEditor\Exception\UDFNotFoundException;

class xudfFormConfigurationFormGUI extends ilPropertyFormGUI
{
    public const F_TITLE = 'title';
    public const F_DESCRIPTION = 'description';
    public const F_UDF_FIELD = 'udf_field';
    public const F_IS_SEPARATOR = 'is_separator';
    public const F_ELEMENT_ID = 'element_id';
    public const F_REQUIRED = 'is_required';

    protected ilLanguage $lng;

    protected ilUdfEditorPlugin $pl;

    public function __construct(protected xudfFormConfigurationGUI $parent_gui, protected xudfContentElement $element)
    {
        parent::__construct();
        global $DIC;
        $this->lng = $DIC->language();
        $this->pl = ilUdfEditorPlugin::getInstance();
        $this->setTitle($this->element->getId() ? $this->lng->txt('edit') : $this->lng->txt('create'));
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

        $this->initForm();
    }

    protected function initForm(): void
    {
        $input = new ilHiddenInputGUI(self::F_IS_SEPARATOR);
        $input->setValue((string) $this->element->isSeparator());
        $this->addItem($input);

        if ($this->element->getId()) {
            $input = new ilHiddenInputGUI(self::F_ELEMENT_ID);
            $input->setValue((string) $this->element->getId());
            $this->addItem($input);
        }

        if ($this->element->isSeparator()) {
            $this->initSeparatorForm();
        } else {
            $this->initUdfFieldForm();
        }

        $this->addCommandButton(xudfFormConfigurationGUI::CMD_CREATE, $this->lng->txt('save'));
        $this->addCommandButton(xudfGUI::CMD_STANDARD, $this->lng->txt('cancel'));
    }

    protected function initUdfFieldForm(): void
    {
        // UDF FIELD
        $input = new ilSelectInputGUI($this->pl->txt(self::F_UDF_FIELD), self::F_UDF_FIELD);

        /** @var ilUserDefinedFields $udf_fields */
        $udf_fields = ilUserDefinedFields::_getInstance()->getDefinitions();
        $options = [];
        foreach ($udf_fields as $udf_field) {
            $options[$udf_field['field_id']] = $udf_field['field_name'];
        }
        $input->setOptions($options);
        $input->setRequired(true);
        $this->addItem($input);

        // DESCRIPTION
        $input = new ilTextInputGUI($this->lng->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($input);

        // REQUIRED
        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_REQUIRED), self::F_REQUIRED);
        $this->addItem($input);
    }

    protected function initSeparatorForm(): void
    {
        // TITLE
        $input = new ilTextInputGUI($this->lng->txt(self::F_TITLE), self::F_TITLE);
        $this->addItem($input);

        // DESCRIPTION
        $input = new ilTextInputGUI($this->lng->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($input);
    }

    public function fillForm(): void
    {
        try {
            $title = $this->element->getTitle();
        } catch (UDFNotFoundException) {
            $this->global_tpl->setOnScreenMessage("info", $this->pl->txt('msg_choose_new_type'));
            $title = '';
        }
        $values = [
            self::F_TITLE => $title,
            self::F_DESCRIPTION => $this->element->getDescription(),
            self::F_UDF_FIELD => $this->element->getUdfFieldId(),
            self::F_REQUIRED => $this->element->isRequired()
        ];

        $this->setValuesByArray($values, true);
    }

    public function saveForm(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $this->element->setObjId($this->parent_gui->getObjId());
        $this->element->setTitle($this->getInput(self::F_TITLE));
        $this->element->setDescription($this->getInput(self::F_DESCRIPTION));
        $this->element->setUdfFieldId((int) $this->getInput(self::F_UDF_FIELD));
        $this->element->setIsRequired((bool) $this->getInput(self::F_REQUIRED));
        $this->element->store();

        return true;
    }
}
