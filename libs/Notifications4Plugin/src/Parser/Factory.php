<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser;

use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

final class Factory implements FactoryInterface
{
    use Notifications4PluginTrait;

    /**
     * @var FactoryInterface|null
     */
    protected static $instance = null;


    private function __construct()
    {

    }


    public static function getInstance(): FactoryInterface
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function twig(): twigParser
    {
        return new twigParser();
    }
}
