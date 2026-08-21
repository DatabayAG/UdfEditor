<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Exception;

use ilException;

class Notifications4PluginException extends ilException
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
