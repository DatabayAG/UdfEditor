<?php

namespace srag\Plugins\UdfEditor\Libs\CustomInputGUIs\Loader;

use Closure;
use ILIAS\Data\Factory;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Implementation\Render\Loader;
use ILIAS\UI\Implementation\Render\RendererFactory;
use ILIAS\UI\Renderer;
use Pimple\Container;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent\InputGUIWrapperUIInputComponent;
use srag\Plugins\UdfEditor\Libs\CustomInputGUIs\InputGUIWrapperUIInputComponent\Renderer as InputGUIWrapperUIInputComponentRenderer;
use Throwable;

class CustomInputGUIsLoaderDetector implements Loader
{
    /**
     * @var bool
     */
    protected static $has_fix_ctrl_namespace_current_url = false;
    private Container $dic;
    protected Loader $loader;

    public function __construct(Loader $loader)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->loader = $loader;
    }

    public static function exchangeUIRendererAfterInitialization(Closure $renderer, \ILIAS\DI\Container $dic): Closure
    {
        self::fixCtrlNamespaceCurrentUrl();

        return function () use ($dic, $renderer): Renderer|Closure {
            try {
                $rendererObj = $renderer($dic);

                if ($rendererObj instanceof DefaultRenderer) {
                    /** @var DefaultRenderer|Closure $renderer */
                    $previous_renderer_loader = Closure::bind(function (): Loader {
                        return $this->component_renderer_loader;
                    }, $rendererObj, DefaultRenderer::class)();
                    return new DefaultRenderer(
                        new self($previous_renderer_loader),
                        $dic["ui.javascript_binding"]
                    );
                }
                return $rendererObj;
            } catch (Throwable $ex) {
                return $renderer($dic);
            }
        };
    }


    private static function fixCtrlNamespaceCurrentUrl(): void
    {
        if (!self::$has_fix_ctrl_namespace_current_url) {
            self::$has_fix_ctrl_namespace_current_url = true;

            // Fix language select meta bar which current ctrl gui has namespaces (public page)
            if (isset($_SERVER["REQUEST_URI"])) {
                $_SERVER["REQUEST_URI"] = str_replace("\\", "%5C", $_SERVER["REQUEST_URI"]);
            }
        }
    }

    public function getRendererFactoryFor(Component $component): RendererFactory
    {
        return $this->loader->getRendererFactoryFor($component);
    }

    public function getRendererFor(Component $component, array $contexts): ComponentRenderer
    {
        $renderer = null;

        if (!empty($this->get_renderer_for_hooks)) {
            foreach ($this->get_renderer_for_hooks as $get_renderer_for_hook) {
                $renderer = $get_renderer_for_hook($component, $contexts);
                if ($renderer !== null) {
                    break;
                }
            }
        }

        if ($renderer === null) {
            if ($component instanceof InputGUIWrapperUIInputComponent) {
                $renderer = new InputGUIWrapperUIInputComponentRenderer(
                    $this->dic->ui()->factory(),
                    $this->dic["ui.template_factory"],
                    $this->dic->language(),
                    $this->dic["ui.javascript_binding"],
                    $this->dic["ui.pathresolver"],
                    new Factory(),
                    $this->dic["help.text_retriever"],
                    $this->dic["ui.upload_limit_resolver"]
                );
            } else {
                return $this->loader->getRendererFor($component, $contexts);
            }
        }

        return $renderer;
    }
}
