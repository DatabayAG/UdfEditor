<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Sender;

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


    public function externalMail(string $from = "", $to = ""): ExternalMailSender
    {
        return new ExternalMailSender($from, $to);
    }


    public function internalMail($user_from = 0, $user_to = ""): InternalMailSender
    {
        return new InternalMailSender($user_from, $user_to);
    }


    public function vcalendar($user_from = 0, $to = "", string $method = vcalendarSender::METHOD_REQUEST, string $uid = "", int $startTime = 0, int $endTime = 0, int $sequence = 0): vcalendarSender
    {
        return new vcalendarSender($user_from, $to, $method, $uid, $startTime, $endTime, $sequence);
    }
}
