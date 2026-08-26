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

require_once __DIR__ . "/../vendor/autoload.php";

use ILIAS\DI\Container;
use ILIAS\Plugin\UdfEditor\Libs\CustomInputGUIs\Loader\CustomInputGUIsLoaderDetector;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;
use ILIAS\Plugin\UdfEditor\Repository\ContentElementRepository;
use ILIAS\Plugin\UdfEditor\Repository\SettingsRepository;
use ILIAS\Plugin\UdfEditor\Setup\Migration\DBUpdateSteps;

class ilUdfEditorPlugin extends ilRepositoryObjectPlugin
{
    use Notifications4PluginTrait;

    public const string PLUGIN_ID = 'xudf';
    public const string PLUGIN_CLASS_NAME = self::class;

    protected static bool $init_notifications = false;
    protected static ?ilUdfEditorPlugin $instance = null;

    public function getPluginName(): string
    {
        return 'UdfEditor';
    }

    public static function initNotifications(): void
    {
        if (!self::$init_notifications) {
            self::$init_notifications = true;

            self::notifications4plugin()->withTableNamePrefix(self::PLUGIN_ID)
                ->withPlugin(self::getInstance())
                ->withPlaceholderTypes([
                    "object" => "object " . ilObjUdfEditor::class,
                    "user" => "object " . ilObjUser::class,
                    "user_defined_data" => "array"
                ]);
        }
    }

    public function allowCopy(): bool
    {
        return true;
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            global $DIC;

            /** @var $component_factory ilComponentFactory */
            $component_factory = $DIC['component.factory'];
            /** @var $plugin ilUdfEditorPlugin */
            $plugin = $component_factory->getPlugin(self::PLUGIN_ID);

            self::$instance = $plugin;
        }

        return self::$instance;
    }

    protected function init(): void
    {
        self::initNotifications();
    }

    public function getRelativeDirectory(): string
    {
        return str_replace(ILIAS_ABSOLUTE_PATH . "/public/", "", realpath($this->getDirectory()));
    }

    public function install(): void
    {
        (new DBUpdateSteps())->install($this->db);
        parent::install();
    }

    public function update(): bool
    {
        //DbUpdateSteps should be excuded before the parent update so potential exceptions result in no il_plugin db changes
        (new DBUpdateSteps())->install($this->db);

        return parent::update();
    }

    protected function uninstallCustom(): void
    {
        (new DBUpdateSteps())->uninstall($this->db);

        self::notifications4plugin()->dropTables();
    }

    public function exchangeUIRendererAfterInitialization(Container $dic): Closure
    {
        $renderer = $dic->raw("ui.renderer");
        if (!$this->isActive()) {
            return $renderer;
        }
        return CustomInputGUIsLoaderDetector::exchangeUIRendererAfterInitialization($renderer, $dic);
    }
}
