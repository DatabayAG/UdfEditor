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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationCtrl;

/**
 * @ilCtrl_isCalledBy xudfFormConfigurationGUI: ilObjUdfEditorGUI
 */
class xudfFormConfigurationGUI extends xudfGUI
{
    public const SUBTAB_SETTINGS = 'settings';
    public const SUBTAB_FORM_CONFIGURATION = 'form_configuration';
    public const CMD_FORM_CONFIGURATION = 'index';
    public const CMD_ADD_UDF_FIELD = 'addUdfField';
    public const CMD_ADD_SEPARATOR = 'addSeparator';
    public const CMD_CREATE = 'create';
    public const CMD_EDIT = 'edit';
    public const CMD_UPDATE = 'update';
    public const CMD_DELETE = 'delete';
    public const CMD_CONFIRM_DELETE = 'confirmDelete';
    public const CMD_REORDER = 'reorder';
    protected WrapperFactory $httpWrapper;
    protected Factory $refinery;

    public function __construct(ilObjUdfEditorGUI $parent_gui)
    {
        global $DIC;
        parent::__construct($parent_gui);
        $this->httpWrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
    }

    protected function performCommand(string $cmd): void
    {
        switch ($cmd) {
            case self::CMD_STANDARD:
                $this->initToolbar();
                break;
            default:
                break;
        }
        parent::performCommand($cmd);
    }

    protected function setSubtabs(): void
    {
        $this->tabs->addSubTab(self::SUBTAB_SETTINGS, $this->lng->txt(self::SUBTAB_SETTINGS), $this->ctrl->getLinkTargetByClass(xudfSettingsGUI::class));
        $this->tabs->addSubTab(self::SUBTAB_FORM_CONFIGURATION, $this->pl->txt(self::SUBTAB_FORM_CONFIGURATION), $this->ctrl->getLinkTargetByClass(xudfFormConfigurationGUI::class, self::CMD_STANDARD));

        $this->ctrl->setParameterByClass(
            self::class,
            NotificationCtrl::GET_PARAM_NOTIFICATION_ID,
            $this->getObject()->getSettings()->getNotification()->getId()
        );

        if ($this->getObject()->getSettings()->hasMailNotification()) {
            $this->tabs->addSubTab(
                xudfSettingsGUI::SUBTAB_MAIL_TEMPLATE,
                $this->pl->txt("notification"),
                $this->ctrl->getLinkTargetByClass([self::class], NotificationCtrl::CMD_EDIT_NOTIFICATION)
            );
        }
        $this->tabs->setSubTabActive(self::SUBTAB_FORM_CONFIGURATION);

    }

