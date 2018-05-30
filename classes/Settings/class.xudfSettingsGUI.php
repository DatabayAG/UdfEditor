<?php

/**
 * Class xudfSettingsGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xudfSettingsGUI extends xudfGUI {

    const SUBTAB_SETTINGS = 'settings';
    const SUBTAB_FORM_CONFIGURATION = 'form_configuration';

    const CMD_FORM_CONFIGURATION = 'showFormConfiguration';

    protected function setSubtabs() {
        $this->tabs->addSubTab(self::SUBTAB_SETTINGS, $this->lng->txt(self::SUBTAB_SETTINGS), $this->ctrl->getLinkTarget($this, self::CMD_STANDARD));
        $this->tabs->addSubTab(self::SUBTAB_FORM_CONFIGURATION, $this->lng->txt(self::SUBTAB_FORM_CONFIGURATION), $this->ctrl->getLinkTargetByClass(xudfFormConfigurationGUI::class));
        $this->tabs->setSubTabActive(self::SUBTAB_FORM_CONFIGURATION);
    }


    protected function index() {

    }



}