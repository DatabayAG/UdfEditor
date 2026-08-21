<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser;

use ILIAS\DI\Container;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

abstract class AbstractParser implements Parser
{
    use Notifications4PluginTrait;

    protected Container $dic;


    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }


    public function getClass(): string
    {
        return static::class;
    }


    public function getDocLink(): string
    {
        return static::DOC_LINK;
    }


    public function getName(): string
    {
        return static::NAME;
    }


    protected function fixLineBreaks(string $html): string
    {
        return str_ireplace(["&lt;br&gt;", "&lt;br/&gt;", "&lt;br /&gt;"], ["<br>", "<br/>", "<br />"], $html);
    }
}
