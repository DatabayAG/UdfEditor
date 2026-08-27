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

use ILIAS\Plugin\UdfEditor\Enum\RedirectType;
use ILIAS\Plugin\UdfEditor\Repository\SettingsRepository;
use ILIAS\Plugin\UdfEditor\Model\Settings;

/**
 * @ilCtrl_Calls      xudfSettingsFormGUI: ilFormPropertyDispatchGUI
 */
class xudfSettingsFormGUI extends ilPropertyFormGUI
{
    public const string F_TITLE = 'title';
    public const string F_DESCRIPTION = 'description';
    public const string F_ONLINE = 'online';
    public const string F_SHOW_INFOTAB = 'show_infotab';
    public const string F_ALWAYS_EDIT = 'always_edit';
    public const string F_MAIL_NOTIFICATION = 'mail_notification';
    public const string F_ADDITIONAL_NOTIFICATION = 'additional_notification';
    public const string F_REDIRECT_TYPE = 'redirect_type';
    public const string F_REF_ID = 'ref_id_redir';
    public const string F_URL = 'url';

    protected static array $redirect_type_to_postvar
        = [
            RedirectType::STAY_IN_FORM->value => false,
            RedirectType::TO_ILIAS_OBJECT->value => self::F_REF_ID,
            RedirectType::TO_URL->value => self::F_URL,
            RedirectType::TO_CALLER->value => false
        ];

    protected ilLanguage $lng;

    protected ilUdfEditorPlugin $pl;

    protected Settings $xudfSetting;
    private SettingsRepository $settings_repo;

    public function __construct(protected xudfSettingsGUI $parent_gui)
    {
        parent::__construct();
        global $DIC;
        $this->lng = $DIC->language();
        $this->pl = ilUdfEditorPlugin::getInstance();
        $this->settings_repo = new SettingsRepository();

        $this->xudfSetting = $this->settings_repo->read($this->parent_gui->getObjId());
        $this->setTitle($this->lng->txt('settings'));
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->initForm();
    }

