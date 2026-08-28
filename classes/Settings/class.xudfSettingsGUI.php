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

use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationCtrl;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationsCtrl;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

/**
 * @ilCtrl_isCalledBy xudfSettingsGUI: ilObjUdfEditorGUI, ilPropertyFormGUI
 * @ilCtrl_Calls      xudfSettingsGUI: xudfSettingsFormGUI
 * @ilCtrl_isCalledBy ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationsCtrl: xudfSettingsGUI
 */
class xudfSettingsGUI extends xudfGUI
{
    use Notifications4PluginTrait;

    public const string SUBTAB_SETTINGS = "settings";
    public const string SUBTAB_FORM_CONFIGURATION = "form_configuration";
    public const string SUBTAB_MAIL_TEMPLATE = NotificationsCtrl::TAB_NOTIFICATIONS;

    public const string CMD_UPDATE = "update";

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        $this->setSubtabs();
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case strtolower(xudfSettingsFormGUI::class):
                $xudf_settings_form_gui = new xudfSettingsFormGUI($this);
                $this->ctrl->forwardCommand($xudf_settings_form_gui);
                break;
            default:
                if ($this->getObject()->getSettings()->isMailNotification()
                    && $this->getObject()->getNotification()->getId() === (int) filter_input(INPUT_GET, NotificationCtrl::GET_PARAM_NOTIFICATION_ID)
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
        $this->ctrl->setParameterByClass(self::class, NotificationCtrl::GET_PARAM_NOTIFICATION_ID, $this->getObject()->getNotification()->getId());
        if ($this->getObject()->getSettings()->isMailNotification()) {
            $this->ctrl->setParameterByClass(
                self::class,
                NotificationCtrl::GET_PARAM_NOTIFICATION_ID,
                $this->getObject()->getNotification()->getId()
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
        $xudf_settings_form_gui = new xudfSettingsFormGUI($this);
        $xudf_settings_form_gui->fillForm();
        $this->tpl->setContent($xudf_settings_form_gui->getHTML());
    }

    protected function update(): void
    {
        $xudf_settings_form_gui = new xudfSettingsFormGUI($this);
        $xudf_settings_form_gui->setValuesByPost();
        if (!$xudf_settings_form_gui->saveForm()) {
            $this->ui_util->sendFailure($this->pl->txt("msg_incomplete"));
            $this->tpl->setContent($xudf_settings_form_gui->getHTML());
            return;
        }
        $this->ui_util->sendSuccess($this->pl->txt("form_saved"));
        $this->ctrl->redirect($this, self::CMD_STANDARD);
    }

}
