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

use ilDateTime;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Plugin\UdfEditor\Model\LogEntry;

class LogEntryRepository
{
    public const string TABLE_NAME = "xudf_log_entry";

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

    /**
     * @return list<LogEntry>
     */
    public function readAllByObjId(int $obj_id): array
    {
        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME
            . " WHERE obj_id = %s",
            [ilDBConstants::T_INTEGER],
            [$obj_id]
        );

        $data = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $data[] = $this->map($row);
        }

        return $data;
    }

    /**
     * @return list<LogEntry>
     */
    public function readAllByUserId(int $user_id): array
    {
        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME
            . " WHERE usr_id = %s",
            [ilDBConstants::T_INTEGER],
            [$user_id]
        );

        $data = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $data[] = $this->map($row);
        }

        return $data;
    }

    public function read(int $id): ?LogEntry
    {
        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE id = %s",
            [ilDBConstants::T_INTEGER],
            [$id]
        );

        $row = $this->db->fetchAssoc($result);

        if (!$row) {
            return null;
        }

        return $this->map($row);
    }

    public function create(LogEntry $log_entry): bool
    {
        $id = $this->db->nextId(self::TABLE_NAME);
        $log_entry->setId($id);

        return $this->db->insert(
            self::TABLE_NAME,
            [
                "id" => [ilDBConstants::T_INTEGER, $log_entry->getId()],
                "obj_id" => [ilDBConstants::T_INTEGER, $log_entry->getObjId()],
                "usr_id" => [ilDBConstants::T_INTEGER, $log_entry->getUsrId()],
                "timestamp" => [ilDBConstants::T_TIMESTAMP, $log_entry->getTimestamp()->get(IL_CAL_DATETIME)],
                "values" => [ilDBConstants::T_CLOB, json_encode($log_entry->getValues(), JSON_THROW_ON_ERROR)],
            ]
        ) === 1;
    }

    public function update(LogEntry $log_entry): bool
    {
        return $this->db->update(
            self::TABLE_NAME,
            [
                    "obj_id" => [ilDBConstants::T_INTEGER, $log_entry->getObjId()],
                    "usr_id" => [ilDBConstants::T_INTEGER, $log_entry->getUsrId()],
                    "timestamp" => [ilDBConstants::T_TIMESTAMP, $log_entry->getTimestamp()->get(IL_CAL_DATETIME)],
                    "values" => [ilDBConstants::T_CLOB, json_encode($log_entry->getValues(), JSON_THROW_ON_ERROR)],
                ],
            ["id" => [ilDBConstants::T_INTEGER, $log_entry->getId()]]
        ) === 1;
    }

    public function store(LogEntry $log_entry): bool
    {
        if ($this->exists($log_entry->getId())) {
            return $this->update($log_entry);
        }

        return $this->create($log_entry);
    }

    public function delete(LogEntry $log_entry): bool
    {
        return $this->deleteById($log_entry->getId());
    }

    public function deleteById(int $id): bool
    {
        return $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE id = %s",
            [ilDBConstants::T_INTEGER],
            [$id]
        ) === 1;
    }

    public function exists(int $id): bool
    {
        $result = $this->db->queryF(
            "SELECT EXISTS(SELECT 1 FROM " . self::TABLE_NAME . " WHERE id = %s) AS does_exist",
            [ilDBConstants::T_INTEGER],
            [$id]
        );

        return (bool) $this->db->fetchAssoc($result)["does_exist"];
    }


    private function map(array $row): LogEntry
    {
        return (new LogEntry(
            (int) $row["obj_id"],
            (int) $row["usr_id"],
            new ilDateTime($row["timestamp"], IL_CAL_DATETIME),
            json_decode($row["values"], true, 512, JSON_THROW_ON_ERROR),
        ))->setId((int) $row["id"]);

    }
}
