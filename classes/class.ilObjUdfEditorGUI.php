<?php

use ILIAS\DI\Container;


use srag\Plugins\UdfEditor\Exception\UDFNotFoundException;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * @ilCtrl_isCalledBy ilObjUdfEditorGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjUdfEditorGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilEditClipboardGUI
 */
class ilObjUdfEditorGUI extends ilObjectPluginGUI
{
    

    public const PLUGIN_CLASS_NAME = ilUdfEditorPlugin::class;
    public const TAB_CONTENT = 'content';
    public const TAB_INFO = 'info';
    public const TAB_SETTINGS = 'settings';
    public const TAB_HISTORY = 'log_history';
    public const TAB_PERMISSIONS = 'permissions';
    public const CMD_INDEX = 'index';
    public const CMD_SETTINGS = 'showSettings';

    private Container $dic;

    public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        global $DIC;
        $this->dic = $DIC;
    }

    public function executeCommand(): void
    {
        $next_class = $this->dic->ctrl()->getNextClass();
        $cmd = $this->dic->ctrl()->getCmd();
        if (!ilObjUdfEditorAccess::hasReadAccess() && $next_class != strtolower(ilInfoScreenGUI::class) && $cmd != "infoScreen") {
            ilUtil::sendFailure(self::plugin()->translate('access_denied'), true);
            $this->dic->ctrl()->returnToParent($this);
        }
        if (self::version()->is6()) {
            $this->tpl->loadStandardTemplate();
        } else {
            $this->tpl->getStandardTemplate();
        }

        try {
            switch ($next_class) {
                case strtolower(xudfContentGUI::class):
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_CONTENT);
                    $xvmpGUI = new xudfContentGUI($this);
                    $this->dic->ctrl()->forwardCommand($xvmpGUI);
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                        $this->tpl->show();
                    }
                    break;
                case strtolower(xudfSettingsGUI::class):
                    if (!ilObjUdfEditorAccess::hasWriteAccess()) {
                        ilUtil::sendFailure(self::plugin()->translate('access_denied'), true);
                        $this->dic->ctrl()->returnToParent($this);
                    }
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_SETTINGS);
                    $xvmpGUI = new xudfSettingsGUI($this);
                    $this->dic->ctrl()->forwardCommand($xvmpGUI);
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                        $this->tpl->show();
                    }
                    break;
                case strtolower(xudfFormConfigurationGUI::class):
                    if (!ilObjUdfEditorAccess::hasWriteAccess()) {
                        ilUtil::sendFailure(self::plugin()->translate('access_denied'), true);
                        $this->dic->ctrl()->returnToParent($this);
                    }
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_SETTINGS);
                    $xvmpGUI = new xudfFormConfigurationGUI($this);
                    $this->dic->ctrl()->forwardCommand($xvmpGUI);
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                        $this->tpl->show();
                    }
                    break;
                case strtolower(xudfLogGUI::class):
                    if (!ilObjUdfEditorAccess::hasWriteAccess()) {
                        ilUtil::sendFailure(self::plugin()->translate('access_denied'), true);
                        $this->dic->ctrl()->returnToParent($this);
                    }
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_HISTORY);
                    $xvmpGUI = new xudfLogGUI($this);
                    $this->dic->ctrl()->forwardCommand($xvmpGUI);
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                        $this->tpl->show();
                    }
                    break;
                case strtolower(ilInfoScreenGUI::class):
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_INFO);
                    $this->checkPermission("visible");
                    $this->infoScreen();    // forwards command
                    if (self::version()->is6()) {
                        $this->tpl->printToStdout();
                    } else {
                        $this->tpl->show();
                    }
                    break;
                case strtolower(ilPermissionGUI::class):
                    $this->initHeader(false);
                    parent::executeCommand();
                    break;
                default:
                    // workaround for object deletion; 'parent::executeCommand()' shows the template and leads to "Headers already sent" error
                    if ($next_class == "" && $cmd == 'deleteObject') {
                        $this->deleteObject();
                        break;
                    }
                    parent::executeCommand();
                    break;
            }
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage());
            if (!$this->creation_mode) {
                if (self::version()->is6()) {
                    $this->tpl->printToStdout();
                } else {
                    $this->tpl->show();
                }
            }
        }
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getObject(): ilObjUdfEditor
    {
        return $this->object;
    }

    protected function performCommand($cmd): void
    {
        $this->{$cmd}();
    }

    protected function index(): void
    {
        $this->dic->ctrl()->redirectByClass(xudfContentGUI::class);
    }

    protected function showSettings(): void
    {
        $this->dic->ctrl()->redirectByClass(xudfSettingsGUI::class);
    }

    protected function initHeader($render_locator = true): void
    {
        if ($render_locator) {
            $this->setLocator();
        }
        $this->tpl->setTitleIcon(ilObjUdfEditor::_getIcon($this->object_id));
        $this->tpl->setTitle($this->object->getTitle());
        $this->tpl->setDescription($this->object->getDescription());

        if (!xudfSetting::find($this->obj_id)->isOnline()) {
            /**
             * @var $list_gui ilObjUdfEditorListGUI
             */
            $list_gui = ilObjectListGUIFactory::_getListGUIByType('xudf');
            $this->tpl->setAlertProperties($list_gui->getAlertProperties());
        }
    }

    protected function setTabs(): void
    {
        global $DIC;
        $lng = $DIC->language();

        $this->dic->tabs()->addTab(self::TAB_CONTENT, $this->dic->language()->txt(self::TAB_CONTENT), $this->dic->ctrl()->getLinkTargetByClass(xudfContentGUI::class, xudfContentGUI::CMD_STANDARD));

        if (xudfSetting::find($this->obj_id)->isShowInfoTab()) {
            $this->dic->tabs()->addTab(self::TAB_INFO, $this->dic->language()->txt(self::TAB_INFO . '_short'), $this->dic->ctrl()->getLinkTargetByClass(ilInfoScreenGUI::class));
        }

        if (ilObjUdfEditorAccess::hasWriteAccess()) {
            $this->dic
                ->tabs()
                ->addTab(self::TAB_SETTINGS, $this->dic->language()->txt(self::TAB_SETTINGS), $this->dic->ctrl()->getLinkTargetByClass(xudfSettingsGUI::class, xudfSettingsGUI::CMD_STANDARD));

            $this->dic->tabs()->addTab(self::TAB_HISTORY, $this->dic->language()->txt('history'), $this->dic->ctrl()->getLinkTargetByClass(xudfLogGUI::class, xudfLogGUI::CMD_STANDARD));
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->dic->tabs()->addTab("perm_settings", $lng->txt("perm_settings"), $this->dic->ctrl()->getLinkTargetByClass([
                get_class($this),
                "ilpermissiongui",
            ], "perm"));
        }
    }

    public function addInfoItems(ilInfoScreenGUI $info): void
    {
        $info->addSection(self::plugin()->translate('info_section_title'));
        $fields_string = '';
        foreach (xudfContentElement::where(['obj_id' => $this->getObjId(), 'is_separator' => 0])->get() as $element) {
            /** @var $element xudfContentElement */
            try {
                $fields_string .= $element->getTitle() . '<br>';
            } catch (UDFNotFoundException $e) {
                $this->dic->logger()->root()->alert($e->getMessage());
                $this->dic->logger()->root()->alert($e->getTraceAsString());
            }
        }
        $info->addProperty(self::plugin()->translate('info_section_subtitle'), $fields_string ? $fields_string : '-');
    }

    public function getAfterCreationCmd(): string
    {
        return self::CMD_SETTINGS;
    }

    public function getStandardCmd(): string
    {
        return self::CMD_INDEX;
    }

    public function getType(): string
    {
        return ilUdfEditorPlugin::PLUGIN_ID;
    }

    protected function supportsCloning(): bool
    {
        return false;
    }
}
