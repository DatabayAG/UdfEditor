<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use ilConfirmationGUI;
use ILIAS\DI\Container;
use ilObjUser;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

class NotificationCtrl
{
    use Notifications4PluginTrait;

    public const string CMD_ADD_NOTIFICATION = "addNotification";
    public const string CMD_BACK = "back";
    public const string CMD_CREATE_NOTIFICATION = "createNotification";
    public const string CMD_DELETE_NOTIFICATION = "deleteNotification";
    public const string CMD_DELETE_NOTIFICATION_CONFIRM = "deleteNotificationConfirm";
    public const string CMD_DUPLICATE_NOTIFICATION = "duplicateNotification";
    public const string CMD_EDIT_NOTIFICATION = "editNotification";
    public const string CMD_UPDATE_NOTIFICATION = "updateNotification";
    public const string GET_PARAM_NOTIFICATION_ID = "notification_id";
    protected Notification $notification;
    private Container $dic;


    public function __construct(private readonly object $parentGui)
    {
        global $DIC;
        $this->dic = $DIC;
    }


    public function handleCommand($cmd): bool
    {
        switch ($cmd) {
            case self::CMD_ADD_NOTIFICATION:
            case self::CMD_BACK:
            case self::CMD_CREATE_NOTIFICATION:
            case self::CMD_DELETE_NOTIFICATION:
            case self::CMD_DELETE_NOTIFICATION_CONFIRM:
            case self::CMD_DUPLICATE_NOTIFICATION:
            case self::CMD_EDIT_NOTIFICATION:
            case self::CMD_UPDATE_NOTIFICATION:
                self::notifications4plugin()->withPlaceholderTypes([
                    'user' => 'object ' . ilObjUser::class,
                    'absence' => 'string'
                ]);

                $this->notification = self::notifications4plugin()->notifications()->getNotificationById(
                    (int) filter_input(INPUT_GET, self::GET_PARAM_NOTIFICATION_ID)
                );
                if ($this->notification === null) {
                    $this->notification = self::notifications4plugin()->notifications()->factory()->newInstance();
                }

                $this->dic->ctrl()->setParameter($this->parentGui, self::GET_PARAM_NOTIFICATION_ID, $this->notification->getId());

                $this->setTabs();

                $this->{$cmd}();
                return true;
        }
        return false;
    }

    protected function addNotification(): void
    {
        $form = self::notifications4plugin()->notifications()->factory()->newFormBuilderInstance($this->parentGui, $this->notification);

        $this->dic->ui()->mainTemplate()->setContent($form->render());
    }


    protected function back(): void
    {
        $this->dic->ctrl()->redirect($this->parentGui, NotificationsCtrl::CMD_LIST_NOTIFICATIONS);
    }


    protected function createNotification(): void
    {
        $form = self::notifications4plugin()->notifications()->factory()->newFormBuilderInstance($this->parentGui, $this->notification);

        if (!$form->storeForm()) {
            $this->dic->ui()->mainTemplate()->setContent($form->render());

            return;
        }

        $this->dic->ctrl()->setParameter($this->parentGui, self::GET_PARAM_NOTIFICATION_ID, $this->notification->getId());

        $this->dic->ui()->mainTemplate()->setOnScreenMessage("success", sprintf(
            self::notifications4plugin()->getPlugin()->txt("notifications4plugin_added_notification"),
            $this->notification->getTitle()
        ), true);

        $this->dic->ctrl()->redirect($this->parentGui, self::CMD_EDIT_NOTIFICATION);
    }


    protected function deleteNotification(): void
    {
        self::notifications4plugin()->notifications()->deleteNotification($this->notification);

        $this->dic->ui()->mainTemplate()->setOnScreenMessage("success", sprintf(
            self::notifications4plugin()->getPlugin()->txt("notifications4plugin_deleted_notification"),
            $this->notification->getTitle()
        ), true);

        $this->dic->ctrl()->redirect($this->parentGui, self::CMD_BACK);
    }


    protected function deleteNotificationConfirm(): void
    {
        $confirmation = new ilConfirmationGUI();

        $confirmation->setFormAction($this->dic->ctrl()->getFormAction($this));

        $confirmation->setHeaderText(self::notifications4plugin()->getPlugin()
            ->txt("notifications4plugin_delete_notification_confirm", [$this->notification->getTitle()]));

        $confirmation->addItem(self::GET_PARAM_NOTIFICATION_ID, $this->notification->getId(), $this->notification->getTitle());

        $confirmation->setConfirm(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_delete"), self::CMD_DELETE_NOTIFICATION);
        $confirmation->setCancel(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_cancel"), self::CMD_BACK);

        $this->dic->ui()->mainTemplate()->setContent($confirmation->getHTML());
    }


    protected function duplicateNotification(): void
    {
        $cloned_notification = self::notifications4plugin()->notifications()->duplicateNotification($this->notification);

        self::notifications4plugin()->notifications()->storeNotification($cloned_notification);

        $this->dic->ui()->mainTemplate()->setOnScreenMessage(
            "success",
            sprintf(
                self::notifications4plugin()->getPlugin()->txt("notifications4plugin_duplicated_notification"),
                $cloned_notification->getTitle()
            ),
            true
        );

        $this->dic->ctrl()->redirect($this->parentGui, self::CMD_BACK);
    }


    protected function editNotification(): void
    {
        $form = self::notifications4plugin()->notifications()->factory()->newFormBuilderInstance($this->parentGui, $this->notification);

        $this->dic->ui()->mainTemplate()->setContent($form->render());
    }

    protected function setTabs(): void
    {

    }


    protected function updateNotification(): void
    {
        $form = self::notifications4plugin()->notifications()->factory()->newFormBuilderInstance($this->parentGui, $this->notification);

        if (!$form->storeForm()) {
            $this->dic->ui()->mainTemplate()->setContent($form->render());

            return;
        }

        $this->dic->ui()->mainTemplate()->setOnScreenMessage("success", sprintf(
            self::notifications4plugin()->getPlugin()->txt("notifications4plugin_saved_notification"),
            $this->notification->getTitle()
        ), true);

        $this->dic->ctrl()->redirect($this->parentGui, self::CMD_EDIT_NOTIFICATION);
    }
}
