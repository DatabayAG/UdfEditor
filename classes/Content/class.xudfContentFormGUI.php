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
use ILIAS\Plugin\UdfEditor\Exception\UnknownUdfTypeException;
use ILIAS\Plugin\UdfEditor\Model\LogEntry;
use ILIAS\Plugin\UdfEditor\Repository\ContentElementRepository;
use ILIAS\Plugin\UdfEditor\Repository\LogEntryRepository;
use ILIAS\Plugin\UdfEditor\Utils\UiUtil;
use ILIAS\User\Context;
use ILIAS\User\Profile\Profile;

class xudfContentFormGUI extends ilPropertyFormGUI
{
    protected int $obj_id;
    private readonly Container $dic;
    private ContentElementRepository $content_element_repo;
    private LogEntryRepository $log_entry_repo;
    private UiUtil $ui_util;
    private Profile $user_profile;
    private ilUdfEditorPlugin $plugin;

    /**
     * @throws UnknownUdfTypeException|ilCtrlException
     */
    public function __construct(protected xudfContentGUI $parent_gui, bool $editable = true)
    {
        parent::__construct();
        $this->obj_id = $this->parent_gui->getObjId();
        global $DIC;
        $this->dic = $DIC;
        $this->ui_util = new UiUtil();
        $this->plugin = ilUdfEditorPlugin::getInstance();

        $this->content_element_repo = new ContentElementRepository();
        $this->log_entry_repo = new LogEntryRepository();

        $this->user_profile = $this->dic["user"]->getProfile();

        $this->setFormAction($this->dic->ctrl()->getFormAction($this->parent_gui));
        $this->initForm($editable);
    }

    /**
     * @throws arException
     * @throws UnknownUdfTypeException
     */
    protected function initForm($editable): void
    {
        foreach ($this->content_element_repo->readAllByObjId($this->obj_id, true, true) as $element) {
            if ($element->isSeparator()) {
                $input = new ilFormSectionHeaderGUI();
                $input->setTitle($element->getTitle());
                $input->setInfo($element->getDescription());
                $this->addItem($input);
            } else {
                $field = $element->getUserDefinedField();
                if (!$field) {
                    continue;
                }

                $input = $field->getLegacyInput($this->lng, Context::User);
                $input->setInfo($element->getDescription());
                $input->setRequired($element->isRequired());
                $input->setDisabled(!$editable);
                $this->addItem($input);
            }
        }

        if ($editable) {
            $this->addCommandButton(xudfSettingsGUI::CMD_UPDATE, $this->dic->language()->txt('save'));
        }
    }

    public function fillForm(): void
    {
        $values = [];

        foreach ($this->content_element_repo->readAllByObjId($this->obj_id) as $element) {
            $field = $element->getUserDefinedField();
            if (!$field) {
                $this->ui_util->sendFailure(sprintf(
                    $this->plugin->txt("udf.not_found"),
                    $element->getUdfField()
                ));
            }

            $values[$element->getUdfField()] = $field?->retrieveValueFromUser($this->user) ?? "";
        }
        $this->setValuesByArray($values);
    }

    public function saveForm(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $user = $this->user;

        $log_values = [];

        foreach ($this->content_element_repo->readAllByObjId($this->obj_id) as $element) {
            $value = $this->getInput((string) $element->getUdfField());

            if (!$value) {
                $value = $this->getInput("udf_" . $element->getUdfField());
            }

            $field = $element->getUserDefinedField();
            $user = $field?->addValueToUserObject(
                $user,
                Context::UserAdministration,
                $value,
                new ilPropertyFormGUI()
            );
            $log_values[$element->getTitle()] = $value;
        }

        $user->update();

        $this->log_entry_repo->create(new LogEntry(
            $this->obj_id,
            $user->getId(),
            new ilDateTime(time(), IL_CAL_UNIX),
            $log_values
        ));

        return true;
    }
}
