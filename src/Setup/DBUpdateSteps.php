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

namespace ILIAS\Plugin\UdfEditor\Setup\Migration;

use ilDatabaseUpdateSteps;
use ilDBConstants;
use ilDBInterface;
use ilDBStepExecutionDB;
use ilDBStepReader;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Repository;
use ILIAS\Plugin\UdfEditor\Repository\ContentElementRepository;
use ILIAS\Plugin\UdfEditor\Repository\SettingsRepository;

class DBUpdateSteps implements ilDatabaseUpdateSteps
{
    public const string PREFIX = "step_";

    private ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function install(ilDBInterface $db): void
    {
        $this->prepare($db);

        $execution_log = new ilDBStepExecutionDB($this->db, fn() => new \DateTime());
        $step_reader = new ilDBStepReader();

        $last_started_step = $execution_log->getLastStartedStep(self::class);
        $last_finished_step = $execution_log->getLastFinishedStep(self::class);

        foreach ($step_reader->readStepNumbers(self::class, self::PREFIX) as $step) {
            if ($step <= $last_finished_step) {
                continue;
            }
            $execution_log->started(self::class, $step);
            $method = self::PREFIX . $step;
            $this->$method();
            $execution_log->finished(self::class, $step);
        }
    }

    public function uninstall(ilDBInterface $db): void
    {
        $this->prepare($db);
        $this->db->manipulateF(
            "DELETE FROM il_db_steps WHERE class = %s",
            [ilDBConstants::T_TEXT],
            [self::class]
        );

        $tables_to_drop = [
            SettingsRepository::TABLE_NAME,
            ContentElementRepository::TABLE_NAME
        ];

        foreach ($tables_to_drop as $table_name) {
            $this->db->dropTable($table_name, false);

        }

        $this->db->manipulateF(
            "DELETE FROM copg_pobj_def WHERE component=%s",
            [ilDBConstants::T_TEXT],
            ["Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor"]
        );
    }

