<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils;

use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Repository as Notifications4PluginRepository;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\RepositoryInterface as Notifications4PluginRepositoryInterface;

trait Notifications4PluginTrait
{
    protected static function notifications4plugin(): Notifications4PluginRepositoryInterface
    {
        return Notifications4PluginRepository::getInstance();
    }
}
