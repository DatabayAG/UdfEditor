<?php

namespace srag\Plugins\UdfEditor\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent;

use Exception;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\Input\Field\Renderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ilTable2GUI;
use ilTemplate;

abstract class AbstractRenderer extends Renderer
{
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);

        $dir = __DIR__;
        $dir = "./" . substr($dir, strpos($dir, "/Customizing/") + 1);

        $registry->register($dir . "/css/InputGUIWrapperUIInputComponent.css");
    }


    protected function getComponentInterfaceName(): array
    {
        return [
            InputGUIWrapperUIInputComponent::class
        ];
    }


    protected function getTemplatePath(string $name): string
    {
        if ($name === "input.html") {
            return __DIR__ . "/templates/" . $name;
        }

        return "components/ILIAS/UI/src/templates/default/Input/$name";
    }


    protected function renderInput(Template $tpl, InputGUIWrapperUIInputComponent $input): string
    {
        $tpl->setVariable("INPUT", $this->getHTML($input->getInput()));

        return $tpl->get();
    }

    public function getHTML($value): string
    {
        global $DIC;
        if (is_array($value)) {
            $html = "";
            foreach ($value as $gui) {
                $html .= $this->getHTML($gui);
            }
        } else {
            switch (true) {
                // HTML
                case (is_string($value)):
                    $html = $value;
                    break;

                    // Component instance
                case ($value instanceof Component):
                    if ($DIC->ctrl()->isAsynch()) {
                        $html = $DIC->ui()->renderer()->renderAsync($value);
                    } else {
                        $html = $DIC->ui()->renderer()->render($value);
                    }
                    break;

                    // ilTable2GUI instance
                case ($value instanceof ilTable2GUI):
                    // Fix stupid broken ilTable2GUI (render has only header without rows)
                    $html = $value->getHTML();
                    break;

                    // GUI instance
                case method_exists($value, "render"):
                    $html = $value->render();
                    break;
                case method_exists($value, "getHTML"):
                    $html = $value->getHTML();
                    break;

                    // Template instance
                case ($value instanceof ilTemplate):
                case ($value instanceof Template):
                    $html = $value->get();
                    break;

                    // Not supported!
                default:
                    throw new Exception("Class " . $value::class . " is not supported for output!");
                    break;
            }
        }

        return $html;
    }
}
