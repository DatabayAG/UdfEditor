<#1>
<?php
/** @var $ilDB \ilDBInterface */

if (!$ilDB->tableExists("xudf_element")) {
    $ilDB->createTable("xudf_element", [
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
    $ilDB->addPrimaryKey("xudf_element", ["id"]);
    $ilDB->createSequence("xudf_element");
}

if (!$ilDB->tableExists("xudf_setting")) {
    $ilDB->createTable("xudf_setting", [
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
    $ilDB->addPrimaryKey("xudf_setting", ["obj_id"]);
}

if (!$ilDB->tableExists("xudf_log_entry")) {
    $ilDB->createTable("xudf_log_entry", [
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
    $ilDB->addPrimaryKey("xudf_log_entry", ["id"]);
    $ilDB->createSequence("xudf_log_entry");

}

?>
<#2>
<?php
global $DIC;
$DIC->database()->modifyTableColumn('copg_pobj_def', 'component', ['length' => 120]);
$sql_query = $DIC->database()->query('SELECT * FROM copg_pobj_def WHERE parent_type = "xudf"');
if ($DIC->database()->numRows($sql_query) === 0) {
    $DIC->database()->insert('copg_pobj_def', [
            'parent_type' => [ilDBConstants::T_TEXT, 'xudf'],
            'class_name' => [ilDBConstants::T_TEXT, 'xudfPageObject'],
            'directory' => [ilDBConstants::T_TEXT, 'classes/Content/PageEditor'],
            'component' => [ilDBConstants::T_TEXT, 'Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor']
    ]);
}
?>
<#3>
<?php
//Kept empty for compatibility
?>
<#4>
<?php
// TODO: if this gets deleted again, just plant it in xudfPageObjectGUI::__construct e.g.
global $DIC;
$sql_query = $DIC->database()->query('SELECT * FROM copg_pobj_def WHERE parent_type = "xudf"');
if ($DIC->database()->numRows($sql_query) === 0) {
    $DIC->database()->insert('copg_pobj_def', [
            'parent_type' => [ilDBConstants::T_TEXT, 'xudf'],
            'class_name' => [ilDBConstants::T_TEXT, 'xudfPageObject'],
            'directory' => [ilDBConstants::T_TEXT, 'classes/Content/PageEditor'],
            'component' => [ilDBConstants::T_TEXT, 'Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor']
    ]);
}
?>
<#5>
<?php
//Kept empty for compatibility
?>
<#6>
<?php
//Kept empty for compatibility
?>
<#7>
<?php
\ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Repository::getInstance()->installTables();
?>
<#8>
<?php
//Kept empty for compatibility
?>
