<#1>
<?php
xudfSetting::updateDB();
xudfContentElement::updateDB();
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
xudfSetting::updateDB();
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
xudfSetting::updateDB();
?>
<#6>
<?php
xudfContentElement::updateDB();
xudfLogEntry::updateDB();
xudfSetting::updateDB();
?>
<#7>
<?php
\ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Repository::getInstance()->installTables();
\xudfSetting::updateDB();
?>
<#8>
<?php
\xudfSetting::updateDB();
?>
