<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser;

use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Exception\Notifications4PluginException;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationInterface;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

final class Repository implements RepositoryInterface
{
    use Notifications4PluginTrait;

    /**
     * @var RepositoryInterface|null
     */
    protected static $instance = null;
    /**
     * @var Parser[]
     */
    protected $parsers = [];


    private function __construct()
    {
        $this->addParser($this->factory()->twig());
    }


    public static function getInstance(): RepositoryInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function addParser(Parser $parser): void
    {
        $this->parsers[$parser->getClass()] = $parser;
    }


    public function dropTables(): void
    {

    }


    public function factory(): FactoryInterface
    {
        return Factory::getInstance();
    }


    public function getParserByClass(string $parser_class): Parser
    {
        if (isset($this->getPossibleParsers()[$parser_class])) {
            return $this->getPossibleParsers()[$parser_class];
        } else {
            throw new Notifications4PluginException("Invalid parser class $parser_class");
        }
    }


    public function getParserForNotification(NotificationInterface $notification): Parser
    {
        return $this->getParserByClass($notification->getParser());
    }


    public function getPossibleParsers(): array
    {
        return $this->parsers;
    }


    public function installTables(): void
    {

    }


    public function parseSubject(Parser $parser, NotificationInterface $notification, array $placeholders = [], ?string $language = null): string
    {
        return $parser->parse($notification->getSubject($language), $placeholders, $notification->getParserOptions());
    }


    public function parseText(Parser $parser, NotificationInterface $notification, array $placeholders = [], ?string $language = null): string
    {
        return $parser->parse($notification->getText($language), $placeholders, $notification->getParserOptions());
    }
}
