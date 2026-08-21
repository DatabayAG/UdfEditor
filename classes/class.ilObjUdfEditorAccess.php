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

require_once __DIR__ . "/../vendor/autoload.php";

class ilObjUdfEditorAccess extends ilObjectPluginAccess
{
    protected static ?ilObjUdfEditorAccess $instance = null;

    public static function getInstance(): ilObjUdfEditorAccess
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected ilAccessHandler $access;

    protected ilObjUser $usr;

    public function __construct()
    {
        parent::__construct();
        global $DIC;

        $this->access = $DIC->access();
        $this->usr = $DIC->user();
    }

    public function _checkAccess(string $cmd, string $permission, ?int $ref_id = null, ?int $obj_id = null, ?int $user_id = null): bool
    {
        if ($ref_id === null) {
            $ref_id = (int) filter_input(INPUT_GET, "ref_id");
        }

        if ($obj_id === null) {
            $obj_id = ilObjUdfEditor::_lookupObjectId($ref_id);
        }

        if ($user_id === null) {
            $user_id = $this->usr->getId();
        }

        return match ($permission) {
            "visible", "read" => ($this->access->checkAccessOfUser($user_id, $permission, "", $ref_id) && !self::_isOffline($obj_id))
                || $this->access->checkAccessOfUser($user_id, "write", "", $ref_id),
            "delete" => $this->access->checkAccessOfUser($user_id, "delete", "", $ref_id)
                || $this->access->checkAccessOfUser($user_id, "write", "", $ref_id),
            default => $this->access->checkAccessOfUser($user_id, $permission, "", $ref_id),
        };
    }

    protected static function checkAccess(string $cmd, string $permission, ?int $ref_id = null, ?int $obj_id = null, ?int $user_id = null): bool
    {
        return self::getInstance()->_checkAccess($cmd, $permission, $ref_id, $obj_id, $user_id);
    }

    public static function redirectNonAccess(string $class, string $cmd = ""): void
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $DIC->ui()->mainTemplate()->setOnScreenMessage("failure", $DIC->language()->txt("permission_denied"), true);

        if (is_object($class)) {
            $ctrl->clearParameters($class);
            $ctrl->redirect($class, $cmd);
        } else {
            $ctrl->clearParametersByClass($class);
            $ctrl->redirectByClass($class, $cmd);
        }
    }

    public static function hasVisibleAccess(?int $ref_id = null): bool
    {
        return self::checkAccess("visible", "visible", $ref_id);
    }

    public static function hasReadAccess(?int $ref_id = null): bool
    {
        return self::checkAccess("read", "read", $ref_id);
    }

    public static function hasWriteAccess(?int $ref_id = null): bool
    {
        return self::checkAccess("write", "write", $ref_id);
    }

    public static function hasDeleteAccess(?int $ref_id = null): bool
    {
        return self::checkAccess("delete", "delete", $ref_id);
    }

    public static function hasEditPermissionAccess(?int $ref_id = null): bool
    {
        return self::checkAccess("edit_permission", "edit_permission", $ref_id);
    }
}
