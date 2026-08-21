<?php

namespace srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification;

use ActiveRecord;
use ilDateTime;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI\MultilangualTabsInputGUI;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Parser\twigParser;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

class Notification extends ActiveRecord implements NotificationInterface
{
    use Notifications4PluginTrait;

    public const TABLE_NAME_SUFFIX = "not";
    /**
     * @var ilDateTime
     * @con_has_field    true
     * @con_fieldtype    timestamp
     * @con_is_notnull   true
     */
    protected $created_at;
    /**
     * @var string
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_length       4000
     * @con_is_notnull   true
     */
    protected $description = "";
    /**
     * @var int
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     * @con_is_primary   true
     */
    protected $id = 0;
    /**
     * @var string
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_length       1024
     * @con_is_notnull   true
     * @con_is_unique    true
     */
    protected $name = "";
    /**
     * @var string
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_is_notnull   true
     */
    protected $parser = twigParser::class;
    /**
     * @var array
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_is_notnull   true
     */
    protected $parser_options = self::DEFAULT_PARSER_OPTIONS;
    /**
     * @var array
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_is_notnull   true
     */
    protected $subject = [];
    /**
     * @var array
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_is_notnull   true
     */
    protected $text = [];
    /**
     * @var string
     * @con_has_field    true
     * @con_fieldtype    text
     * @con_length       1024
     * @con_is_notnull   true
     */
    protected $title = "";
    /**
     * @var ilDateTime
     * @con_has_field    true
     * @con_fieldtype    timestamp
     * @con_is_notnull   true
     */
    protected $updated_at;

    private Container $dic;

    public function __construct(mixed $primary_key = 0)
    {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($primary_key);
    }


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
     * @return Component[]
     */
    public function getActions(): array
    {
        $this->dic->ctrl()->setParameterByClass(NotificationCtrl::class, NotificationCtrl::GET_PARAM_NOTIFICATION_ID, $this->id);

        return [
            $this->dic->ui()->factory()->link()->standard(
                self::notifications4plugin()->getPlugin()->txt("notifications4plugin_edit"),
                $this->dic->ctrl()->getLinkTargetByClass(NotificationCtrl::class, NotificationCtrl::CMD_EDIT_NOTIFICATION, "", false, false)
            ),
            $this->dic->ui()->factory()->link()->standard(
                self::notifications4plugin()->getPlugin()->txt("notifications4plugin_duplicate"),
                $this->dic->ctrl()->getLinkTargetByClass(NotificationCtrl::class, NotificationCtrl::CMD_DUPLICATE_NOTIFICATION, "", false, false)
            ),
            $this->dic->ui()->factory()->link()->standard(
                self::notifications4plugin()->getPlugin()->txt("notifications4plugin_delete"),
                $this->dic->ctrl()->getLinkTargetByClass(NotificationCtrl::class, NotificationCtrl::CMD_DELETE_NOTIFICATION_CONFIRM, "", false, false)
            )
        ];
    }


    public function getConnectorContainerName(): string
    {
        return self::getTableName();
    }


    public function getCreatedAt(): ilDateTime
    {
        return $this->created_at;
    }


    public function setCreatedAt(ilDateTime $created_at): void
    {
        $this->created_at = $created_at;
    }


    public function getDescription(): string
    {
        return $this->description;
    }


    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


    public function getId(): int
    {
        return $this->id;
    }


    public function setId(int $id): void
    {
        $this->id = $id;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function setName(string $name): void
    {
        $this->name = $name;
    }


    public function getParser(): string
    {
        return $this->parser;
    }


    public function setParser(string $parser): void
    {
        $this->parser = $parser;
    }


    public function getParserOption(string $key)
    {
        return $this->parser_options[$key];
    }


    public function getParserOptions(): array
    {
        return $this->parser_options;
    }


    public function setParserOptions(array $parser_options = self::DEFAULT_PARSER_OPTIONS): void
    {
        if (empty($parser_options)) {
            $parser_options = self::DEFAULT_PARSER_OPTIONS;
        }

        $this->parser_options = $parser_options;
    }


    public function getSubject(?string $lang_key = null, bool $use_default_if_not_set = true): string
    {
        return (string) MultilangualTabsInputGUI::getValueForLang($this->subject, $lang_key, "subject", $use_default_if_not_set);
    }


    public function setSubject(string $subject, string $lang_key): void
    {
        MultilangualTabsInputGUI::setValueForLang($this->subject, $subject, $lang_key, "subject");
    }


    public function getSubjects(): array
    {
        return $this->subject;
    }


    public function getText(?string $lang_key = null, bool $use_default_if_not_set = true): string
    {
        return (string) MultilangualTabsInputGUI::getValueForLang($this->text, $lang_key, "text", $use_default_if_not_set);
    }


    public function setText(string $text, string $lang_key): void
    {
        MultilangualTabsInputGUI::setValueForLang($this->text, $text, $lang_key, "text");
    }


    public function getTexts(): array
    {
        return $this->text;
    }


    public function getTitle(): string
    {
        return $this->title;
    }


    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


    public function getUpdatedAt(): ilDateTime
    {
        return $this->updated_at;
    }


    public function setUpdatedAt(ilDateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }


    public function setParserOption(string $key, $value): void
    {
        $this->parser_options[$key] = $value;
    }


    public function setSubjects(array $subjects): void
    {
        $this->subject = $subjects;
    }


    public function setTexts(array $texts): void
    {
        $this->text = $texts;
    }


    /**
     * @param string $field_name
     */
    public function sleep($field_name): bool|string|null
    {
        $field_value = $this->{$field_name};

        return match ($field_name) {
            "subject", "text", "parser_options" => json_encode($field_value),
            default => parent::sleep($field_name),
        };
    }

    /**
     * @param string $field_name
     * @return mixed|null
     */
    public function wakeUp($field_name, $field_value): mixed
    {
        return match ($field_name) {
            "subject", "text", "parser_options" => json_decode($field_value, true),
            default => parent::wakeUp($field_name, $field_value),
        };
    }
}
