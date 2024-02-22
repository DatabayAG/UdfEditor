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

use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationCtrl;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationsCtrl;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

/**
 * @ilCtrl_isCalledBy xudfSettingsGUI: ilObjUdfEditorGUI, ilPropertyFormGUI
 * @ilCtrl_Calls      xudfSettingsGUI: xudfSettingsFormGUI
 * @ilCtrl_isCalledBy srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationsCtrl: xudfSettingsGUI
 */
class xudfSettingsGUI extends xudfGUI
{
    use Notifications4PluginTrait;

    public const SUBTAB_SETTINGS = 'settings';
    public const SUBTAB_FORM_CONFIGURATION = 'form_configuration';
    public const SUBTAB_MAIL_TEMPLATE = NotificationsCtrl::TAB_NOTIFICATIONS;

    public const CMD_UPDATE = 'update';

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $this->setSubtabs();
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case strtolower(xudfSettingsFormGUI::class):
                $xudfSettingsFormGUI = new xudfSettingsFormGUI($this);
                $this->ctrl->forwardCommand($xudfSettingsFormGUI);
                break;
            default:
                if ($this->getObject()->getSettings()->hasMailNotification()
                    && $this->getObject()->getSettings()->getNotification()->getId() === (int) filter_input(INPUT_GET, NotificationCtrl::GET_PARAM_NOTIFICATION_ID)
                ) {
                    $this->tabs->activateSubTab(self::SUBTAB_MAIL_TEMPLATE);
                }
                $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
                $this->performCommand($cmd);
                break;
        }
    }

    protected function setSubtabs(): void
    {
        $this->tabs->addSubTab(self::SUBTAB_SETTINGS, $this->lng->txt(self::SUBTAB_SETTINGS), $this->ctrl->getLinkTarget($this, self::CMD_STANDARD));
        $this->tabs->addSubTab(self::SUBTAB_FORM_CONFIGURATION, $this->pl->txt(self::SUBTAB_FORM_CONFIGURATION), $this->ctrl->getLinkTargetByClass(xudfFormConfigurationGUI::class));
        $this->ctrl->setParameterByClass(self::class, NotificationCtrl::GET_PARAM_NOTIFICATION_ID, $this->getObject()->getSettings()->getNotification()->getId());
        if ($this->getObject()->getSettings()->hasMailNotification()) {
            $this->ctrl->setParameterByClass(
                self::class,
                NotificationCtrl::GET_PARAM_NOTIFICATION_ID,
                $this->getObject()->getSettings()->getNotification()->getId()
            );
            $this->tabs->addSubTab(
                self::SUBTAB_MAIL_TEMPLATE,
                $this->pl->txt("notification"),
                $this->ctrl->getLinkTargetByClass([self::class], NotificationCtrl::CMD_EDIT_NOTIFICATION)
            );
        }
        $this->tabs->setSubTabActive(self::SUBTAB_SETTINGS);
    }

    protected function index(): void
    {
        $xudfSettingsFormGUI = new xudfSettingsFormGUI($this);
        $xudfSettingsFormGUI->fillForm();
        $this->tpl->setContent($xudfSettingsFormGUI->getHTML());
    }

    protected function update(): void
    {
        $xudfSettingsFormGUI = new xudfSettingsFormGUI($this);
        $xudfSettingsFormGUI->setValuesByPost();
        if (!$xudfSettingsFormGUI->saveForm()) {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_incomplete'));
            $this->tpl->setContent($xudfSettingsFormGUI->getHTML());
            return;
        }
        $this->tpl->setOnScreenMessage("success", $this->pl->txt('form_saved'), true);
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

}
