<?php

/**
 * Class xudfSettingsFormGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xudfSettingsFormGUI extends ilPropertyFormGUI {

    const F_TITLE = 'title';
    const F_DESCRIPTION = 'description';
    const F_ONLINE = 'online';
    const F_SHOW_INFOTAB = 'show_infotab';
    const F_MAIL_NOTIFICATION = 'mail_notification';
    const F_ADDITIONAL_NOTIFICATION = 'additional_notification';

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilUdfEditorPlugin
     */
    protected $pl;
    /**
     * @var xudfSettingsGUI
     */
    protected $parent_gui;
	/**
	 * @var xudfSetting
	 */
    protected $settings;

    /**
     * xudfSettingsFormGUI constructor.
     * @param xudfSettingsGUI $parent_gui
     */
    public function __construct(xudfSettingsGUI $parent_gui) {
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->pl = ilUdfEditorPlugin::getInstance();
        $this->parent_gui = $parent_gui;
        $this->settings = xudfSetting::find($this->parent_gui->getObjId());
        $this->setTitle($this->lng->txt('settings'));
        $this->setFormAction($this->ctrl->getFormAction($parent_gui));
        $this->initForm();
    }

    /**
     *
     */
    protected function initForm() {
        // TITLE
        $input = new ilTextInputGUI($this->lng->txt(self::F_TITLE), self::F_TITLE);
        $input->setRequired(true);
        $this->addItem($input);

        // DESCRIPTION
        $input = new ilTextInputGUI($this->lng->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($input);

        // ONLINE
        $input = new ilCheckboxInputGUI($this->lng->txt(self::F_ONLINE), self::F_ONLINE);
        $this->addItem($input);

        // SHOW INFOTAB
        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_SHOW_INFOTAB), self::F_SHOW_INFOTAB);
        $this->addItem($input);

        // MAIL NOTIFICATION
        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_MAIL_NOTIFICATION), self::F_MAIL_NOTIFICATION);
        $input->setInfo($this->pl->txt(self::F_MAIL_NOTIFICATION . '_info'));
        $this->addItem($input);

        // MAIL NOTIFICATION
        $input = new ilTextInputGUI($this->pl->txt(self::F_ADDITIONAL_NOTIFICATION), self::F_ADDITIONAL_NOTIFICATION);
        $input->setInfo($this->pl->txt(self::F_ADDITIONAL_NOTIFICATION . '_info'));
        $this->addItem($input);

        $this->addCommandButton(xudfSettingsGUI::CMD_UPDATE, $this->lng->txt('save'));
    }

    /**
     *
     */
    public function fillForm() {
        $values = array(
            self::F_TITLE => $this->parent_gui->getObject()->getTitle(),
            self::F_DESCRIPTION => $this->parent_gui->getObject()->getDescription(),
            self::F_ONLINE => $this->settings->isOnline(),
            self::F_SHOW_INFOTAB => $this->settings->isShowInfoTab(),
            self::F_MAIL_NOTIFICATION => $this->settings->hasMailNotification(),
            self::F_ADDITIONAL_NOTIFICATION => $this->settings->getAdditionalNotification(),
        );
        $this->setValuesByArray($values);
    }


    /**
     * @return bool
     */
    public function saveForm() {
        if (!$this->checkInput()) {
            return false;
        }

        $this->parent_gui->getObject()->setTitle($this->getInput(self::F_TITLE));
        $this->parent_gui->getObject()->setDescription($this->getInput(self::F_DESCRIPTION));
        $this->parent_gui->getObject()->update();

        $this->settings->setIsOnline($this->getInput(self::F_ONLINE));
        $this->settings->setShowInfoTab($this->getInput(self::F_SHOW_INFOTAB));
        $this->settings->setMailNotification($this->getInput(self::F_MAIL_NOTIFICATION));
        $this->settings->setAdditionalNotification($this->getInput(self::F_ADDITIONAL_NOTIFICATION));
        $this->settings->update();

        return true;
    }
}