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
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationCtrl;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationsCtrl;

abstract class xudfGUI
{
    public const CMD_STANDARD = 'index';

    protected ilCtrlInterface $ctrl;

    protected ilObjUser $user;

    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    protected ilTabsGUI $tabs;

    protected ilToolbarGUI $toolbar;

    protected ilUdfEditorPlugin $pl;

    protected ilObjUdfEditorGUI $parent_gui;
    protected Container $dic;
    protected ilTree $tree;
    protected WrapperFactory $httpWrapper;
    protected Factory $refinery;

    public function __construct(ilObjUdfEditorGUI $parent_gui)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->tree = $DIC->repositoryTree();
        $this->pl = ilUdfEditorPlugin::getInstance();
        $this->parent_gui = $parent_gui;
        $this->httpWrapper = $this->dic->http()->wrapper();
        $this->refinery = $this->dic->refinery();
    }

    public function executeCommand(): void
    {
        $this->setSubtabs();
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_STANDARD);
                $this->performCommand($cmd);
                break;
        }
    }

    /** @noinspection PhpStatementHasEmptyBodyInspection */
    protected function performCommand(string $cmd): void
    {
        if ((new NotificationCtrl($this))->handleCommand($cmd)) {
            //Do nothing special
        } elseif ((new NotificationsCtrl())->handleCommand($cmd)) {
            //Do nothing special
        } else {
            $this->{$cmd}();
        }
    }

    protected function setSubtabs(): void
    {
        // overwrite if class has subtabs
    }

    public function getObjId(): int
    {
        return $this->parent_gui->getObjId();
    }

    public function getObject(): ilObjUdfEditor
    {
        return $this->parent_gui->getObject();
    }

    abstract protected function index();
}
