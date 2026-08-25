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

namespace ILIAS\Plugin\UdfEditor\Form;

use ilCheckboxInputGUI;
use ilHiddenInputGUI;
use ILIAS\Plugin\UdfEditor\Model\ContentElement;
use ilLanguage;
use ilPropertyFormGUI;
use ilSelectInputGUI;
use ilTextInputGUI;
use ilUdfEditorPlugin;
use ilUserDefinedFields;
use xudfFormConfigurationGUI;
use xudfGUI;

class ContentElementConfigForm extends ilPropertyFormGUI
{
    public const string F_TITLE = 'title';
    public const string F_DESCRIPTION = 'description';
    public const string F_UDF_FIELD = 'udf_field';
    public const string F_IS_SEPARATOR = 'is_separator';
    public const string F_ELEMENT_ID = 'element_id';
    public const string F_REQUIRED = 'is_required';

    protected ilLanguage $lng;

    protected ilUdfEditorPlugin $pl;

    public function __construct(
        protected xudfFormConfigurationGUI $parent_gui,
        protected readonly ?int $element_id = null,
        protected readonly bool $separator = false
    ) {
        parent::__construct();
        global $DIC;
        $this->lng = $DIC->language();
        $this->pl = ilUdfEditorPlugin::getInstance();
        $this->setTitle($this->lng->txt($element_id ? 'edit' : 'create'));
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));

        $this->initForm();
    }

    protected function initForm(): void
    {
        $input = new ilHiddenInputGUI(self::F_IS_SEPARATOR);
        $input->setValue((string) $this->separator);
        $this->addItem($input);

        if ($this->element_id) {
            $input = new ilHiddenInputGUI(self::F_ELEMENT_ID);
            $input->setValue((string) $this->element_id);
            $this->addItem($input);
        }

        if ($this->separator) {
            $this->initSeparatorForm();
        } else {
            $this->initUdfFieldForm();
        }


        $this->addCommandButton(
            $this->element_id
                ? xudfFormConfigurationGUI::CMD_UPDATE
                : xudfFormConfigurationGUI::CMD_CREATE,
            $this->lng->txt('save')
        );
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

    public function fillForm(ContentElement $content_element): void
    {
        $values = [
            self::F_TITLE => $content_element->getTitle(),
            self::F_DESCRIPTION => $content_element->getDescription(),
            self::F_UDF_FIELD => $content_element->getUdfField(),
            self::F_REQUIRED => $content_element->isRequired()
        ];

        $this->setValuesByArray($values, true);
    }
}
