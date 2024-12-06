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
        if (!xudfPageObject::_exists(xudfPageObject::PARENT_TYPE, $parent_gui->getObjId())) {
            $page_obj = new xudfPageObject();
            $page_obj->setId($parent_gui->getObjId());
            $page_obj->setParentId($parent_gui->getObjId());
            $page_obj->create();
        }

        parent::__construct(xudfPageObject::PARENT_TYPE, $parent_gui->getObjId());

        // content style
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");

        global $DIC;
        $tpl = $DIC->ui()->mainTemplate();
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("ContentStyle");
        $style_id = $parent_gui->getObject()->getStyleSheetId();
        if ($style_id == null) {
            $style_id = 0;
        }
        $tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($style_id))
        );
        $tpl->parseCurrentBlock();
    }

    /**
     * for some reason the entry in copg_pobj_def gets deleted from time to time, so we check and add it everytime now
     */
    protected function checkAndAddCOPageDefinition(): void
    {
        global $DIC;
        $sql_query = $DIC->database()->query('SELECT * FROM copg_pobj_def WHERE parent_type = "xudf"');
        if ($DIC->database()->numRows($sql_query) === 0) {
            $DIC->database()->insert('copg_pobj_def', [
                'parent_type' => ['text', 'xudf'],
                'class_name' => ['text', 'xudfPageObject'],
                'directory' => ['text', 'classes/Content/PageEditor'],
                'component' => ['text', 'Customizing/global/plugins/Services/Repository/RepositoryObject/UdfEditor']
            ]);
        }
    }
}
