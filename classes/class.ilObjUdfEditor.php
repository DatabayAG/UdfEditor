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

use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationInterface;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;
use ILIAS\Plugin\UdfEditor\Model\ContentElement;
use ILIAS\Plugin\UdfEditor\Model\Settings;
use ILIAS\Plugin\UdfEditor\Repository\ContentElementRepository;
use ILIAS\Plugin\UdfEditor\Repository\SettingsRepository;

require_once __DIR__ . "/../vendor/autoload.php";

class ilObjUdfEditor extends ilObjectPlugin
{
    use Notifications4PluginTrait;

    protected ?Settings $settings = null;
    private ContentElementRepository $content_element_repo;
    private SettingsRepository $settings_repo;

    public function __construct(int $a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
        $this->content_element_repo = new ContentElementRepository();
        $this->settings_repo = new SettingsRepository();
    }

    protected function initType(): void
    {
        $this->type = ilUdfEditorPlugin::PLUGIN_ID;
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        $this->settings_repo->create(
            new Settings($this->getId())
        );
    }

    protected function beforeDelete(): bool
    {
        $this->settings_repo->deleteById($this->getId());
        return true;
    }

    /**
     * @param self|ilObject2 $new_obj
     */
    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null): void
    {
        $this->cloneSettings($new_obj);
        $this->cloneContentElements($new_obj);
        $this->clonePageObject($new_obj);
    }

    public function getStyleSheetId(): int
    {
        return ilObjStyleSheet::lookupObjectStyle($this->getId());
    }

    public function getSettings(): Settings
    {
        if (!($this->settings instanceof Settings)) {
            $this->settings = $this->settings_repo->read($this->id);
        }

        return $this->settings;
    }

    protected function cloneSettings(ilObjUdfEditor $new_obj): void
    {
        $old_settings = $this->getSettings();
        $new_settings = $new_obj->getSettings();

        $new_settings->setAdditionalNotification($old_settings->getAdditionalNotification());
        $new_settings->setMailNotification($old_settings->isMailNotification());
        $new_settings->setShowInfoTab($old_settings->isShowInfoTab());
        $new_settings->setRedirectType($old_settings->getRedirectType());
        $new_settings->setRedirectValue($old_settings->getRedirectValue());
        $new_settings->setAlwaysEdit($old_settings->isAlwaysEdit());
        $new_settings->setIsOnline($old_settings->isOnline());

        $this->settings_repo->update($new_settings);
    }

    protected function cloneContentElements(ilObjUdfEditor $new_obj): void
    {
        /** @var array<int, array{old: ContentElement, new: ContentElement}> $old_to_new_content_element_map */
        $old_to_new_content_element_map = [];

        foreach ($this->content_element_repo->readAllByObjId($this->getId(), true) as $old_content_element) {
            $new_content_element = new ContentElement(
                $new_obj->getId(),
                $old_content_element->getTitle(),
                $old_content_element->getDescription(),
                0,
                $old_content_element->getUdfField(),
                $old_content_element->isSeparator()
            );

            $old_to_new_content_element_map[] = [
                "old" => $old_content_element,
                "new" => $new_content_element
            ];

            $this->content_element_repo->create($new_content_element);
        }

        //create method resets sortation, sortation needs to be done after they are created
        foreach ($old_to_new_content_element_map as $old_and_new) {
            $old = $old_and_new["old"];
            $new = $old_and_new["new"];

            $new->setIsRequired($old->isRequired());
            $new->setSort($old->getSort());
            $new->update();
        }
    }

    protected function clonePageObject(ilObjUdfEditor $new_obj): void
    {
        $old_page_object = new xudfPageObject($this->getId());
        $old_page_object->copy($new_obj->getId());

    }

    public function getNotification(): NotificationInterface
    {
        $settings = $this->getSettings();
        if (empty($settings->getNotificationName())) {
            $settings->setNotificationName("object_{$settings->getObjId()}");
            $this->settings_repo->store($settings);
        }

        $notification = self::notifications4plugin()->notifications()->getNotificationByName($settings->getNotificationName());

        if ($notification === null) {
            $notification = self::notifications4plugin()->notifications()->factory()->newInstance();

            $notification->setTitle(ilUdfEditorPlugin::getInstance()->txt("notification"));

            $notification->setName($settings->getNotificationName());

            $notification->setSubject("ILIAS: {{ object.getTitle }}", "default");

            $notification->setText("Sehr geehrte/r {{ user.getFullname }},

Sie haben im Objekt „{{ object.getTitle }}“ die folgenden Angaben ausgewählt:

{% for key, value in user_defined_data %}
{{ key }} : {{ value }}

{% endfor %}
{{ \"now\"|date('d.m.Y H:i') }}", "default");

            self::notifications4plugin()->notifications()->storeNotification($notification);
        }

        return $notification;
    }
}
