<?php

namespace ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\FormBuilder;

use Closure;
use Exception;
use ilFormPropertyDispatchGUI;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\OptionalGroup;
use ILIAS\UI\Component\Input\Field\Radio as RadioInterface;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Implementation\Component\Input\Field\Group;
use ILIAS\UI\Implementation\Component\Input\Field\Radio;
use ILIAS\UI\Implementation\Component\Input\Field\SwitchableGroup;
use ilSubmitButton;
use ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent\InputGUIWrapperUIInputComponent;
use Throwable;

abstract class AbstractFormBuilder implements FormBuilder
{
    public const string REPLACE_BUTTONS_REG_EXP = '/(<button\s+class\s*=\s*"btn btn-default"\s+data-action\s*=\s*"#?"(\s+id\s*=\s*"[a-z0-9_]+")?\s*>)(.+)(<\/button\s*>)/';
    protected ?Form $form = null;
    /**
     * @var MessageBox[]
     */
    protected array $messages = [];
    protected object $parent;
    private readonly Container $dic;


    public function __construct(object $parent)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->parent = $parent;
    }


    public function executeCommand(): void
    {
        $next_class = $this->dic->ctrl()->getNextClass($this);

        switch (strtolower($next_class)) {
            case strtolower(ilFormPropertyDispatchGUI::class):
                foreach ($this->getForm()->getInputs()["form"]->getInputs() as $input) {
                    if ($input instanceof InputGUIWrapperUIInputComponent) {
                        if ($input->getInput()->getPostVar() === strval(filter_input(INPUT_GET, "postvar"))) {
                            $form_dispatcher = new ilFormPropertyDispatchGUI();
                            $form_dispatcher->setItem($input->getInput());
                            $this->dic->ctrl()->forwardCommand($form_dispatcher);
                            break;
                        }
                    }
                }
                break;

            default:
                break;
        }
    }


    public function getForm(): Form
    {
        if ($this->form === null) {
            $this->form = $this->buildForm();
        }

        return $this->form;
    }


    public function render(): string
    {
        $html = $this->dic->ui()->renderer()->render($this->getForm());

        $html = $this->setButtonsToForm($html);

        return $this->dic->ui()->renderer()->render($this->messages) . $html;
    }


    public function storeForm(): bool
    {
        try {
            $this->form = $this->getForm()->withRequest($this->dic->http()->request());

            $data = $this->form->getData();

            if (empty($data)) {
                throw new Exception();
            }

            $data = $data["form"] ?? [];

            if (!$this->validateData($data)) {
                throw new Exception();
            }

            $this->storeData($data);
        } catch (Throwable) {
            $this->messages[] = $this->dic->ui()->factory()->messageBox()->failure($this->dic->language()->txt("form_input_not_valid"));

            return false;
        }

        return true;
    }


    protected function buildForm(): Form
    {
        $this->dic->language()->loadLanguageModule("form");

        $form = $this->dic->ui()->factory()->input()->container()->form()->standard(
            $this->getAction(),
            [
                "form" => $this->dic->ui()->factory()->input()->field()->section($this->getFields(), $this->getTitle())
            ]
        );

        $this->setDataToForm($form);

        return $form;
    }


    protected function getAction(): string
    {
        return $this->dic->ctrl()->getFormAction($this->parent);
    }


    abstract protected function getButtons(): array;


    abstract protected function getData(): array;


    abstract protected function getFields(): array;


    abstract protected function getTitle(): string;


    protected function setButtonsToForm(string $html): string
    {
        $html = preg_replace_callback(self::REPLACE_BUTTONS_REG_EXP, function (array $matches): string {
            $buttons = [];

            foreach ($this->getButtons() as $cmd => $label) {
                if (!empty($buttons)) {
                    $buttons[] = "&nbsp;";
                }

                $button = ilSubmitButton::getInstance();

                $button->setCommand($cmd);

                $button->setCaption($label, false);

                $buttons[] = $button;
            }

            $buttonHtml = "";
            foreach ($buttons as $button) {
                $buttonHtml .= is_string($button) ? $button : $button->render();
            }

            return $buttonHtml;
        }, $html);

        return $html;
    }

    protected function setDataToForm(Form $form): void
    {
        $this->setDataToFormGroup($form->getInputs()["form"], $this->getData());
    }


    abstract protected function storeData(array $data): void;


    protected function validateData(array $data): bool
    {
        return true;
    }


    private function setDataToFormGroup(Group $group, array $data): void
    {
        $inputs = $group->getInputs();

        if (!empty($inputs)) {
            foreach ($inputs as $key => $field) {
                if (isset($data[$key])) {

                    if ($field instanceof OptionalGroup) {
                        $inputs2 = $field->getInputs();
                        if (!empty($inputs2)) {
                            if (isset($data[$key]["value"])) {
                                try {
                                    $inputs[$key] = $field = $field->withValue($data[$key]["value"] ? [] : null);
                                } catch (Throwable) {

                                }
                            }
                            $data2 = ($data[$key]["group_values"] ?? $data[$key])["dependant_group"];
                            foreach ($inputs2 as $key2 => $field2) {
                                if (isset($data2[$key2])) {
                                    try {
                                        $inputs2[$key2] = $field2 = $field2->withValue($data2[$key2]);
                                    } catch (Throwable) {

                                    }
                                }
                            }
                            Closure::bind(function (array $inputs2): void {
                                $this->inputs = $inputs2;
                            }, $field, Group::class)($inputs2);
                        }
                        continue;
                    }

                    if ($field instanceof SwitchableGroup) {
                        $inputs2 = $field->getInputs();
                        if (!empty($inputs2)) {
                            if (isset($data[$key]["value"])) {
                                try {
                                    $inputs[$key] = $field = $field->withValue($data[$key]["value"]);
                                } catch (Throwable) {

                                }
                            }
                            $data2 = $data[$key]["group_values"];
                            foreach ($inputs2 as $field2) {
                                $inputs3 = $field2->getInputs();
                                if (!empty($inputs3)) {
                                    foreach ($inputs3 as $key3 => $field3) {
                                        if (isset($data2[$key3])) {
                                            try {
                                                $inputs3[$key3] = $field3 = $field3->withValue($data2[$key3]);
                                            } catch (Throwable) {

                                            }
                                        }
                                    }
                                    Closure::bind(function (array $inputs3): void {
                                        $this->inputs = $inputs3;
                                    }, $field2, Group::class)($inputs3);
                                }
                            }
                            Closure::bind(function (array $inputs2): void {
                                $this->inputs = $inputs2;
                            }, $field, Group::class)($inputs2);
                        }
                    }
                    if ($field instanceof RadioInterface
                        && isset($data[$key]["value"])
                        && !empty($inputs2 = Closure::bind(function (array $data, string $key): array {
                            return $this->dependant_fields[$data[$key]["value"]];
                        }, $field, Radio::class)($data, $key))
                    ) {
                        try {
                            $inputs[$key] = $field = $field->withValue($data[$key]["value"]);
                        } catch (Throwable) {

                        }
                        $data2 = $data[$key]["group_values"];
                        foreach ($inputs2 as $key2 => $field2) {
                            if (isset($data2[$key2])) {
                                try {
                                    $inputs2[$key2] = $field2 = $field2->withValue($data2[$key2]);
                                } catch (Throwable) {

                                }
                            }
                        }
                        Closure::bind(function (array $data, string $key, array $inputs2): void {
                            $this->dependant_fields[$data[$key]["value"]] = $inputs2;
                        }, $field, Radio::class)($data, $key, $inputs2);
                        continue;
                    }

                    if ($field instanceof Section) {
                        $this->setDataToFormGroup($field, $data[$key]);
                        continue;
                    }
                    try {
                        $inputs[$key] = $field = $field->withValue($data[$key]);
                    } catch (Throwable) {

                    }
                }
            }

            Closure::bind(function (array $inputs): void {
                $this->inputs = $inputs;
            }, $group, Group::class)($inputs);
        }
    }
}
