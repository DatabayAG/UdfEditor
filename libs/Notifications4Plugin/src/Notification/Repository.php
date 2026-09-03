<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification;

use ilDateTime;
use ilDBConstants;
use ILIAS\DI\Container;
use ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI\MultilangualTabsInputGUI;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\Language\NotificationLanguage;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser\twigParser;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;
use stdClass;
use Throwable;

final class Repository implements RepositoryInterface
{
    use Notifications4PluginTrait;

    /**
     * @var RepositoryInterface|null
     */
    protected static $instance = null;
    private Container $dic;


    private function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }


    public static function getInstance(): RepositoryInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function deleteNotification(NotificationInterface $notification): void
    {
        $this->dic->database()->manipulateF('DELETE FROM ' . $this->dic->database()->quoteIdentifier(Notification::getTableName())
            . ' WHERE id=%s', [ilDBConstants::T_INTEGER], [$notification->getId()]);
    }


    public function dropTables(): void
    {
        $this->dic->database()->dropTable(Notification::getTableName(), false);

        //$this->dic->database()->dropAutoIncrementTable(Notification::getTableName());

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
        $stm = $this->dic->database()->queryF(
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
        $stm = $this->dic->database()->queryF(
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
        $stm = $this->dic->database()->query('SELECT *' . ' FROM ' . $this->dic->database()->quoteIdentifier(Notification::getTableName()));

        $notifications = [];
        while ($stdClass = $stm->fetchObject()) {
            $notifications[] = $this->factory()->fromDB($stdClass);
        }

        return $notifications;
    }


    public function getNotificationsCount(): int
    {

        $sql = 'SELECT COUNT(id) AS count' . ' FROM ' . $this->dic->database()->quoteIdentifier(Notification::getTableName());

        $result = $this->dic->database()->query($sql);

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

        if ($this->dic->database()->sequenceExists(Notification::getTableName())) {
            $this->dic->database()->dropSequence(Notification::getTableName());
        }

        $this->createAutoIncrement(Notification::getTableName(), "id");

        $this->migrateLanguages();

        if ($this->dic->database()->tableColumnExists(Notification::getTableName(), "default_language")) {
            $this->dic->database()->dropTableColumn(Notification::getTableName(), "default_language");
        }
    }

    public function createAutoIncrement(string $table_name, string $field): void
    {
        $this->dic->database()->manipulate('ALTER TABLE ' . $table_name . ' MODIFY COLUMN ' . $field . ' INT NOT NULL AUTO_INCREMENT');
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
            if ($this->dic->database()->tableExists($global_plugin_notification_table_name)
                && $this->dic->database()->tableExists($global_plugin_notification_language_table_name)
            ) {
                $result = $this->dic->database()->queryF('SELECT * FROM ' . $this->dic->database()
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

                    $result2 = $this->dic->database()->queryF('SELECT * FROM ' . $this->dic->database()
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
            $this->dic->database()->insert(
                Notification::getTableName(),
                $values
            );
            $notification->setId($this->dic->database()->getLastInsertId());
        } else {
            $this->dic->database()->update(
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
        if ($this->dic->database()->sequenceExists(NotificationLanguage::getTableName() . "g")) {
            $this->dic->database()->dropSequence(NotificationLanguage::getTableName() . "g");
        }
        $this->dic->database()->dropTable(NotificationLanguage::getTableName() . "g", false);
        //$this->dic->database()->dropAutoIncrementTable(NotificationLanguage::getTableName() . "g");

        if ($this->dic->database()->sequenceExists(NotificationLanguage::getTableName())) {
            $this->dic->database()->dropSequence(NotificationLanguage::getTableName());
        }
        $this->dic->database()->dropTable(NotificationLanguage::getTableName(), false);
        //$this->dic->database()->dropAutoIncrementTable(NotificationLanguage::getTableName());
    }


    /**
     * @deprecated
     */
    protected function getLanguageForNotification(int $notification_id, string $language): ?stdClass
    {
        /**
         * @var stdClass|null $l
         */
        $l = $this->dic->database()->fetchObjectClass($this->dic->database()->queryF('SELECT * FROM ' . $this->dic->database()
                ->quoteIdentifier(NotificationLanguage::getTableName()) . ' WHERE notification_id=%s AND language=%s', [
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_TEXT
        ], [$notification_id, $language]), stdClass::class);

        return $l;
    }


    protected function migrateLanguages(): void
    {
        if ($this->dic->database()->tableExists(NotificationLanguage::getTableName() . "g")) {
            $this->dic->database()->renameTable(NotificationLanguage::getTableName() . "g", NotificationLanguage::getTableName());
        }

        if ($this->dic->database()->tableExists(NotificationLanguage::getTableName())) {

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
