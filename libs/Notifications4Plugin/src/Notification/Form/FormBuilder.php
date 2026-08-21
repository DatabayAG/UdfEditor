<?php

namespace srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\Form;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilNonEditableValueGUI;
use ilTextInputGUI;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\FormBuilder\AbstractFormBuilder;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent\InputGUIWrapperUIInputComponent;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\PropertyFormGUI\Items\Items;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI\MultilangualTabsInputGUI;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\TabsInputGUI\TabsInputGUI;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\TextAreaInputGUI\TextAreaInputGUI;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationCtrl;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Notification\NotificationInterface;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Parser\Parser;
use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

class FormBuilder extends AbstractFormBuilder
{
    use Notifications4PluginTrait;
    private Container $dic;
    private Factory $uiFactory;
    private Renderer $uiRenderer;

    public function __construct(object $parentGui, protected NotificationInterface $notification)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->uiFactory = $this->dic->ui()->factory();
        $this->uiRenderer = $this->dic->ui()->renderer();

        parent::__construct($parentGui);
    }


    public function render(): string
    {
        $this->messages[] = $this->uiFactory->messageBox()->info(
            htmlspecialchars(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_placeholder_types_info"))
            . "<br><br>"
            . $this->uiRenderer->render($this->uiFactory->listing()->descriptive(self::notifications4plugin()->getPlaceholderTypes()))
        );

        return parent::render();
    }


    protected function getButtons(): array
    {
        $buttons = [];

        if (!empty($this->notification->getId())) {
            $buttons[NotificationCtrl::CMD_UPDATE_NOTIFICATION] = self::notifications4plugin()->getPlugin()->txt("notifications4plugin_save");
        } else {
            $buttons[NotificationCtrl::CMD_CREATE_NOTIFICATION] = self::notifications4plugin()->getPlugin()->txt("notifications4plugin_add");
        }

        $buttons[NotificationCtrl::CMD_BACK] = self::notifications4plugin()->getPlugin()->txt("notifications4plugin_cancel");

        return $buttons;
    }


    protected function getData(): array
    {
        $data = [];

        foreach (array_keys($this->getFields()) as $key) {
            $data[$key] = match ($key) {
                "parser" => [
                    "value" => Items::getter($this->notification, $key),
                    "group_values" => $this->notification->getParserOptions()
                ],
                default => Items::getter($this->notification, $key),
            };
        }

        return $data;
    }


    protected function getFields(): array
    {
        $fields = [];

        if (!empty($this->notification->getId())) {
            $fields += [
                "id" => new InputGUIWrapperUIInputComponent(new ilNonEditableValueGUI(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_id"))),
                "name" => (new InputGUIWrapperUIInputComponent(new ilNonEditableValueGUI(self::notifications4plugin()
                    ->getPlugin()
                    ->txt("notifications4plugin_name"))))->withByline(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_name_info"))
            ];
        } else {
            $fields += [
                "name" => $this->dic
                    ->ui()
                    ->factory()
                    ->input()
                    ->field()
                    ->text(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_name"))
                    ->withByline(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_name_info"))
                    ->withRequired(true)
            ];
        }


        $parser = $this->uiFactory->input()->field()->switchableGroup(array_map(function (Parser $parser): Group {
            return $this->uiFactory->input()->field()->group($parser->getOptionsFields(), $parser->getName() . "<br>" . $this->uiRenderer->render($this->uiFactory->link()
                    ->standard($parser->getDocLink(), $parser->getDocLink())->withOpenInNewViewport(true)))->withByline($this->uiRenderer->render($this->uiFactory->link()
                ->standard($parser->getDocLink(), $parser->getDocLink())->withOpenInNewViewport(true))); // TODO `withByline` not work in ILIAS 6 group (radio), so temporary in label
        }, self::notifications4plugin()->parser()->getPossibleParsers()), self::notifications4plugin()->getPlugin()->txt("notifications4plugin_parser"))->withRequired(true);

        $fields += [
            "title" => $this->uiFactory->input()->field()->text(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_title"))->withRequired(true),
            "description" => $this->uiFactory->input()->field()->textarea(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_description")),
            "parser" => $parser,
            "subjects" => (new InputGUIWrapperUIInputComponent(new TabsInputGUI(self::notifications4plugin()
                ->getPlugin()
                ->txt("notifications4plugin_subject"))))->withRequired(true),
            "texts" => (new InputGUIWrapperUIInputComponent(new TabsInputGUI(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_text"))))->withRequired(true)
        ];
        MultilangualTabsInputGUI::generateLegacy($fields["subjects"]->getInput(), [
            new ilTextInputGUI(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_subject"), "subject")
        ], true);
        $input = new TextAreaInputGUI(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_text"), "text");
        $input->setRows(10);
        MultilangualTabsInputGUI::generateLegacy($fields["texts"]->getInput(), [
            $input
        ], true);

        return $fields;
    }


    protected function getTitle(): string
    {
        if (!empty($this->notification->getId())) {
            return self::notifications4plugin()->getPlugin()->txt("notifications4plugin_edit_notification");
        } else {
            return self::notifications4plugin()->getPlugin()->txt("notifications4plugin_add_notification");
        }
    }


    protected function storeData(array $data): void
    {
        foreach (array_keys($this->getFields()) as $key) {
            switch ($key) {
                case "id" :
                    break;

                case "name" :
                    if (empty($this->notification->getId())) {
                        Items::setter($this->notification, $key, $data[$key]);
                    }
                    break;

                case "parser":
                    Items::setter($this->notification, $key, $data[$key][0]);

                    foreach (array_keys($this->notification->getParserOptions()) as $parser_option_key) {
                        $this->notification->setParserOption($parser_option_key, $data[$key][1][$parser_option_key]);
                    }
                    break;

                default:
                    Items::setter($this->notification, $key, $data[$key]);
                    break;
            }
        }

        self::notifications4plugin()->notifications()->storeNotification($this->notification);
    }
}
