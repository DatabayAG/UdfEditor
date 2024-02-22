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

require_once __DIR__ . "/../vendor/autoload.php";

class ilObjUdfEditorListGUI extends ilObjectPluginListGUI
{
    public function getGuiClass(): string
    {
        return ilObjUdfEditorGUI::class;
    }

    public function initCommands(): array
    {
        $this->timings_enabled = true;
        $this->subscribe_enabled = false;
        $this->payment_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->comments_enabled = false;
        $this->tags_enabled = false;
        $this->notes_enabled = false;

        $commands = [
            [
                "permission" => "read",
                "cmd" => ilObjUdfEditorGUI::CMD_INDEX,
                "default" => true,
            ],
            [
                "permission" => "write",
                "cmd" => ilObjUdfEditorGUI::CMD_SETTINGS,
                "lang_var" => 'settings'
            ]
        ];

        return $commands;
    }

    public function initType(): void
    {
        $this->setType(ilUdfEditorPlugin::PLUGIN_ID);
    }

    /**
     * get all alert properties
     */
    public function getAlertProperties(): array
    {
        $alert = [];
        foreach ((array) $this->getCustomProperties([]) as $prop) {
            if ($prop['alert'] == true) {
                $alert[] = $prop;
            }
        }

        return $alert;
    }

    /**
     * Get item properties
     * @return    array        array of property arrays:
     *                        'alert' (boolean) => display as an alert property (usually in red)
     *                        'property' (string) => property name
     *                        'value' (string) => property value
     */
    public function getCustomProperties($a_prop): array
    {
        $props = parent::getCustomProperties([]);

        try {
            /** @var xudfSetting $settings */
            $settings = xudfSetting::find($this->obj_id);
        } catch (Throwable $ex) {
            return $props;
        }

        if (!$settings->isOnline()) {
            $props[] = [
                'alert' => true,
                'newline' => true,
                'property' => 'Status',
                'value' => 'Offline',
                'propertyNameVisible' => true
            ];
        }

        return $props;
    }
}
