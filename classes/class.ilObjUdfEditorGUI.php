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
use ILIAS\Plugin\UdfEditor\Repository\ContentElementRepository;
use ILIAS\Plugin\UdfEditor\Repository\SettingsRepository;
use ILIAS\Plugin\UdfEditor\Utils\UiUtil;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * @ilCtrl_isCalledBy ilObjUdfEditorGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjUdfEditorGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilEditClipboardGUI
 */
class ilObjUdfEditorGUI extends ilObjectPluginGUI
{
    public const string TAB_CONTENT = "content";
    public const string TAB_INFO = "info";
    public const string TAB_SETTINGS = "settings";
    public const string TAB_HISTORY = "log_history";
    public const string TAB_PERMISSIONS = "permissions";
    public const string CMD_INDEX = "index";
    public const string CMD_SETTINGS = "showSettings";

    private readonly Container $dic;
    protected ilUdfEditorPlugin|ilPlugin|null $plugin = null;
    private ContentElementRepository $content_element_repo;
    private SettingsRepository $settings_repo;
    private UiUtil $ui_util;

    public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        global $DIC;
        $this->dic = $DIC;
        $this->ui_util = new UiUtil();

        $server_params = $this->request->getServerParams();

        if (isset($server_params["HTTP_REFERER"])) {
            $rref = 0;
            $a_referer = explode("&", $server_params["HTTP_REFERER"]);
            if (count($a_referer)) {
                foreach ($a_referer as $entry) {
                    $a_entry = explode("=", $entry);
                    if ($a_entry[0] === "ref_id" && isset($a_entry[1])) {
                        $rref = $a_entry[1];
                    }
                }
            }
            if ($rref != $this->ref_id && $rref != 0) {
                ilSession::set("xudfreturn", $server_params["HTTP_REFERER"]);
            }
        }

