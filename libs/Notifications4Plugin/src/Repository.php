<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin;

use ilPlugin;
use LogicException;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\Repository as NotificationsRepository;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\RepositoryInterface as NotificationsRepositoryInterface;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser\Repository as ParserRepository;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser\RepositoryInterface as ParserRepositoryInterface;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Sender\Repository as SenderRepository;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Sender\RepositoryInterface as SenderRepositoryInterface;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

final class Repository implements RepositoryInterface
{
    use Notifications4PluginTrait;

    /**
     * @var RepositoryInterface|null
     */
    protected static $instance = null;
    /**
     * @var array
     */
    protected $placeholder_types;
    /**
     * @var ilPlugin
     */
    protected $plugin;
    /**
     * @var string
     */
    protected $table_name_prefix = "";


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
        $this->notifications()->dropTables();
        $this->parser()->dropTables();
        $this->sender()->dropTables();
    }


    public function getPlaceholderTypes(): array
    {
        if (empty($this->placeholder_types)) {
            throw new LogicException("placeholder types is empty - please call withPlaceholderTypes earlier!");
        }

        return $this->placeholder_types;
    }


    public function getPlugin(): ilPlugin
    {
        if (empty($this->plugin)) {
            throw new LogicException("plugin is empty - please call withPlugin earlier!");
        }

        return $this->plugin;
    }


    public function getTableNamePrefix(): string
    {
        if (empty($this->table_name_prefix)) {
            throw new LogicException("table name prefix is empty - please call withTableNamePrefix earlier!");
        }

        return $this->table_name_prefix;
    }


    public function installTables(): void
    {
        $this->notifications()->installTables();
        $this->parser()->installTables();
        $this->sender()->installTables();
    }


    public function notifications(): NotificationsRepositoryInterface
    {
        return NotificationsRepository::getInstance();
    }


    public function parser(): ParserRepositoryInterface
    {
        return ParserRepository::getInstance();
    }


    public function sender(): SenderRepositoryInterface
    {
        return SenderRepository::getInstance();
    }


    public function withPlaceholderTypes(array $placeholder_types): RepositoryInterface
    {
        $this->placeholder_types = $placeholder_types;

        return $this;
    }


    public function withPlugin(ilPlugin $plugin): RepositoryInterface
    {
        $this->plugin = $plugin;

        return $this;
    }


    public function withTableNamePrefix(string $table_name_prefix): RepositoryInterface
    {
        $this->table_name_prefix = $table_name_prefix;

        return $this;
    }
}
