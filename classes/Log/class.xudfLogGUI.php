<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

declare(strict_types=1);

/**
 * @ilCtrl_isCalledBy xudfLogGUI: ilObjUdfEditorGUI
 */
class xudfLogGUI extends xudfGUI
{
    protected function index(): void
    {
        $table = new xudfLogTableGUI($this, self::CMD_STANDARD);
        $this->tpl->setContent($table->getHTML());
    }

    protected function applyFilter(): void
    {
        $table = new xudfLogTableGUI($this, self::CMD_STANDARD);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->index();
    }

    protected function resetFilter(): void
    {
        $table = new xudfLogTableGUI($this, self::CMD_STANDARD);
        $table->resetFilter();
        $table->resetOffset();
        $this->index();
    }
}
