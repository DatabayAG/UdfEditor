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
            function ($value) use ($input): bool {
                return boolval($input->checkInput());
            },
            function (callable $txt, $value) use ($input): string {
                return strval($input->getAlert());
            },
            $data_factory,
            $lng
        );
    }
}
