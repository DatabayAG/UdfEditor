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

    public function _checkAccess(string $a_cmd, string $a_permission, ?int $a_ref_id = null, ?int $a_obj_id = null, ?int $a_user_id = null): bool
    {
        if ($a_ref_id === null) {
            $a_ref_id = (int) filter_input(INPUT_GET, "ref_id");
        }

        if ($a_obj_id === null) {
            $a_obj_id = ilObjUdfEditor::_lookupObjectId($a_ref_id);
        }

        if ($a_user_id == null) {
            $a_user_id = $this->usr->getId();
        }

        switch ($a_permission) {
            case "visible":
            case "read":
                return (($this->access->checkAccessOfUser($a_user_id, $a_permission, "", $a_ref_id) && !self::_isOffline($a_obj_id))
                    || $this->access->checkAccessOfUser($a_user_id, "write", "", $a_ref_id));

            case "delete":
                return ($this->access->checkAccessOfUser($a_user_id, "delete", "", $a_ref_id)
                    || $this->access->checkAccessOfUser($a_user_id, "write", "", $a_ref_id));

            case "write":
            case "edit_permission":
            default:
                return $this->access->checkAccessOfUser($a_user_id, $a_permission, "", $a_ref_id);
        }
    }

    protected static function checkAccess(string $a_cmd, string $a_permission, ?int $a_ref_id = null, ?int $a_obj_id = null, ?int $a_user_id = null): bool
    {
        return self::getInstance()->_checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id);
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
