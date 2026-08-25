<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Plugin\UdfEditor\Model;

class Settings
{
    public const string DB_TABLE_NAME = 'xudf_setting';

    public const string REDIRECT_STAY_IN_FORM = 'stay_in_form';
    public const string REDIRECT_TO_ILIAS_OBJECT = 'to_ilias_object';
    public const string REDIRECT_TO_URL = 'to_url';
    public const string REDIRECT_TO_CALLER = 'to_caller';

    public function __construct(
        private readonly int $obj_id,
        private bool $online = false,
        private bool $show_info_tab = false,
        private bool $mail_notification = false,
        private string $additional_notification = "",
        private string $redirect_type = self::REDIRECT_STAY_IN_FORM,
        private string $redirect_value = "",
        private string $notification_name = "",
        private bool $always_edit = false,
    ) {
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setIsOnline(bool $online): self
    {
        $this->online = $online;
        return $this;
    }

    public function isShowInfoTab(): bool
    {
        return $this->show_info_tab;
    }

    public function setShowInfoTab(bool $show_info_tab): self
    {
        $this->show_info_tab = $show_info_tab;
        return $this;
    }

    public function isMailNotification(): bool
    {
        return $this->mail_notification;
    }

    public function setMailNotification(bool $mail_notification): self
    {
        $this->mail_notification = $mail_notification;
        return $this;
    }

    public function getAdditionalNotification(): string
    {
        return $this->additional_notification;
    }

    public function setAdditionalNotification(string $additional_notification): self
    {
        $this->additional_notification = $additional_notification;
        return $this;
    }

    public function getRedirectType(): string
    {
        return $this->redirect_type;
    }

    public function setRedirectType(string $redirect_type): self
    {
        $this->redirect_type = $redirect_type;
        return $this;
    }

    public function getRedirectValue(): string
    {
        return $this->redirect_value;
    }

    public function setRedirectValue(string $redirect_value): self
    {
        $this->redirect_value = $redirect_value;
        return $this;
    }

    public function getNotificationName(): string
    {
        return $this->notification_name;
    }

    public function setNotificationName(string $notification_name): self
    {
        $this->notification_name = $notification_name;
        return $this;
    }

    public function isAlwaysEdit(): bool
    {
        return $this->always_edit;
    }

    public function setAlwaysEdit(bool $always_edit): self
    {
        $this->always_edit = $always_edit;
        return $this;
    }
}
