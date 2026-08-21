<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Sender;

use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationInterface;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

final class Repository implements RepositoryInterface
{
    use Notifications4PluginTrait;

    /**
     * @var RepositoryInterface|null
     */
    protected static $instance = null;


    private function __construct()
    {

    }


    public static function getInstance(): RepositoryInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function dropTables(): void
    {

    }


    public function factory(): FactoryInterface
    {
        return Factory::getInstance();
    }


    public function installTables(): void
    {

    }


    public function send(Sender $sender, NotificationInterface $notification, array $placeholders = [], ?string $language = null): void
    {
        $parser = self::notifications4plugin()->parser()->getParserForNotification($notification);

        $sender->setSubject(self::notifications4plugin()->parser()->parseSubject($parser, $notification, $placeholders, $language));

        $sender->setMessage(self::notifications4plugin()->parser()->parseText($parser, $notification, $placeholders, $language));

        $sender->send();
    }
}