    public function step_1(): void
    {
        if ($this->db->tableExists("xudf_element")) {
            return;
        }

        $this->db->createTable("xudf_element", [
            "id" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 8,
                "notnull" => true,
            ],
            "obj_id" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 8,
                "notnull" => true,
            ],
            "sort" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 8,
            ],
            "is_separator" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 1,
                "default" => false
            ],
            "udf_field" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 8,
            ],
            "title" => [
                "type" => ilDBConstants::T_TEXT,
                "length" => 256,
            ],
            "description" => [
                "type" => ilDBConstants::T_TEXT,
                "length" => 256,
            ],
            "is_required" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 1,
                "default" => false,
            ],
        ]);
        $this->db->addPrimaryKey("xudf_element", ["id"]);
        $this->db->createSequence("xudf_element");
    }

    public function step_2(): void
    {
        if ($this->db->tableExists("xudf_setting")) {
            return;
        }

        $this->db->createTable("xudf_setting", [
            "obj_id" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 8,
                "notnull" => true,
            ],
            "is_online" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 1,
                "default" => false,
                "notnull" => true,
            ],
            "show_info_tab" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 1,
                "default" => false,
                "notnull" => true,
            ],
            "mail_notification" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 1,
                "default" => false,
                "notnull" => true,
            ],
            "additional_notification" => [
                "type" => ilDBConstants::T_TEXT,
                "length" => 256,
                "notnull" => true,
            ],
            "redirect_type" => [
                "type" => ilDBConstants::T_TEXT,
                "length" => 64,
                "default" => "stay_in_form",
                "notnull" => true,
            ],
            "redirect_value" => [
                "type" => ilDBConstants::T_TEXT,
                "length" => 256,
                "notnull" => true,
            ],
            "notification_name" => [
                "type" => ilDBConstants::T_TEXT,
                "length" => 256,
                "notnull" => true,
            ],
            "always_edit" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 1,
                "notnull" => true,
                "default" => false
            ]
        ]);
        $this->db->addPrimaryKey("xudf_setting", ["obj_id"]);
    }

    public function step_3(): void
    {
        if ($this->db->tableExists("xudf_log_entry")) {
            return;
        }

        $this->db->createTable("xudf_log_entry", [
            "id" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 8,
                "notnull" => true,
            ],
            "obj_id" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 8,
                "notnull" => true,
            ],
            "usr_id" => [
                "type" => ilDBConstants::T_INTEGER,
                "length" => 8,
                "notnull" => true,
            ],
            "values" => [
                "type" => ilDBConstants::T_CLOB,
                "notnull" => true,
            ],
            "timestamp" => [
                "type" => ilDBConstants::T_TIMESTAMP,
                "notnull" => true,
            ]
        ]);
        $this->db->addPrimaryKey("xudf_log_entry", ["id"]);
        $this->db->createSequence("xudf_log_entry");
    }

    public function step_4(): void
    {
        $this->db->modifyTableColumn("copg_pobj_def", "component", ["length" => 120]);
        $sql_query = $this->db->query("SELECT * FROM copg_pobj_def WHERE parent_type = 'xudf'");
        if ($this->db->numRows($sql_query) === 0) {
            $this->db->insert("copg_pobj_def", [
                "parent_type" => [ilDBConstants::T_TEXT, "xudf"],
                "class_name" => [ilDBConstants::T_TEXT, "xudfPageObject"],
                "directory" => [ilDBConstants::T_TEXT, "classes/Content/PageEditor"],
                "component" => [ilDBConstants::T_TEXT, "Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor"]
            ]);
        }
    }

    public function step_5(): void
    {
        $result = $this->db->query("SELECT * FROM copg_pobj_def WHERE parent_type = 'xudf'");
        if ($this->db->numRows($result) === 0) {
            $this->db->insert("copg_pobj_def", [
                "parent_type" => [ilDBConstants::T_TEXT, "xudf"],
                "class_name" => [ilDBConstants::T_TEXT, "xudfPageObject"],
                "directory" => [ilDBConstants::T_TEXT, "classes/Content/PageEditor"],
                "component" => [ilDBConstants::T_TEXT, "Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor"]
            ]);
        }
    }

    public function step_6(): void
    {
        Repository::getInstance()->installTables();
    }

    public function step_7(): void
    {
        if (
            !$this->db->tableExists("xudf_element")
            || !$this->db->tableColumnExists("xudf_element", "udf_field")
        ) {
            return;
        }

        $old_to_new_id_map = [];

        if ($this->db->tableExists("udf_field_id_map")) {
            $result = $this->db->query("SELECT * FROM udf_field_id_map");
            while ($row = $this->db->fetchAssoc($result)) {
                $old_to_new_id_map[(int) $row["old_field_id"]] = $row["field_id"];
            }
        }

        $result = $this->db->query("SELECT id, udf_field FROM xudf_element WHERE udf_field IS NOT NULL");
        $current_udf_field_map = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $udf_field_id = $row["udf_field"];
            if (is_string($udf_field_id) || !is_numeric($udf_field_id)) {
                continue; // Skip fields that may already be in uuid format
            }
            $current_udf_field_map[(int) $row["id"]] = (int) $udf_field_id;
        }

        $this->db->modifyTableColumn(
            "xudf_element",
            "udf_field",
            [
                "type" => ilDBConstants::T_TEXT,
                "length" => 64,
            ]
        );

        $prepared_statement = $this->db->prepareManip(
            "UPDATE xudf_element SET udf_field = ? WHERE id = ?",
            [
                ilDBConstants::T_TEXT,
                ilDBConstants::T_INTEGER
            ]
        );

        foreach ($current_udf_field_map as $id => $udf_field_id) {
            $new_udf_id = $old_to_new_id_map[$udf_field_id] ?? null;

            $this->db->execute($prepared_statement, [
                $new_udf_id,
                $id
            ]);
        }
    }
}