        $this->content_element_repo = new ContentElementRepository();
        $this->settings_repo = new SettingsRepository();
    }

    public function executeCommand(): void
    {
        $next_class = $this->dic->ctrl()->getNextClass();
        $cmd = $this->dic->ctrl()->getCmd();
        if (!ilObjUdfEditorAccess::hasReadAccess() && $next_class != strtolower(ilInfoScreenGUI::class) && $cmd !== "infoScreen") {
            $this->ui_util->sendFailure($this->plugin->txt("access_denied"));
            $this->dic->ctrl()->returnToParent($this);
        }
        $this->tpl->loadStandardTemplate();

        try {
            switch ($next_class) {
                case strtolower(xudfContentGUI::class):
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_CONTENT);
                    $xvmp_gui = new xudfContentGUI($this);
                    $this->dic->ctrl()->forwardCommand($xvmp_gui);
                    $this->tpl->printToStdout();
                    break;
                case strtolower(xudfSettingsGUI::class):
                    if (!ilObjUdfEditorAccess::hasWriteAccess()) {
                        $this->ui_util->sendFailure($this->plugin->txt("access_denied"));
                        $this->dic->ctrl()->returnToParent($this);
                    }
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_SETTINGS);
                    $xvmp_gui = new xudfSettingsGUI($this);
                    $this->dic->ctrl()->forwardCommand($xvmp_gui);
                    $this->tpl->printToStdout();
                    break;
                case strtolower(xudfFormConfigurationGUI::class):
                    if (!ilObjUdfEditorAccess::hasWriteAccess()) {
                        $this->ui_util->sendFailure($this->plugin->txt("access_denied"));
                        $this->dic->ctrl()->returnToParent($this);
                    }
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_SETTINGS);
                    $xvmp_gui = new xudfFormConfigurationGUI($this);
                    $this->dic->ctrl()->forwardCommand($xvmp_gui);
                    $this->tpl->printToStdout();
                    break;
                case strtolower(xudfLogGUI::class):
                    if (!ilObjUdfEditorAccess::hasWriteAccess()) {
                        $this->ui_util->sendFailure($this->plugin->txt("access_denied"));
                        $this->dic->ctrl()->returnToParent($this);
                    }
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_HISTORY);
                    $xvmp_gui = new xudfLogGUI($this);
                    $this->dic->ctrl()->forwardCommand($xvmp_gui);
                    $this->tpl->printToStdout();
                    break;
                case strtolower(ilInfoScreenGUI::class):
                    if (!$this->dic->ctrl()->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->dic->tabs()->activateTab(self::TAB_INFO);
                    $this->checkPermission("visible");
                    $this->infoScreen();    // forwards command
                    $this->tpl->printToStdout();
                    break;
                case strtolower(ilPermissionGUI::class):
                    $this->initHeader(false);
                    parent::executeCommand();
                    break;
                default:
                    // workaround for object deletion; 'parent::executeCommand()' shows the template and leads to "Headers already sent" error
                    if ($next_class == "" && $cmd === "deleteObject") {
                        $this->deleteObject();
                        break;
                    }
                    parent::executeCommand();
                    break;
            }
        } catch (Exception $e) {
            $this->ui_util->sendFailure($e->getMessage());
            if (!$this->creation_mode) {
                $this->tpl->printToStdout();
            }
        }
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function performCommand(string $cmd): void
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
        $this->tpl->setTitleIcon(ilUdfEditorPlugin::_getIcon(ilUdfEditorPlugin::ID));
        $this->tpl->setTitle($this->object->getTitle());
        $this->tpl->setDescription($this->object->getDescription());

        if (!$this->settings_repo->read($this->obj_id)?->isOnline()) {
            /**
             * @var $list_gui ilObjUdfEditorListGUI
             */
            $list_gui = ilObjectListGUIFactory::_getListGUIByType("xudf");
            $this->tpl->setAlertProperties($list_gui->getAlertProperties());
        }
    }

    protected function setTabs(): void
    {
        global $DIC;
        $lng = $DIC->language();

        $this->dic->tabs()->addTab(self::TAB_CONTENT, $this->dic->language()->txt(self::TAB_CONTENT), $this->dic->ctrl()->getLinkTargetByClass(xudfContentGUI::class, xudfContentGUI::CMD_STANDARD));

        if ($this->settings_repo->read($this->obj_id)?->isShowInfoTab()) {
            $this->dic->tabs()->addTab(self::TAB_INFO, $this->dic->language()->txt(self::TAB_INFO . "_short"), $this->dic->ctrl()->getLinkTargetByClass(ilInfoScreenGUI::class));
        }

        if (ilObjUdfEditorAccess::hasWriteAccess()) {
            $this->dic
                ->tabs()
                ->addTab(self::TAB_SETTINGS, $this->dic->language()->txt(self::TAB_SETTINGS), $this->dic->ctrl()->getLinkTargetByClass(xudfSettingsGUI::class, xudfSettingsGUI::CMD_STANDARD));

            $this->dic->tabs()->addTab(self::TAB_HISTORY, $this->dic->language()->txt("history"), $this->dic->ctrl()->getLinkTargetByClass(xudfLogGUI::class, xudfLogGUI::CMD_STANDARD));
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->dic->tabs()->addTab("perm_settings", $lng->txt("perm_settings"), $this->dic->ctrl()->getLinkTargetByClass([
                static::class,
                "ilpermissiongui",
            ], "perm"));
        }
    }

    public function addInfoItems(ilInfoScreenGUI $info): void
    {
        $info->addSection($this->plugin->txt("info_section_title"));
        $fields_string = "";
        foreach ($this->content_element_repo->readAllByObjId($this->getObjId()) as $element) {
            $fields_string .= $element->getTitle() . "<br>";
        }
        $info->addProperty($this->plugin->txt("info_section_subtitle"), $fields_string ?: "-");
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
        return ilUdfEditorPlugin::ID;
    }

    protected function supportsCloning(): bool
    {
        return false;
    }
}
