<?php


use srag\Notifications4Plugin\UdfEditor\Notification\NotificationInterface;
use srag\Notifications4Plugin\UdfEditor\Utils\Notifications4PluginTrait;

class xudfSetting extends ActiveRecord
{
    
    use Notifications4PluginTrait;

    public const PLUGIN_CLASS_NAME = ilUdfEditorPlugin::class;
    public const DB_TABLE_NAME = 'xudf_setting';

    public const REDIRECT_STAY_IN_FORM = 'stay_in_form';
    public const REDIRECT_TO_ILIAS_OBJECT = 'to_ilias_object';
    public const REDIRECT_TO_URL = 'to_url';

    public function getConnectorContainerName(): string
    {
        return self::DB_TABLE_NAME;
    }

    /**
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     * @con_is_primary   true
     */
    protected int $obj_id;

    /**
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       1
     * @con_is_notnull   true
     */
    protected bool $is_online = false;

    /**
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       1
     * @con_is_notnull   true
     */
    protected bool $show_info_tab = false;

    /**
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       1
     * @con_is_notnull   true
     */
    protected bool $mail_notification = false;

    /**
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_length       256
     */
    protected string $additional_notification = '';

    /**
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_length       64
     */
    protected string $redirect_type = self::REDIRECT_STAY_IN_FORM;

    /**
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_length       256
     */
    protected string $redirect_value;

    /**
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_is_notnull   true
     */
    protected string $notification_name = "";

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function isOnline(): bool
    {
        return $this->is_online;
    }

    public function setIsOnline(bool $is_online): void
    {
        $this->is_online = $is_online;
    }

    public function isShowInfoTab(): bool
    {
        return $this->show_info_tab;
    }

    public function setShowInfoTab(bool $show_info_tab): void
    {
        $this->show_info_tab = $show_info_tab;
    }

    public function hasMailNotification(): bool
    {
        return $this->mail_notification;
    }

    public function setMailNotification(bool $mail_notification): void
    {
        $this->mail_notification = $mail_notification;
    }

    public function getAdditionalNotification(): string
    {
        return $this->additional_notification;
    }

    public function setAdditionalNotification(string $additional_notification): void
    {
        $this->additional_notification = $additional_notification;
    }

    public function getRedirectType(): string
    {
        return $this->redirect_type ?: self::REDIRECT_STAY_IN_FORM;
    }

    public function setRedirectType(string $redirect_type): void
    {
        $this->redirect_type = $redirect_type;
    }

    public function getRedirectValue(): string
    {
        return $this->redirect_value;
    }

    public function setRedirectValue(string $redirect_value): void
    {
        $this->redirect_value = $redirect_value;
    }

    public static function find($primary_key, array $add_constructor_args = []): ?self
    {
        return parent::find($primary_key, $add_constructor_args);
    }

    public function getNotification(): NotificationInterface
    {
        if (empty($this->notification_name)) {
            $this->notification_name = "object_" . $this->getObjId();

            $this->store();
        }

        $notification = self::notifications4plugin()->notifications()->getNotificationByName($this->notification_name);

        if ($notification === null) {
            $notification = self::notifications4plugin()->notifications()->factory()->newInstance();

            $notification->setTitle(self::plugin()->translate("notification"));

            $notification->setName($this->notification_name);

            $notification->setSubject("ILIAS: {{ object.getTitle }}", "default");

            $notification->setText("Sehr geehrte/r {{ user.getFullname }},

Sie haben im Objekt „{{ object.getTitle }}“ die folgenden Angaben ausgewählt:

{% for key, value in user_defined_data %}
{{ key }} : {{ value }}

{% endfor %}
{{ \"now\"|date('d.m.Y H:i') }}", "default");

            self::notifications4plugin()->notifications()->storeNotification($notification);
        }

        return $notification;
    }
}
