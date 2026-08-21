<?php

namespace ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent;

use ilFormPropertyGUI;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;

trait InputGUIWrapperConstraintTrait
{
    public function __construct(ilFormPropertyGUI $input, DataFactory $data_factory, ilLanguage $lng)
    {
        parent::__construct(
            static function ($value) use ($input): bool {
                return $input->checkInput();
            },
            static function (callable $txt, $value) use ($input): string {
                return $input->getAlert();
            },
            $data_factory,
            $lng
        );
    }
}
