<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification;

use ilDateTime;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI\MultilangualTabsInputGUI;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\Language\NotificationLanguage;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser\twigParser;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;
use stdClass;
use Throwable;

final class Repository implements RepositoryInterface
{
    use Notifications4PluginTrait;


    public function __construct(private readonly ilDBInterface $db)
    {

    }

    public function deleteNotification(NotificationInterface $notification): void
    {
        $this->db->manipulateF('DELETE FROM ' . $this->db->quoteIdentifier(Notification::getTableName())
            . ' WHERE id=%s', [ilDBConstants::T_INTEGER], [$notification->getId()]);
    }


    public function dropTables(): void
    {
        $this->db->dropTable(Notification::getTableName(), false);

        //$this->db->dropAutoIncrementTable(Notification::getTableName());

        $this->dropTablesLanguage();
    }


    public function duplicateNotification(NotificationInterface $notification): NotificationInterface
    {
        $duplicated_notification = clone $notification;

        $duplicated_notification->setId(0);

        $duplicated_notification->setTitle($duplicated_notification->getTitle() . " ("
            . self::notifications4plugin()->getPlugin()->txt("notifications4plugin_duplicated") . ")");

        return $duplicated_notification;
    }


    public function factory(): FactoryInterface
    {
        return Factory::getInstance();
    }


    public function getNotificationById(int $id): ?NotificationInterface
    {
        /**
         * @var NotificationInterface|null $notification
         */
        $stm = $this->db->queryF(
            'SELECT * FROM ' . Notification::getTableName() . ' WHERE id=%s',
            [ilDBConstants::T_INTEGER],
            [$id]
        );

        $stdClass = $stm->fetchObject();

        if (!$stdClass) {
            return null;
        }

        return $this->factory()->fromDB($stdClass);
    }


    public function getNotificationByName(string $name): ?NotificationInterface
    {
        $stm = $this->db->queryF(
            'SELECT * FROM ' . Notification::getTableName() . ' WHERE name=%s',
            [ilDBConstants::T_TEXT],
            [$name]
        );


        $stdClass = $stm->fetchObject();

        if (!$stdClass) {
            return null;
        }

        return $this->factory()->fromDB($stdClass);
    }


    public function getNotifications(): array
    {
        $stm = $this->db->query('SELECT *' . ' FROM ' . $this->db->quoteIdentifier(Notification::getTableName()));

        $notifications = [];
        while ($stdClass = $stm->fetchObject()) {
            $notifications[] = $this->factory()->fromDB($stdClass);
        }

        return $notifications;
    }


    public function getNotificationsCount(): int
    {

        $sql = 'SELECT COUNT(id) AS count' . ' FROM ' . $this->db->quoteIdentifier(Notification::getTableName());

        $result = $this->db->query($sql);

        if (($row = $result->fetchAssoc()) !== false) {
            return (int) $row["count"];
        }

        return 0;
    }


    public function installTables(): void
    {
        try {
            Notification::updateDB();
        } catch (Throwable) {
            // Fix Call to a member function getName() on null (Because not use ILIAS sequence)
        }

        if ($this->db->sequenceExists(Notification::getTableName())) {
            $this->db->dropSequence(Notification::getTableName());
        }

        $this->createAutoIncrement(Notification::getTableName(), "id");

        $this->migrateLanguages();

        if ($this->db->tableColumnExists(Notification::getTableName(), "default_language")) {
            $this->db->dropTableColumn(Notification::getTableName(), "default_language");
        }
    }

    public function createAutoIncrement(string $table_name, string $field): void
    {
        $this->db->manipulate('ALTER TABLE ' . $table_name . ' MODIFY COLUMN ' . $field . ' INT NOT NULL AUTO_INCREMENT');
    }


    /**
     * @deprecated
     */
    public function migrateFromOldGlobalPlugin(string $name = null): ?NotificationInterface
    {
        $global_plugin_notification_table_name = "sr_notification";
        $global_plugin_notification_language_table_name = "sr_notification_lang";
        $global_plugin_twig_parser_class = implode("\\", [
            "srag",
            "Notifications4Plugin",
            "Notifications4Plugins",
            "Parser",
            "twigParser"
        ]); // (Prevents LibraryNamespaceChanger)

        if (!empty($name)) {
            if ($this->db->tableExists($global_plugin_notification_table_name)
                && $this->db->tableExists($global_plugin_notification_language_table_name)
            ) {
                $result = $this->db->queryF('SELECT * FROM ' . $this->db
                        ->quoteIdentifier($global_plugin_notification_table_name) . ' WHERE name=%s', [ilDBConstants::T_TEXT], [$name]);

                if (($row = $result->fetchAssoc()) !== false) {

                    $notification = $this->getNotificationByName($name);
                    if ($notification !== null) {
                        return $notification;
                    }

                    $notification = $this->factory()->newInstance();

                    $notification->setName((string) $row["name"]);
                    $notification->setTitle((string) $row["title"]);
                    $notification->setDescription((string) $row["description"]);

                    if (empty($row["parser"]) || $row["parser"] === $global_plugin_twig_parser_class) {
                        $notification->setParser(twigParser::class);
                    } else {
                        $notification->setParser((string) $row["parser"]);
                    }

                    $result2 = $this->db->queryF('SELECT * FROM ' . $this->db
                            ->quoteIdentifier($global_plugin_notification_language_table_name)
                        . ' WHERE notification_id=%s', [ilDBConstants::T_INTEGER], [$row["id"]]);

                    while (($row2 = $result2->fetchAssoc()) !== false) {
                        $notification->setSubject((string) $row2["subject"], (string) $row2["language"]);
                        $notification->setText((string) $row2["text"], (string) $row2["language"]);
                    }

                    if (!empty($row["default_language"])) {
                        $notification->setSubject($notification->getSubject((string) $row["default_language"], false), "default");
                        $notification->setText($notification->getText((string) $row["default_language"], false), "default");
                    }

                    $this->storeNotification($notification);

                    return $notification;
                }
            }
        }

        return null;
    }


