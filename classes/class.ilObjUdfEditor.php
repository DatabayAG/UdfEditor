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

use srag\Plugins\UdfEditor\Exception\UDFNotFoundException;

require_once __DIR__ . "/../vendor/autoload.php";

class ilObjUdfEditor extends ilObjectPlugin
{
    protected ?xudfSetting $settings = null;

    protected function initType(): void
    {
        $this->type = ilUdfEditorPlugin::PLUGIN_ID;
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        $xudfSetting = new xudfSetting();
        $xudfSetting->setObjId($this->getId());
        $xudfSetting->create();
    }

    protected function beforeDelete(): bool
    {
        xudfSetting::find($this->getId())->delete();
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

    public function getSettings(): xudfSetting
    {
        if (!($this->settings instanceof xudfSetting)) {
            $this->settings = xudfSetting::find($this->id);
        }

        return $this->settings;
    }

    protected function cloneSettings(ilObjUdfEditor $new_obj): void
    {
        $old_settings = $this->getSettings();
        $new_settings = $new_obj->getSettings();

        $new_settings->setAdditionalNotification($old_settings->getAdditionalNotification());
        $new_settings->setMailNotification($old_settings->hasMailNotification());
        $new_settings->setShowInfoTab($old_settings->isShowInfoTab());
        $new_settings->setRedirectType($old_settings->getRedirectType());
        $new_settings->setRedirectValue($old_settings->getRedirectValue());
        $new_settings->update();
    }

    protected function cloneContentElements(ilObjUdfEditor $new_obj): void
    {
        /** @var array<int, array{old: xudfContentElement, new: xudfContentElement}> $old_to_new_content_element_map */
        $old_to_new_content_element_map = [];

        /** @var xudfContentElement $old_content_element */
        foreach (xudfContentElement::where(['obj_id' => $this->getId()])->get() as $old_content_element) {
            $new_content_element = new xudfContentElement();

            $old_to_new_content_element_map[] = [
                "old" => $old_content_element,
                "new" => $new_content_element
            ];

            $new_content_element->setObjId($new_obj->getId());
            try {
                $new_content_element->setTitle($old_content_element->getTitle());
            } catch (UDFNotFoundException $e) {
                $new_content_element->setTitle('UDF not found');
            }
            $new_content_element->setDescription($old_content_element->getDescription());
            $new_content_element->setIsSeparator($old_content_element->isSeparator());
            $new_content_element->setUdfFieldId($old_content_element->getUdfFieldId());
            $new_content_element->create();
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
}
