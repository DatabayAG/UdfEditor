<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Parser;

use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Exception\Notifications4PluginException;

interface Parser
{
    /**
     * @abstract
     */
    public const string DOC_LINK = "";
    /**
     * @abstract
     */
    public const string NAME = "";


    public function getClass(): string;


    public function getDocLink(): string;


    public function getName(): string;


    /**
     * @return Input[]
     */
    public function getOptionsFields(): array;


    /**
     * @throws Notifications4PluginException
     */
    public function parse(string $text, array $placeholders = [], array $options = []): string;
}
