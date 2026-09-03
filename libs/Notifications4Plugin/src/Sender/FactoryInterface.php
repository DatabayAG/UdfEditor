<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Sender;

use ilObjUser;

interface FactoryInterface
{
    /**
     * @param string|array $to
     */
    public function externalMail(string $from = "", $to = ""): ExternalMailSender;


    /**
     * @param int|string|ilObjUser $user_from
     * @param int|string|ilObjUser $user_to
     */
    public function internalMail($user_from = 0, $user_to = ""): InternalMailSender;


    /**
     * @param int|string|ilObjUser $user_from
     * @param string|array $to
     */
    public function vcalendar($user_from = 0, $to = "", string $method = vcalendarSender::METHOD_REQUEST, string $uid = "", int $startTime = 0, int $endTime = 0, int $sequence = 0): vcalendarSender;
}
