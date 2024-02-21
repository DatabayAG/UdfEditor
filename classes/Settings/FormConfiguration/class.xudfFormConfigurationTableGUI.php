<?php


use ILIAS\DI\Container;

class xudfFormConfigurationTableGUI extends ilTable2GUI
{


    public const PLUGIN_CLASS_NAME = ilUdfEditorPlugin::class;

    protected ilUdfEditorPlugin $pl;
    private Container $dic;


    /**
     * @throws arException
     */
    public function __construct($parent_gui, $parent_cmd)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->pl = ilUdfEditorPlugin::getInstance();

        parent::__construct($parent_gui, $parent_cmd);

        $this->setFormAction($this->dic->ctrl()->getFormAction($parent_gui));
        $this->setRowTemplate($this->pl->getDirectory() . '/templates/default/tpl.form_configuration_table_row.html');

        $this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getDirectory() . '/templates/default/sortable.js');
        $this->dic->ui()->mainTemplate()->addJavaScript($this->pl->getDirectory() . '/templates/default/waiter.js');
        $this->dic->ui()->mainTemplate()->addCss($this->pl->getDirectory() . '/templates/default/waiter.css');
        $this->dic->ui()->mainTemplate()->addOnLoadCode("xoctWaiter.init();");

        $base_link = $this->dic->ctrl()->getLinkTarget($parent_gui, xudfFormConfigurationGUI::CMD_REORDER, '', true);
        $this->dic->ui()->mainTemplate()->addOnLoadCode("xudf = {'base_link': '$base_link'};");

        $this->initColumns();
        $this->setData(xudfContentElement::where(['obj_id' => ilObjUdfEditor::_lookupObjectId(filter_input(INPUT_GET, 'ref_id'))])->orderBy('sort')->getArray());
    }

    protected function initColumns(): void
    {
        $this->addColumn('', '', 10, true);
        $this->addColumn($this->dic->language()->txt('title'), 'title', 50);
        $this->addColumn($this->dic->language()->txt('description'), 'description', 100);
        $this->addColumn($this->dic->language()->txt('type'), 'type', 30);
        $this->addColumn($this->pl->txt('udf_type'), 'udf_type', 30);
        $this->addColumn($this->pl->txt('is_required'), 'is_required', 30);
        $this->addColumn('', '', 10, true);
    }

    protected function fillRow(array $a_set): void
    {
        $udf_definition = ilUserDefinedFields::_getInstance()->getDefinition($a_set['udf_field']);

        if (!$a_set['is_separator'] && !$udf_definition) {
            $this->showMissingUdfMessage();
        }

        $this->tpl->setVariable('ID', $a_set['id']);
        $this->tpl->setVariable(
            'TITLE',
            $a_set['is_separator'] ?
                $a_set['title']
                : ($udf_definition['field_name'] ?: $this->pl->txt('field_not_found'))
        );
        $this->tpl->setVariable('DESCRIPTION', $a_set['description']);
        $this->tpl->setVariable('TYPE', $a_set['is_separator'] ? 'Separator' : $this->pl->txt('udf_field'));

        $this->tpl->setVariable(
            'UDF_TYPE',
            $a_set['is_separator'] ? '&nbsp'
                : ($udf_definition['field_type'] ? $this->pl->txt('udf_field_type_' . $udf_definition['field_type']) : $this->pl->txt('field_not_found'))
        );

        if ($a_set['is_separator']) {
            $udf_required = '&nbsp';
        } else {
            if ($a_set['is_required'] == 1) {
                $udf_required = '<img style="width: 1rem" src="./templates/default/images/standard/icon_ok.svg">';
            } else {
                $udf_required = '<img style="width: 1rem" src="./templates/default/images/standard/icon_not_ok.svg">';
            }
        }

        $this->tpl->setVariable('IS_REQUIRED', $udf_required);

        $this->tpl->setVariable('ACTIONS', $this->buildActions($a_set['id']));
    }

    protected function showMissingUdfMessage(): void
    {
        static $already_shown;
        if (!$already_shown) {
            $this->tpl->setOnScreenMessage("failure", $this->pl->txt('msg_missing_udf'), true);
            $already_shown = true;
        }
    }

    protected function buildActions($id): string
    {
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle($this->dic->language()->txt('actions'));
        $this->dic->ctrl()->setParameter($this->parent_obj, 'element_id', $id);

        $actions->addItem($this->dic->language()->txt('edit'), 'edit', $this->dic->ctrl()->getLinkTarget($this->parent_obj, xudfFormConfigurationGUI::CMD_EDIT));
        $actions->addItem($this->dic->language()->txt('delete'), 'delete', $this->dic->ctrl()->getLinkTarget($this->parent_obj, xudfFormConfigurationGUI::CMD_DELETE));

        return $actions->getHTML();
    }
}
