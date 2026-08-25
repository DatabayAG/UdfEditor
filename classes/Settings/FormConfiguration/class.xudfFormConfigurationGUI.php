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
use ILIAS\Plugin\UdfEditor\Form\ContentElementConfigForm;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationCtrl;
use ILIAS\Plugin\UdfEditor\Model\ContentElement;
use ILIAS\Plugin\UdfEditor\Table\ContentElementTable;
use ILIAS\Refinery\Factory;

/**
 * @ilCtrl_isCalledBy xudfFormConfigurationGUI: ilObjUdfEditorGUI
 */
class xudfFormConfigurationGUI extends xudfGUI
{
    public const string SUBTAB_SETTINGS = 'settings';
    public const string SUBTAB_FORM_CONFIGURATION = 'form_configuration';
    public const string CMD_FORM_CONFIGURATION = 'index';
    public const string CMD_ADD_UDF_FIELD = 'addUdfField';
    public const string CMD_ADD_SEPARATOR = 'addSeparator';
    public const string CMD_CREATE = 'create';
    public const string CMD_EDIT = 'edit';
    public const string CMD_UPDATE = 'update';
    public const string CMD_DELETE = 'delete';
    public const string CMD_CONFIRM_DELETE = 'confirmDelete';
    public const string CMD_REORDER = 'reorder';
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
            $this->getObject()->getNotification()->getId()
        );

        if ($this->getObject()->getSettings()->isMailNotification()) {
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
        $table = new ContentElementTable($this, self::CMD_STANDARD);
        $this->tpl->setContent($table->getHTML());
    }

    protected function addUdfField(): void
    {
        $udf_fields = ilUserDefinedFields::_getInstance()->getDefinitions();
        if (!count($udf_fields)) {
            $this->ui_util->sendFailure($this->pl->txt('msg_no_udfs'));
            $this->ctrl->redirect($this, self::CMD_STANDARD);
        }
        $form = new ContentElementConfigForm($this);
        $this->tpl->setContent($form->getHTML());
    }

    protected function addSeparator(): void
    {
        $form = new ContentElementConfigForm($this, null, true);
        $this->tpl->setContent($form->getHTML());
    }

    protected function retrieveElementIdFromPost(): int
    {
        return $this->httpWrapper->post()->retrieve(
            ContentElementConfigForm::F_ELEMENT_ID,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(0)
            ])
        );
    }

    protected function create(): void
    {
        $isSeparator = $this->httpWrapper->post()->retrieve(
            ContentElementConfigForm::F_IS_SEPARATOR,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->bool(),
                $this->refinery->always(false)
            ])
        );

        $form = new ContentElementConfigForm($this, null, $isSeparator);

        if (!$form->checkInput()) {
            $this->ui_util->sendFailure($this->pl->txt('msg_incomplete'));
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $form->setValuesByPost();


        $udf_field_id = (int) $form->getInput(ContentElementConfigForm::F_UDF_FIELD);

        $content_element = new ContentElement(
            $this->getObjId(),
            $form->getInput(ContentElementConfigForm::F_TITLE),
            $form->getInput(ContentElementConfigForm::F_DESCRIPTION),
            0,
            $udf_field_id ?: null,
            $isSeparator,
            (bool) $form->getInput(ContentElementConfigForm::F_REQUIRED),
        );

        $this->content_element_repo->create($content_element);


        $this->ui_util->sendSuccess($this->pl->txt('form_saved'));
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function update(): void
    {
        $element = $this->content_element_repo->read($this->retrieveElementIdFromPost());

        $form = new ContentElementConfigForm($this, $element->getId(), $element->isSeparator());

        if (!$form->checkInput()) {
            $this->ui_util->sendFailure($this->pl->txt('msg_incomplete'));
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $form->setValuesByPost();

        $udf_field_id = (int) $form->getInput(ContentElementConfigForm::F_UDF_FIELD);

        $element
            ->setTitle($form->getInput(ContentElementConfigForm::F_TITLE))
            ->setDescription($form->getInput(ContentElementConfigForm::F_DESCRIPTION))
            ->setUdfField($element->getUdfField())
            ->setUdfField($udf_field_id ?: null)
            ->setRequired((bool) $form->getInput(ContentElementConfigForm::F_REQUIRED));

        $this->content_element_repo->update($element);

        $this->ui_util->sendSuccess($this->pl->txt('form_saved'));
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function edit(): void
    {
        $elementId = $this->httpWrapper->query()->retrieve(
            ContentElementConfigForm::F_ELEMENT_ID,
            $this->refinery->kindlyTo()->int()
        );
        $element = $this->content_element_repo->read($elementId);

        $form = new ContentElementConfigForm($this, $element->getId(), $element->isSeparator());
        $form->fillForm($element);
        $this->tpl->setContent($form->getHTML());
    }

    protected function delete(): void
    {
        $elementId = $this->httpWrapper->query()->retrieve(
            ContentElementConfigForm::F_ELEMENT_ID,
            $this->refinery->kindlyTo()->int()
        );
        $element = $this->content_element_repo->read($elementId);

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
        $this->content_element_repo->deleteById($this->retrieveElementIdFromPost());
        $this->ui_util->sendSuccess($this->pl->txt('msg_successfully_deleted'));
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

    protected function reorder(): void
    {
        $sort = 10;
        $ids = $this->httpWrapper->post()->retrieve(
            "ids",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        foreach ($ids as $id) {
            $element = $this->content_element_repo->read($id);
            if (!$element) {
                continue;
            }
            $element->setSort($sort);
            $this->content_element_repo->update($element);
            $sort += 10;
        }
    }
}
