<?php

namespace ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent;

use ilHiddenInputGUI;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;

class Renderer extends AbstractRenderer
{
    public function render(Component $component, RendererInterface $default_renderer): string
    {
        if ($component->getInput() instanceof ilHiddenInputGUI) {
            return "";
        }

        $input_tpl = $this->getTemplate("input.html", true, true);

        $html = $this->wrapInFormContext(
            $component,
            "",
            $this->renderInputField(
                $input_tpl,
                $component,
                "",
                $default_renderer
            ),
        );

        return $html;
    }


    protected function renderInputField(Template $tpl, Input $input, $id, RendererInterface $default_renderer): string
    {
        return $this->renderInput($tpl, $input);
    }
}
