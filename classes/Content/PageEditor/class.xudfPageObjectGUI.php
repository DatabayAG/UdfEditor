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

/**
 * @ilCtrl_isCalledBy xudfPageObjectGUI: xudfContentGUI
 * @ilCtrl_Calls      xudfPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls      xudfPageObjectGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 */
class xudfPageObjectGUI extends ilPageObjectGUI
{
    public function __construct(xudfContentGUI $parent_gui)
    {
        $this->checkAndAddCOPageDefinition();

        // we always need a page object - create on demand
        if (!xudfPageObject::_exists(ilUdfEditorPlugin::ID, $parent_gui->getObjId())) {
            $page_obj = new xudfPageObject();
            $page_obj->setId($parent_gui->getObjId());
            $page_obj->setParentId($parent_gui->getObjId());
            $page_obj->create();
        }

        parent::__construct(ilUdfEditorPlugin::ID, $parent_gui->getObjId());

        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($parent_gui->getObject()->getStyleSheetId())
        );
        $tpl->parseCurrentBlock();
    }

    /**
     * for some reason the entry in copg_pobj_def gets deleted from time to time, so we check and add it everytime now
     */
    protected function checkAndAddCOPageDefinition(): void
    {
        global $DIC;
        $sql_query = $DIC->database()->queryF(
            "SELECT * FROM copg_pobj_def WHERE parent_type = %s",
            [ilDBConstants::T_TEXT],
            [ilUdfEditorPlugin::ID]
        );
        if ($DIC->database()->numRows($sql_query) === 0) {
            $DIC->database()->insert("copg_pobj_def", [
                "parent_type" => [ilDBConstants::T_TEXT, ilUdfEditorPlugin::ID],
                "class_name" => [ilDBConstants::T_TEXT, "xudfPageObject"],
                "directory" => [ilDBConstants::T_TEXT, "classes/Content/PageEditor"],
                "component" => [ilDBConstants::T_TEXT, "Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor"]
            ]);
        }
    }
}
