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
 * @ilCtrl_Calls      xudfSettingsFormGUI: ilFormPropertyDispatchGUI
 */
class xudfSettingsFormGUI extends ilPropertyFormGUI
{
    public const F_TITLE = 'title';
    public const F_DESCRIPTION = 'description';
    public const F_ONLINE = 'online';
    public const F_SHOW_INFOTAB = 'show_infotab';
    public const F_ALWAYS_EDIT = 'always_edit';
    public const F_MAIL_NOTIFICATION = 'mail_notification';
    public const F_ADDITIONAL_NOTIFICATION = 'additional_notification';
    public const F_REDIRECT_TYPE = 'redirect_type';
    public const F_REF_ID = 'ref_id';
    public const F_URL = 'url';

    protected static array $redirect_type_to_postvar
        = [
            xudfSetting::REDIRECT_STAY_IN_FORM => false,
            xudfSetting::REDIRECT_TO_ILIAS_OBJECT => self::F_REF_ID,
            xudfSetting::REDIRECT_TO_URL => self::F_URL
        ];

    protected ilCtrl $ctrl;

    protected ilLanguage $lng;

    protected ilUdfEditorPlugin $pl;

    protected xudfSettingsGUI $parent_gui;

    protected xudfSetting $xudfSetting;

    public function __construct(xudfSettingsGUI $parent_gui)
    {
        parent::__construct();
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->pl = ilUdfEditorPlugin::getInstance();
        $this->parent_gui = $parent_gui;
        $this->xudfSetting = xudfSetting::find($this->parent_gui->getObjId());
        $this->setTitle($this->lng->txt('settings'));
        $this->setFormAction($this->ctrl->getFormAction($parent_gui));
        $this->initForm();
    }

    protected function initForm(): void
    {
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

        // Configure Edit Mode
        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_ALWAYS_EDIT), self::F_ALWAYS_EDIT);
        $input->setInfo($this->pl->txt(self::F_ALWAYS_EDIT . '_info'));
        $this->addItem($input);

        // MAIL NOTIFICATION
        $input = new ilCheckboxInputGUI($this->pl->txt(self::F_MAIL_NOTIFICATION), self::F_MAIL_NOTIFICATION);
        $input->setInfo($this->pl->txt(self::F_MAIL_NOTIFICATION . '_info'));
        $this->addItem($input);

        // MAIL NOTIFICATION
        $input = new ilTextInputGUI($this->pl->txt(self::F_ADDITIONAL_NOTIFICATION), self::F_ADDITIONAL_NOTIFICATION);
        $input->setInfo($this->pl->txt(self::F_ADDITIONAL_NOTIFICATION . '_info'));
        $this->addItem($input);

        // REDIRECT TYPE
        $input = new ilRadioGroupInputGUI($this->pl->txt(self::F_REDIRECT_TYPE), self::F_REDIRECT_TYPE);
        $input->setInfo($this->pl->txt(self::F_REDIRECT_TYPE . '_info'));

        $opt = new ilRadioOption($this->pl->txt(xudfSetting::REDIRECT_STAY_IN_FORM), xudfSetting::REDIRECT_STAY_IN_FORM);
        $input->addOption($opt);

        $opt = new ilRadioOption($this->pl->txt(xudfSetting::REDIRECT_TO_ILIAS_OBJECT), xudfSetting::REDIRECT_TO_ILIAS_OBJECT);
        $obj_input = new ilRepositorySelector2InputGUI('', self::F_REF_ID, false, $this);
        $opt->addSubItem($obj_input);
        $input->addOption($opt);

        $opt = new ilRadioOption($this->pl->txt(xudfSetting::REDIRECT_TO_URL), xudfSetting::REDIRECT_TO_URL);
        $url_input = new ilTextInputGUI('', self::F_URL);
        $opt->addSubItem($url_input);
        $input->addOption($opt);

        $this->addItem($input);

        $this->addCommandButton(xudfSettingsGUI::CMD_UPDATE, $this->lng->txt('save'));
    }

    public function fillForm(): void
    {
        $values = [
            self::F_TITLE => $this->parent_gui->getObject()->getTitle(),
            self::F_DESCRIPTION => $this->parent_gui->getObject()->getDescription(),
            self::F_ONLINE => $this->xudfSetting->isOnline(),
            self::F_SHOW_INFOTAB => $this->xudfSetting->isShowInfoTab(),
            self::F_ALWAYS_EDIT => $this->xudfSetting->getAlwaysEdit(),
            self::F_MAIL_NOTIFICATION => $this->xudfSetting->hasMailNotification(),
            self::F_ADDITIONAL_NOTIFICATION => $this->xudfSetting->getAdditionalNotification(),
            self::F_REDIRECT_TYPE => $this->xudfSetting->getRedirectType()
        ];
        $redirect_value_postvar = self::$redirect_type_to_postvar[$this->xudfSetting->getRedirectType()];
        if ($redirect_value_postvar !== false) {
            $values[$redirect_value_postvar] = $this->xudfSetting->getRedirectValue();
        }

        $this->setValuesByArray($values);
    }

    public function saveForm(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $this->parent_gui->getObject()->setTitle($this->getInput(self::F_TITLE));
        $this->parent_gui->getObject()->setDescription($this->getInput(self::F_DESCRIPTION));
        $this->parent_gui->getObject()->update();

        $this->xudfSetting->setIsOnline((bool) $this->getInput(self::F_ONLINE));
        $this->xudfSetting->setShowInfoTab((bool) $this->getInput(self::F_SHOW_INFOTAB));
        $this->xudfSetting->setAlwaysEdit($this->getInput(self::F_ALWAYS_EDIT));
        $this->xudfSetting->setMailNotification((bool) $this->getInput(self::F_MAIL_NOTIFICATION));
        $this->xudfSetting->setAdditionalNotification($this->getInput(self::F_ADDITIONAL_NOTIFICATION));
        $this->xudfSetting->setRedirectType($this->getInput(self::F_REDIRECT_TYPE));
        switch ($this->xudfSetting->getRedirectType()) {
            case xudfSetting::REDIRECT_TO_ILIAS_OBJECT:
                $this->xudfSetting->setRedirectValue($this->getInput(self::F_REF_ID));
                break;
            case xudfSetting::REDIRECT_TO_URL:
                $this->xudfSetting->setRedirectValue($this->getInput(self::F_URL));
                break;
            default:
                break;
        }
        $this->xudfSetting->update();

        return true;
    }
}
