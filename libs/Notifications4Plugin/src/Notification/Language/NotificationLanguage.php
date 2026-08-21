<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\Language;

use ActiveRecord;
use arConnector;
use ilDateTime;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

/**
 *
 *
 *
 * @deprecated
 */
class NotificationLanguage extends ActiveRecord
{
    use Notifications4PluginTrait;

    /**
     * @var string
     * @deprecated
     */
    public const TABLE_NAME_SUFFIX = "not_lan";
    /**
     * @var ilDateTime
     * @con_has_field    true
     * @con_fieldtype    timestamp
     * @con_is_notnull   true
     * @deprecated
     */
    protected $created_at;
    /**
     * @var int
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     * @con_is_primary   true
     * @deprecated
     */
    protected $id = 0;
    /**
     * @var string
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_length       2
     * @con_is_notnull   true
     * @deprecated
     */
    protected $language = "";
    /**
     * @var int
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     * @deprecated
     */
    protected $notification_id;
    /**
     * @var string
     * @con_has_field    true
     * @con_fieldtype    clob
     * @con_length       256
     * @con_is_notnull   true
     * @deprecated
     */
    protected $subject = "";
    /**
     * @var string
     * @con_has_field    true
     * @con_fieldtype    clob
     * @con_length       4000
     * @con_is_notnull   true
     * @deprecated
     */
    protected $text = "";
    /**
     * @var ilDateTime
     * @con_has_field    true
     * @con_fieldtype    timestamp
     * @con_is_notnull   true
     * @deprecated
     */
    protected $updated_at;


    /**
     * @deprecated
     */
    public function __construct(int $primary_key_value = 0, /*?*/ arConnector $connector = null)
    {
        parent::__construct($primary_key_value, $connector);
    }


    /**
     * @deprecated
     */
    public static function getTableName(): string
    {
        return self::notifications4plugin()->getTableNamePrefix() . "_" . self::TABLE_NAME_SUFFIX;
    }


    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::getTableName();
    }


    /**
     * @deprecated
     */
    public function getConnectorContainerName(): string
    {
        return self::getTableName();
    }


    /**
     * @deprecated
     */
    public function getSubject(): string
    {
        return $this->subject;
    }


    /**
     * @deprecated
     */
    public function getText(): string
    {
        return $this->text;
    }
}
