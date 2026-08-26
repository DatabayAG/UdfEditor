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

use ILIAS\DI\Container;
use ILIAS\Plugin\UdfEditor\Model\LogEntry;
use ILIAS\Plugin\UdfEditor\Repository\LogEntryRepository;

class xudfLogTableGUI extends ilTable2GUI
{
    public const string ID_PREFIX = 'xudf_log_table_';
    public const string PLUGIN_CLASS_NAME = ilUdfEditorPlugin::class;
    public const string ROW_TEMPLATE = 'tpl.log_table_row.html';
    /**
     * @var ilFormPropertyGUI[]
     *
     */
    private array $filter_cache = [];

    private readonly Container $dic;
    private readonly ilUdfEditorPlugin $plugin;
    private LogEntryRepository $log_entry_repo;

    /**
     * @param xudfLogGUI|null $parent_obj
     */
    public function __construct(protected ?object $parent_obj, string $parent_cmd)
    {
        $this->setId(self::ID_PREFIX . $this->parent_obj->getObjId());

        parent::__construct($this->parent_obj, $parent_cmd);
        global $DIC;
        $this->dic = $DIC;
        $this->plugin = ilUdfEditorPlugin::getInstance();
        $this->log_entry_repo = new LogEntryRepository();

        $this->dic->ui()->mainTemplate()->addCss($this->plugin->getRelativeDirectory() . '/templates/default/log_table.css');

        if (!(str_starts_with($this->parent_cmd, "applyFilter")
            || str_starts_with($this->parent_cmd, "resetFilter"))
        ) {
            $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
            $this->setTitle($this->dic->language()->txt('history'));
            $this->setRowTemplate(static::ROW_TEMPLATE, $this->plugin->getDirectory());

            $this->initFilter();

            $this->addColumn($this->plugin->txt('values'));
            $this->addColumn($this->dic->language()->txt('user'), 'user');
            $this->addColumn($this->dic->language()->txt('date'), 'timestamp');
            $this->initData();
        } else {
            // Speed up, not init data on applyFilter or resetFilter, only filter
            $this->initFilter();
        }
    }

    /**
     * @throws Exception
     */
    protected function initData(): void
    {
        /** @var ilSelectInputGUI $userFilter */
        $userFilter = $this->filter_cache["user"];
        $filter_user = $userFilter->getValue();

        $log_entries = $this->log_entry_repo->readAllByObjId($this->parent_obj->getObjId());
        if ($filter_user !== null) {
            $log_entries = $this->log_entry_repo->readAllByUserId((int) $filter_user);
        }
        $this->setData(array_map(static function (LogEntry $log_entry): array {
            return $log_entry->jsonSerialize();
        }, $log_entries));
    }

    public function initFilter(): void
    {
        $this->setDisableFilterHiding(true);

        $userFilter = new ilSelectInputGUI($this->lng->txt("user"), "user");
        $userFilter->setOptions($this->getUserFilterOptions());
        $this->filter_cache["user"] = $userFilter;

        $this->addFilterItem($userFilter);

        if ($this->hasSessionValue($userFilter->getFieldId())) {
            $userFilter->readFromSession();
        }
    }

    /**
     *
     *
     * @deprecated
     */
    public function txt(string $key, ?string $default = null): string
    {
        return $this->plugin->txt($key);
    }

    protected function hasSessionValue(string $field_id): bool
    {
        // Not set (null) on first visit, false on reset filter, string if is set
        return (
            ilsession::has("form_{$this->getId()}_$field_id")
            && ilSession::get("form_{$this->getId()}_$field_id") !== false
        );
    }

    protected function fillRow(array $row): void
    {
        $this->tpl->setVariable('VALUES', $this->formatValues($row['values']));
        $this->tpl->setVariable('USER', ilObjUser::_lookupFullname($row['usr_id']) . ', [' . ilObjUser::_lookupLogin($row['usr_id']) . ']');
        $this->tpl->setVariable('DATE', $row['timestamp']->get(IL_CAL_FKT_DATE, 'd.m.Y H:i:s'));
    }

    protected function formatValues(array $values): string
    {
        // this should be a template, but i'm too lazy
        $string = '<table class="xudf_log_values">';
        $string .= '<tr><th>' . $this->plugin->txt('udf_field') . '</th><th>' . $this->dic->language()->txt('value') . '</th></tr>';
        foreach ($values as $title => $value) {
            $string .= '<tr>';
            $string .= '<td>' . $title . '</td>';
            $string .= '<td>' . $value . '</td>';
            $string .= '</tr>';
        }

        return $string . '</table>';
    }

    protected function getUserFilterOptions(): array
    {
        $result = $this->dic->database()->query(
            'SELECT DISTINCT(usr_id) FROM ' . LogEntryRepository::TABLE_NAME
        );
        $options = ['' => '-'];
        while ($rec = $this->dic->database()->fetchAssoc($result)) {
            $options[$rec['usr_id']] = ilObjUser::_lookupFullname($rec['usr_id']) . ', [' . ilObjUser::_lookupLogin($rec['usr_id']) . ']';
        }

        return $options;
    }
}