    protected function initToolbar(): void
    {
        $add_udf_field = ilLinkButton::getInstance();
        $add_udf_field->setCaption($this->pl->txt('add_udf_field'), false);
        $add_udf_field->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_UDF_FIELD));
        $this->toolbar->addButtonInstance($add_udf_field);

        $add_separator = $add_udf_field = ilLinkButton::getInstance();
        $add_separator->setCaption($this->pl->txt('add_separator'), false);
        $add_separator->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD_SEPARATOR));
        $this->toolbar->addButtonInstance($add_separator);
    }

    protected function index(): void
    {
        $xudfFormConfigurationTableGUI = new xudfFormConfigurationTableGUI($this, self::CMD_STANDARD);
        $this->tpl->setContent($xudfFormConfigurationTableGUI->getHTML());
    }

    protected function addUdfField(): void
    {
        $udf_fields = ilUserDefinedFields::_getInstance()->getDefinitions();
        if (!count($udf_fields)) {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_no_udfs'), true);
            $this->ctrl->redirect($this, self::CMD_STANDARD);
        }
        $xudfFormConfigurationFormGUI = new xudfFormConfigurationFormGUI($this, new xudfContentElement());
        $this->tpl->setContent($xudfFormConfigurationFormGUI->getHTML());
    }

    protected function addSeparator(): void
    {
        $element = new xudfContentElement();
        $element->setIsSeparator(true);
        $xudfFormConfigurationFormGUI = new xudfFormConfigurationFormGUI($this, $element);
        $this->tpl->setContent($xudfFormConfigurationFormGUI->getHTML());
    }

    protected function retrieveElementIdFromPost(): int
    {
        return $this->httpWrapper->post()->retrieve(
            xudfFormConfigurationFormGUI::F_ELEMENT_ID,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(0)
            ])
        );
    }

    protected function create(): void
    {
        $element = new xudfContentElement($this->retrieveElementIdFromPost());
        $isSeparator = $this->httpWrapper->post()->retrieve(
            xudfFormConfigurationFormGUI::F_IS_SEPARATOR,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->bool(),
                $this->refinery->always(false)
            ])
        );
        $element->setIsSeparator($isSeparator);

        $xudfFormConfigurationFormGUI = new xudfFormConfigurationFormGUI($this, $element);
        $xudfFormConfigurationFormGUI->setValuesByPost();
        if (!$xudfFormConfigurationFormGUI->saveForm()) {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_incomplete'));
            $this->tpl->setContent($xudfFormConfigurationFormGUI->getHTML());

            return;
        }
        $this->tpl->setOnScreenMessage("success", $this->pl->txt('form_saved'), true);
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function update(): void
    {
        $element = new xudfContentElement($this->retrieveElementIdFromPost());

        $xudfFormConfigurationFormGUI = new xudfFormConfigurationFormGUI($this, $element);
        $xudfFormConfigurationFormGUI->setValuesByPost();
        if (!$xudfFormConfigurationFormGUI->saveForm()) {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_incomplete'));
            $this->tpl->setContent($xudfFormConfigurationFormGUI->getHTML());

            return;
        }
        $this->tpl->setOnScreenMessage("success", $this->pl->txt('form_saved'), true);
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function edit(): void
    {
        $elementId = $this->httpWrapper->query()->retrieve(
            xudfFormConfigurationFormGUI::F_ELEMENT_ID,
            $this->refinery->kindlyTo()->int()
        );
        $element = xudfContentElement::find($elementId);
        $xudfFormConfigurationFormGUI = new xudfFormConfigurationFormGUI($this, $element);
        $xudfFormConfigurationFormGUI->fillForm();
        $this->tpl->setContent($xudfFormConfigurationFormGUI->getHTML());
    }

    protected function delete(): void
    {
        $elementId = $this->httpWrapper->query()->retrieve(
            xudfFormConfigurationFormGUI::F_ELEMENT_ID,
            $this->refinery->kindlyTo()->int()
        );
        $element = new xudfContentElement($elementId);

        $text = $this->lng->txt('title') . ": {$element->getTitle()}<br>";
        $text .= $this->lng->txt('description') . ": {$element->getDescription()}<br>";
        $text .= $this->lng->txt('type') . ": " . ($element->isSeparator() ? 'Separator' : $this->pl->txt('udf_field'));

        $confirmationGUI = new ilConfirmationGUI();
        $confirmationGUI->addItem('element_id', (string) $elementId, $text);
        $confirmationGUI->setFormAction($this->ctrl->getFormAction($this));
        $confirmationGUI->setHeaderText($this->pl->txt('delete_confirmation_text'));
        $confirmationGUI->setConfirm($this->lng->txt('delete'), self::CMD_CONFIRM_DELETE);
        $confirmationGUI->setCancel($this->lng->txt('cancel'), self::CMD_STANDARD);

        $this->tpl->setContent($confirmationGUI->getHTML());
    }

    protected function confirmDelete(): void
    {
        $element = new xudfContentElement($this->retrieveElementIdFromPost());
        $element->delete();
        $this->tpl->setOnScreenMessage("success", $this->pl->txt('msg_successfully_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function reorder(): void
    {
        $sort = 10;
        foreach ($_POST['ids'] as $id) {
            $element = xudfContentElement::find($id);
            $element->setSort($sort);
            $element->update();
            $sort += 10;
        }
    }
}