    public function storeNotification(NotificationInterface $notification): void
    {
        $date = new ilDateTime(time(), IL_CAL_UNIX);

        if (empty($notification->getId())) {
            $notification->setCreatedAt($date);
        }

        $notification->setUpdatedAt($date);

        $values = [
            "name" => [ilDBConstants::T_TEXT, $notification->getName()],
            "title" => [ilDBConstants::T_TEXT, $notification->getTitle()],
            "description" => [ilDBConstants::T_TEXT, $notification->getDescription()],
            "parser" => [ilDBConstants::T_TEXT, $notification->getParser()],
            "parser_options" => [ilDBConstants::T_TEXT, json_encode($notification->getParserOptions(), JSON_THROW_ON_ERROR)],
            "subject" => [ilDBConstants::T_TEXT, json_encode($notification->getSubjects(), JSON_THROW_ON_ERROR)],
            "text" => [ilDBConstants::T_TEXT, json_encode($notification->getTexts(), JSON_THROW_ON_ERROR)],
            "created_at" => [ilDBConstants::T_TEXT, $notification->getCreatedAt()->get(IL_CAL_DATETIME)],
            "updated_at" => [ilDBConstants::T_TEXT, $notification->getUpdatedAt()->get(IL_CAL_DATETIME)]
        ];

        if (empty($notification->getId())) {
            $this->db->insert(
                Notification::getTableName(),
                $values
            );
            $notification->setId($this->db->getLastInsertId());
        } else {
            $this->db->update(
                Notification::getTableName(),
                $values,
                [
                    "id" => [ilDBConstants::T_INTEGER, $notification->getId()]
                ]
            );
        }
    }


    /**
     * @deprecated
     */
    protected function dropTablesLanguage(): void
    {
        if ($this->db->sequenceExists(NotificationLanguage::getTableName() . "g")) {
            $this->db->dropSequence(NotificationLanguage::getTableName() . "g");
        }
        $this->db->dropTable(NotificationLanguage::getTableName() . "g", false);
        //$this->db->dropAutoIncrementTable(NotificationLanguage::getTableName() . "g");

        if ($this->db->sequenceExists(NotificationLanguage::getTableName())) {
            $this->db->dropSequence(NotificationLanguage::getTableName());
        }
        $this->db->dropTable(NotificationLanguage::getTableName(), false);
        //$this->db->dropAutoIncrementTable(NotificationLanguage::getTableName());
    }


    /**
     * @deprecated
     */
    protected function getLanguageForNotification(int $notification_id, string $language): ?stdClass
    {
        /**
         * @var stdClass|null $l
         */
        $l = $this->db->fetchObjectClass($this->db->queryF('SELECT * FROM ' . $this->db
                ->quoteIdentifier(NotificationLanguage::getTableName()) . ' WHERE notification_id=%s AND language=%s', [
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_TEXT
        ], [$notification_id, $language]), stdClass::class);

        return $l;
    }


    protected function migrateLanguages(): void
    {
        if ($this->db->tableExists(NotificationLanguage::getTableName() . "g")) {
            $this->db->renameTable(NotificationLanguage::getTableName() . "g", NotificationLanguage::getTableName());
        }

        if ($this->db->tableExists(NotificationLanguage::getTableName())) {

            foreach (self::notifications4plugin()->notifications()->getNotifications() as $notification) {

                foreach (array_keys(MultilangualTabsInputGUI::getLanguages()) as $lang_key) {

                    $language = $this->getLanguageForNotification($notification->getId(), $lang_key);

                    if ($language !== null) {
                        $notification->setSubject($language->subject, $lang_key);
                        $notification->setText($language->text, $lang_key);
                    }
                }

                if (!empty($notification->default_language)) {
                    $notification->setSubject($notification->getSubject($notification->default_language, false), "default");
                    $notification->setText($notification->getText($notification->default_language, false), "default");
                }

                self::notifications4plugin()->notifications()->storeNotification($notification);
            }
        }

        $this->dropTablesLanguage();
    }
}
