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

namespace ILIAS\Plugin\UdfEditor\Table;

use Exception;
use ILIAS\DI\Container;
use ILIAS\Plugin\UdfEditor\Model\ContentElement;
use ILIAS\Plugin\UdfEditor\Repository\ContentElementRepository;
use ILIAS\Plugin\UdfEditor\Utils\UiUtil;
use ilObjUdfEditor;
use ilTable2GUI;
use ilUdfEditorPlugin;
use ilUserDefinedFields;
use ilUtil;
use xudfFormConfigurationGUI;

class ContentElementTable extends ilTable2GUI
{
    public const string PLUGIN_CLASS_NAME = ilUdfEditorPlugin::class;

    protected ilUdfEditorPlugin $pl;
    private readonly Container $dic;
    private ContentElementRepository $content_element_repo;
    private UiUtil $ui_util;

    public function __construct(object $parent_gui, string $parent_cmd)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->pl = ilUdfEditorPlugin::getInstance();
        $this->content_element_repo = new ContentElementRepository();
        $this->ui_util = new UiUtil();

        parent::__construct($parent_gui, $parent_cmd);

        $this->setFormAction($this->dic->ctrl()->getFormAction($parent_gui));
        $this->setRowTemplate($this->pl->getDirectory() . '/templates/default/tpl.form_configuration_table_row.html');

        $this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getRelativeDirectory() . '/templates/default/jquery-ui.min.js');
        $this->dic->ui()->mainTemplate()->addCss($this->pl->getRelativeDirectory() . '/templates/default/jquery-ui.min.css');

        $this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getRelativeDirectory() . '/templates/default/sortable.js');
        $this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getRelativeDirectory() . '/templates/default/waiter.js');
        $this->dic->ui()->mainTemplate()->addCss($this->pl->getRelativeDirectory() . '/templates/default/waiter.css');
        $this->dic->ui()->mainTemplate()->addOnLoadCode("xoctWaiter.init();");

        $base_link = $this->dic->ctrl()->getLinkTarget($parent_gui, xudfFormConfigurationGUI::CMD_REORDER, '', true);
        $this->dic->ui()->mainTemplate()->addOnLoadCode("xudf = {'base_link': '$base_link'};");

        $this->initColumns();

        try {
            $this->setData(array_map(
                static function (ContentElement $content_element): array {
                    return $content_element->jsonSerialize();
                },
                $this->content_element_repo->readAllByObjId(
                    ilObjUdfEditor::_lookupObjectId((int) filter_input(INPUT_GET, 'ref_id')),
                    true,
                    true
                )
            ));
        } catch (Exception) {
            $this->setData([]);
        }
    }

    protected function initColumns(): void
    {
        $this->addColumn('', '', "10", true);
        $this->addColumn($this->dic->language()->txt('title'), 'title', "50");
        $this->addColumn($this->dic->language()->txt('description'), 'description', "100");
        $this->addColumn($this->dic->language()->txt('type'), 'type', "30");
        $this->addColumn($this->pl->txt('udf_type'), 'udf_type', "30");
        $this->addColumn($this->pl->txt('is_required'), 'is_required', "30");
        $this->addColumn('', '', "10", true);
    }

    protected function fillRow(array $a_set): void
    {
        $separator = $a_set['separator'];

        if (!$separator) {
            $udf_definition = $a_set['udf_field']
                ? ilUserDefinedFields::_getInstance()->getDefinition($a_set['udf_field'])
                : null;
            if (!$udf_definition) {
                $this->showMissingUdfMessage();
            }
        }

        $fieldName = $udf_definition['field_name'] ?? $this->pl->txt('field_not_found');
        $fieldType = isset($udf_definition['field_type'])
            ? $this->pl->txt('udf_field_type_' . $udf_definition['field_type'])
            : $this->pl->txt('field_not_found');

        $this->tpl->setVariable('ID', $a_set['id']);
        $this->tpl->setVariable(
            'TITLE',
            $separator
                ? $a_set['title']
                : $fieldName
        );
        $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        $this->tpl->setVariable('TYPE', $separator ? 'Separator' : $this->pl->txt('udf_field'));

        $this->tpl->setVariable(
            'UDF_TYPE',
            $separator
                ? '&nbsp'
                : $fieldType
        );

        if ($separator) {
            $udf_required = '&nbsp';
        } elseif ($a_set['required']) {
            $imagePath = ilUtil::getImagePath("standard/icon_ok.svg");
            $udf_required = "<img style='width: 1rem' src='$imagePath' alt='icon_ok'>";
        } else {
            $imagePath = ilUtil::getImagePath("standard/icon_not_ok.svg");
            $udf_required = "<img style='width: 1rem' src='$imagePath' alt='icon_not_ok'>";
        }

        $this->tpl->setVariable('IS_REQUIRED', $udf_required);

        $this->tpl->setVariable('ACTIONS', $this->buildActions($a_set['id']));
    }

    protected function showMissingUdfMessage(): void
    {
        static $already_shown;
        if (!$already_shown) {
            $this->ui_util->sendFailure($this->pl->txt('msg_missing_udf'));
            $already_shown = true;
        }
    }

    protected function buildActions($id): string
    {
        $uiFactory = $this->dic->ui()->factory();
        $uiRenderer = $this->dic->ui()->renderer();

        $this->dic->ctrl()->setParameter($this->parent_obj, 'element_id', $id);
        $actions = [
            $uiFactory->link()->standard(
                $this->dic->language()->txt('edit'),
                $this->dic->ctrl()->getLinkTarget($this->parent_obj, xudfFormConfigurationGUI::CMD_EDIT)
            ),
            $uiFactory->link()->standard(
                $this->dic->language()->txt('delete'),
                $this->dic->ctrl()->getLinkTarget($this->parent_obj, xudfFormConfigurationGUI::CMD_DELETE)
            )
        ];

        $actionDropdown = $uiFactory->dropdown()->standard($actions)->withLabel($this->dic->language()->txt('actions'));
        return $uiRenderer->render($actionDropdown);
    }
}
