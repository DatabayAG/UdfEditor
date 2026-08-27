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

use ILIAS\Plugin\UdfEditor\Form\ContentElementViewForm;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Exception\Notifications4PluginException;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;
use ILIAS\Plugin\UdfEditor\Model\Settings;

/**
 * @ilCtrl_isCalledBy xudfContentGUI: ilObjUdfEditorGUI
 */
class xudfContentGUI extends xudfGUI
{
    use Notifications4PluginTrait;

    public const string SUBTAB_SHOW = 'show';
    public const string SUBTAB_EDIT_PAGE = 'edit_page';

    public const string CMD_RETURN_TO_PARENT = 'returnToParent';

    protected function setSubtabs(): void
    {
        if (ilObjUdfEditorAccess::hasWriteAccess()) {
            $this->dic->tabs()->addSubTab(self::SUBTAB_SHOW, $this->lng->txt(self::SUBTAB_SHOW), $this->dic->ctrl()->getLinkTarget($this));
            $this->dic->tabs()->addSubTab(self::SUBTAB_EDIT_PAGE, $this->lng->txt(self::SUBTAB_EDIT_PAGE), $this->dic->ctrl()->getLinkTargetByClass(xudfPageObjectGUI::class, 'edit'));
            $this->dic->tabs()->setSubTabActive(self::SUBTAB_SHOW);
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $this->setSubtabs();
        $next_class = $this->dic->ctrl()->getNextClass();
        switch ($next_class) {
            case 'xudfpageobjectgui':
                if (!ilObjUdfEditorAccess::hasWriteAccess()) {
                    $this->ui_util->sendFailure($this->pl->txt('access_denied'));
                    $this->dic->ctrl()->returnToParent($this);
                }
                $this->dic->tabs()->activateSubTab(self::SUBTAB_EDIT_PAGE);
                $xudfPageObjectGUI = new xudfPageObjectGUI($this);
                $html = $this->dic->ctrl()->forwardCommand($xudfPageObjectGUI);
                $this->tpl->setContent($html);
                break;
            default:
                $cmd = $this->dic->ctrl()->getCmd(self::CMD_STANDARD);
                $this->performCommand($cmd);
                break;
        }
        // these are automatically rendered by the pageobject gui
        $this->dic->tabs()->removeTab('edit');
        $this->dic->tabs()->removeTab('history');
        $this->dic->tabs()->removeTab('clipboard');
        $this->dic->tabs()->removeTab('pg');
    }

    protected function index(): void
    {
        $editable = $this->getObject()->getSettings()->isAlwaysEdit();
        $content_elements = $this->content_element_repo->readAllByObjId($this->getObjId(), true);

        $edit = $this->httpWrapper->query()->retrieve(
            "edit",
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->bool(),
                $this->refinery->always(false)
            ])
        );

        if (!$edit && count($content_elements)) {
            foreach ($content_elements as $element) {
                if (!$element->isSeparator()) {
                    $field = $element->getUserDefinedField();

                    if (!$field) {
                        continue;
                    }

                    if (!$field->retrieveValueFromUser($this->user)) {
                        $editable = true;
                        break;
                    }
                }
            }
            if (!$editable) {
                // return button
                $button = ilLinkButton::getInstance();
                $button->setPrimary(true);
                $button->setCaption('back');
                $button->setUrl($this->dic->ctrl()->getLinkTarget($this, self::CMD_RETURN_TO_PARENT));
                $this->toolbar->addButtonInstance($button);
                // edit button
                $button = ilLinkButton::getInstance();
                $button->setCaption('edit');
                $this->dic->ctrl()->setParameter($this, 'edit', 1);
                $button->setUrl($this->dic->ctrl()->getLinkTarget($this, self::CMD_STANDARD));
                $this->toolbar->addButtonInstance($button);
            }
        }
        $page_obj_gui = new xudfPageObjectGUI($this);
        $form = new ContentElementViewForm($this, $editable || $edit);
        $form->fillForm();
        $this->tpl->setContent($page_obj_gui->getHTML() . $form->getHTML());
    }

    protected function update(): void
    {
        $form = new ContentElementViewForm($this);
        $form->setValuesByPost();
        if (!$form->saveForm()) {
            $this->ui_util->sendFailure($this->pl->txt('msg_incomplete'));
            $page_obj_gui = new xudfPageObjectGUI($this);
            $this->tpl->setContent($page_obj_gui->getHTML() . $form->getHTML());
            return;
        }
        $this->checkAndSendNotification();
        $this->ui_util->sendSuccess($this->pl->txt('content_form_saved'));
        $this->redirectAfterSave();
        $this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
    }

    protected function checkAndSendNotification(): void
    {
        $xudfSettings = $this->getObject()->getSettings();

        if ($xudfSettings->isMailNotification()) {
            $notification = $this->getObject()->getNotification();

            $sender = self::notifications4plugin()->sender()->factory()->internalMail(ANONYMOUS_USER_ID, $this->dic->user()->getId());

            $sender->setBcc($xudfSettings->getAdditionalNotification());

            $user_defined_data = [];
            foreach ($this->content_element_repo->readAllByObjId($this->getObjId()) as $element) {
                $field = $element->getUserDefinedField();
                $user_defined_data[$element->getTitle()] = $field?->retrieveValueFromUser($this->dic->user()) ?? "";
            }

            $placeholders = [
                "object" => $this->getObject(),
                "user" => $this->dic->user(),
                "user_defined_data" => $user_defined_data
            ];

            try {
                self::notifications4plugin()->sender()->send($sender, $notification, $placeholders, $placeholders["user"]->getLanguage());
            } catch (Notifications4PluginException $e) {
                $this->dic->logger()->root()->alert($e->getMessage());
                $this->dic->logger()->root()->alert($e->getTraceAsString());
            }
        }
    }

    protected function returnToParent(): void
    {
        $refId = $this->httpWrapper->query()->retrieve(
            "ref_id",
            $this->refinery->kindlyTo()->int()
        );
        $this->dic->ctrl()->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->tree->getParentId($refId));
        $this->dic->ctrl()->redirectByClass(ilRepositoryGUI::class);
    }

    protected function returnToCaller(): void
    {
        if (ilSession::has('xudfreturn')) {
            $backlink = ilSession::get('xudfreturn');
            ilSession::clear('xudfreturn');
            ilUtil::redirect($backlink);
        } else {
            $this->ctrl->redirect($this);
        }
    }

    protected function redirectAfterSave(): void
    {
        switch ($this->getObject()->getSettings()->getRedirectType()) {
            case Settings::REDIRECT_STAY_IN_FORM:
                if (ilSession::has('xudfreturn')) {
                    ilSession::clear('xudfreturn');
                }
                $this->ctrl->redirect($this);
                break;
            case Settings::REDIRECT_TO_ILIAS_OBJECT:
                if (ilSession::has('xudfreturn')) {
                    ilSession::clear('xudfreturn');
                }
                $ref_id = $this->getObject()->getSettings()->getRedirectValue();
                $this->ctrl->redirectToUrl('goto.php?target=' . ilObject::_lookupType((int) $ref_id, true) . '_' . $ref_id);
                break;
            case Settings::REDIRECT_TO_URL:
                if (ilSession::has('xudfreturn')) {
                    ilSession::clear('xudfreturn');
                }
                $url = $this->getObject()->getSettings()->getRedirectValue();
                $this->ctrl->redirectToURL($url);
                break;
            case Settings::REDIRECT_TO_CALLER:
                $this->returnToCaller();
                break;
        }
    }
}
