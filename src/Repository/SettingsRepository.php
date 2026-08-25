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

namespace ILIAS\Plugin\UdfEditor\Repository;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Plugin\UdfEditor\Model\Settings;

class SettingsRepository
{
    public const string TABLE_NAME = "xudf_setting";

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            global $DIC;
            $this->db = $DIC->database();
        }
    }

    public function read(int $obj_id): ?Settings
    {
        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE obj_id = %s",
            [ilDBConstants::T_INTEGER],
            [$obj_id]
        );

        $row = $this->db->fetchAssoc($result);

        if (!$row) {
            return null;
        }

        return $this->map($row);
    }

    public function create(Settings $settings): bool
    {
        return $this->db->insert(
            self::TABLE_NAME,
            [
                    "obj_id" => [ilDBConstants::T_INTEGER, $settings->getObjId()],
                    "is_online" => [ilDBConstants::T_INTEGER, $settings->isOnline()],
                    "show_info_tab" => [ilDBConstants::T_INTEGER, $settings->isShowInfoTab()],
                    "mail_notification" => [ilDBConstants::T_INTEGER, $settings->isMailNotification()],
                    "additional_notification" => [ilDBConstants::T_TEXT, $settings->getAdditionalNotification()],
                    "redirect_type" => [ilDBConstants::T_TEXT, $settings->getRedirectType()],
                    "redirect_value" => [ilDBConstants::T_TEXT, $settings->getRedirectValue()],
                    "notification_name" => [ilDBConstants::T_TEXT, $settings->getNotificationName()],
                    "always_edit" => [ilDBConstants::T_INTEGER, $settings->isAlwaysEdit()]
                ]
        ) === 1;
    }

    public function update(Settings $settings): bool
    {
        return $this->db->update(
            self::TABLE_NAME,
            [
                    "is_online" => [ilDBConstants::T_INTEGER, $settings->isOnline()],
                    "show_info_tab" => [ilDBConstants::T_INTEGER, $settings->isShowInfoTab()],
                    "mail_notification" => [ilDBConstants::T_INTEGER, $settings->isMailNotification()],
                    "additional_notification" => [ilDBConstants::T_TEXT, $settings->getAdditionalNotification()],
                    "redirect_type" => [ilDBConstants::T_TEXT, $settings->getRedirectType()],
                    "redirect_value" => [ilDBConstants::T_TEXT, $settings->getRedirectValue()],
                    "notification_name" => [ilDBConstants::T_TEXT, $settings->getNotificationName()],
                    "always_edit" => [ilDBConstants::T_INTEGER, $settings->isAlwaysEdit()]
                ],
            ["obj_id" => [ilDBConstants::T_INTEGER, $settings->getObjId()]]
        ) === 1;
    }

    public function store(Settings $settings): bool
    {
        if ($this->exists($settings->getObjId())) {
            return $this->update($settings);
        }

        return $this->create($settings);
    }

    public function delete(Settings $settings): bool
    {
        return $this->deleteById($settings->getObjId());
    }

    public function deleteById(int $obj_id): bool
    {
        return $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE obj_id = %s",
            [ilDBConstants::T_INTEGER],
            [$obj_id]
        ) === 1;
    }

    public function exists(int $obj_id): bool
    {
        $result = $this->db->queryF(
            "SELECT EXISTS(SELECT 1 FROM " . self::TABLE_NAME . " WHERE obj_id = %s) AS does_exist",
            [ilDBConstants::T_INTEGER],
            [$obj_id]
        );

        return (bool) $this->db->fetchAssoc($result)["does_exist"];
    }

    private function map(array $row): Settings
    {
        return new Settings(
            (int) $row["obj_id"],
            (bool) $row["is_online"],
            (bool) $row["show_info_tab"],
            (bool) $row["mail_notification"],
            $row["additional_notification"],
            $row["redirect_type"],
            $row["redirect_value"],
            $row["notification_name"],
            (bool) $row["always_edit"],
        );
    }
}
