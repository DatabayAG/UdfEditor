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

use srag\Plugins\UdfEditor\Exception\UDFNotFoundException;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Exception\Notifications4PluginException;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

/**
 * @ilCtrl_isCalledBy xudfContentGUI: ilObjUdfEditorGUI
 */
class xudfContentGUI extends xudfGUI
{
    use Notifications4PluginTrait;

    public const SUBTAB_SHOW = 'show';
    public const SUBTAB_EDIT_PAGE = 'edit_page';

    public const CMD_RETURN_TO_PARENT = 'returnToParent';

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
                    $this->tpl->setOnScreenMessage("failure", $this->pl->txt('access_denied'), true);
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
        $editable = $this->getObject()->getSettings()->getAlwaysEdit();
        $where = xudfContentElement::where(['obj_id' => $this->getObjId()]);
        if (!$_GET['edit'] && $where->count()) {
            $udf_values = $this->dic->user()->getUserDefinedData();

            /** @var xudfContentElement $element */
            foreach ($where->get() as $element) {
                if (!$element->isSeparator() && !isset($udf_values["f_{$element->getUdfFieldId()}"])) {
                    $editable = true;
                    break;
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
        $form = new xudfContentFormGUI($this, $editable || $_GET['edit']);
        $form->fillForm();
        $this->tpl->setContent($page_obj_gui->getHTML() . $form->getHTML());
    }

    protected function update(): void
    {
        $form = new xudfContentFormGUI($this);
        $form->setValuesByPost();
        if (!$form->saveForm()) {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_incomplete'));
            $page_obj_gui = new xudfPageObjectGUI($this);
            $this->tpl->setContent($page_obj_gui->getHTML() . $form->getHTML());
            return;
        }
        $this->checkAndSendNotification();
        $this->tpl->setOnScreenMessage("success", $this->pl->txt('content_form_saved'), true);
        $this->redirectAfterSave();
        $this->dic->ctrl()->redirect($this, self::CMD_STANDARD);
    }

    protected function checkAndSendNotification(): void
    {
        $xudfSettings = $this->getObject()->getSettings();

        if ($xudfSettings->hasMailNotification()) {
            $notification = $xudfSettings->getNotification();

            $sender = self::notifications4plugin()->sender()->factory()->internalMail(ANONYMOUS_USER_ID, $this->dic->user()->getId());

            $sender->setBcc($xudfSettings->getAdditionalNotification());

            $user_defined_data = [];
            $udf_data = $this->dic->user()->getUserDefinedData();
            foreach (xudfContentElement::where(['obj_id' => $this->getObjId(), 'is_separator' => false])->get() as $element) {
                /** @var xudfContentElement $element */
                try {
                    $user_defined_data[$element->getTitle()] = $udf_data['f_' . $element->getUdfFieldId()] ?? "";
                } catch (UDFNotFoundException $e) {
                    $this->dic->logger()->root()->alert($e->getMessage());
                    $this->dic->logger()->root()->alert($e->getTraceAsString());
                    continue;
                }
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
        $this->dic->ctrl()->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $this->tree->getParentId($_GET['ref_id']));
        $this->dic->ctrl()->redirectByClass(ilRepositoryGUI::class);
    }

    protected function redirectAfterSave(): void
    {
        switch ($this->getObject()->getSettings()->getRedirectType()) {
            case xudfSetting::REDIRECT_STAY_IN_FORM:
                $this->ctrl->redirect($this);
                break;
            case xudfSetting::REDIRECT_TO_ILIAS_OBJECT:
                $ref_id = $this->getObject()->getSettings()->getRedirectValue();
                $this->ctrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', $ref_id);
                $this->ctrl->redirectByClass(ilRepositoryGUI::class);
                break;
            case xudfSetting::REDIRECT_TO_URL:
                $url = $this->getObject()->getSettings()->getRedirectValue();
                $this->ctrl->redirectToURL($url);
                break;
        }
    }
}
