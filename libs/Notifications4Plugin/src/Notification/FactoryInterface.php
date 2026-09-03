<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification;

use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\Form\FormBuilder;
use stdClass;

interface FactoryInterface
{
    public function fromDB(stdClass $data): NotificationInterface;


    public function newFormBuilderInstance(object $parentGui, NotificationInterface $notification): FormBuilder;


    public function newInstance(): NotificationInterface;
}