    protected function initForm(): void
    {
        // TITLE
        $title = new ilTextInputGUI($this->lng->txt(self::F_TITLE), self::F_TITLE);
        $title->setRequired(true);
        $this->addItem($title);

        // DESCRIPTION
        $description = new ilTextInputGUI($this->lng->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($description);

        // ONLINE
        $online = new ilCheckboxInputGUI($this->lng->txt(self::F_ONLINE), self::F_ONLINE);
        $this->addItem($online);

        // SHOW INFOTAB
        $show_info_tab = new ilCheckboxInputGUI($this->pl->txt(self::F_SHOW_INFOTAB), self::F_SHOW_INFOTAB);
        $this->addItem($show_info_tab);

        // Configure Edit Mode
        $edit_mode = new ilCheckboxInputGUI($this->pl->txt(self::F_ALWAYS_EDIT), self::F_ALWAYS_EDIT);
        $edit_mode->setInfo($this->pl->txt(self::F_ALWAYS_EDIT . '_info'));
        $this->addItem($edit_mode);

        // MAIL NOTIFICATION
        $mail_notification = new ilCheckboxInputGUI($this->pl->txt(self::F_MAIL_NOTIFICATION), self::F_MAIL_NOTIFICATION);
        $mail_notification->setInfo($this->pl->txt(self::F_MAIL_NOTIFICATION . '_info'));
        $this->addItem($mail_notification);

        // ADDITIONAL MAIL NOTIFICATION
        $additional_mail_notification = new ilTextInputGUI($this->pl->txt(self::F_ADDITIONAL_NOTIFICATION), self::F_ADDITIONAL_NOTIFICATION);
        $additional_mail_notification->setInfo($this->pl->txt(self::F_ADDITIONAL_NOTIFICATION . '_info'));
        $additional_mail_notification->setRequired(true);
        $mail_notification->addSubItem($additional_mail_notification);

        // REDIRECT TYPE
        $redirect_type = new ilRadioGroupInputGUI($this->pl->txt(self::F_REDIRECT_TYPE), self::F_REDIRECT_TYPE);
        $redirect_type->setInfo($this->pl->txt(self::F_REDIRECT_TYPE . '_info'));

        $redirect_type->addOption(new ilRadioOption($this->pl->txt(RedirectType::STAY_IN_FORM->toTranslationKey()), RedirectType::STAY_IN_FORM->value));

        $to_ilias_object_option = new ilRadioOption($this->pl->txt(RedirectType::TO_ILIAS_OBJECT->toTranslationKey()), RedirectType::TO_ILIAS_OBJECT->value);
        $obj_input = new ilRepositorySelector2InputGUI('', self::F_REF_ID, false, $this);
        $obj_input->setRequired(true);
        $to_ilias_object_option->addSubItem($obj_input);
        $redirect_type->addOption($to_ilias_object_option);

        $to_url_option = new ilRadioOption($this->pl->txt(RedirectType::TO_URL->toTranslationKey()), RedirectType::TO_URL->value);
        $url_input = new ilTextInputGUI('', self::F_URL);
        $url_input->setRequired(true);
        $to_url_option->addSubItem($url_input);
        $redirect_type->addOption($to_url_option);


        // only offer redirect to caller if referer contains a ref_id
        // since some proxy scenarios do not pass the complete referer
        $serverParams = $this->http->request()->getServerParams();

        if (isset($serverParams['HTTP_REFERER']) && str_contains($serverParams['HTTP_REFERER'], 'ref_id')) {
            $redirect_type->addOption(new ilRadioOption($this->pl->txt(RedirectType::TO_CALLER->toTranslationKey()), RedirectType::TO_CALLER->value));
        }

        $this->addItem($redirect_type);

        $this->addCommandButton(xudfSettingsGUI::CMD_UPDATE, $this->lng->txt('save'));
    }

    public function fillForm(): void
    {
        $values = [
            self::F_TITLE => $this->parent_gui->getObject()->getTitle(),
            self::F_DESCRIPTION => $this->parent_gui->getObject()->getDescription(),
            self::F_ONLINE => $this->xudfSetting->isOnline(),
            self::F_SHOW_INFOTAB => $this->xudfSetting->isShowInfoTab(),
            self::F_ALWAYS_EDIT => $this->xudfSetting->isAlwaysEdit(),
            self::F_MAIL_NOTIFICATION => $this->xudfSetting->isMailNotification(),
            self::F_ADDITIONAL_NOTIFICATION => $this->xudfSetting->getAdditionalNotification(),
            self::F_REDIRECT_TYPE => $this->xudfSetting->getRedirectType()->value
        ];
        $redirect_value_postvar = self::$redirect_type_to_postvar[$this->xudfSetting->getRedirectType()->value];
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
        $this->xudfSetting->setAlwaysEdit((bool) $this->getInput(self::F_ALWAYS_EDIT));
        $this->xudfSetting->setMailNotification((bool) $this->getInput(self::F_MAIL_NOTIFICATION));
        $this->xudfSetting->setAdditionalNotification($this->getInput(self::F_ADDITIONAL_NOTIFICATION));
        $this->xudfSetting->setRedirectType(RedirectType::tryFrom($this->getInput(self::F_REDIRECT_TYPE)) ?? RedirectType::STAY_IN_FORM);
        switch ($this->xudfSetting->getRedirectType()) {
            case RedirectType::TO_ILIAS_OBJECT:
                $this->xudfSetting->setRedirectValue($this->getInput(self::F_REF_ID));
                break;
            case RedirectType::TO_URL:
                $this->xudfSetting->setRedirectValue($this->getInput(self::F_URL));
                break;
            default:
                break;
        }
        $this->settings_repo->update($this->xudfSetting);

        return true;
    }
}
