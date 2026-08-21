<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

class NotificationsCtrl implements DataRetrieval
{
    use Notifications4PluginTrait;

    public const CMD_LIST_NOTIFICATIONS = "listNotifications";
    public const TAB_NOTIFICATIONS = "notifications";

    private Container $dic;
    private Factory $uiFactory;
    private Renderer $uiRenderer;


    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->uiFactory = $this->dic->ui()->factory();
        $this->uiRenderer = $this->dic->ui()->renderer();
    }


    public function handleCommand($cmd): bool
    {
        $this->setTabs();

        if ($cmd === self::CMD_LIST_NOTIFICATIONS) {
            $this->{$cmd}();
            return true;
        }
        return false;
    }


    protected function listNotifications(): void
    {
        $table = $this->uiFactory->table()->data(
            self::notifications4plugin()->getPlugin()->txt("notifications4plugin_notifications"),
            [
                "title" => $this->uiFactory->table()->column()->text(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_title")),
                "description" => $this->uiFactory->table()->column()->text(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_description")),
                "name" => $this->uiFactory->table()->column()->text(self::notifications4plugin()->getPlugin()->txt("notifications4plugin_name")),
            ],
            $this
        )->withRequest($this->dic->http()->request())/*->withActions($this->uiFactory->table()->action()->standard())*/
        ;

        $this->dic->ui()->mainTemplate()->setContent($this->uiRenderer->render($table));
    }


    protected function setTabs(): void
    {

    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $notifications = $this->orderRows(self::notifications4plugin()->notifications()->getNotifications(), $order);

        $notificationsRanged = [];
        foreach ($notifications as $index => $notification) {
            if ($index >= $range->getStart() && $index <= $range->getEnd()) {
                $notificationsRanged[] = $notification;
            }
        }
        $notifications = $notificationsRanged;

        foreach ($notifications as $notification) {
            $dataRow = $row_builder->buildDataRow(
                $notification->getId(),
                [
                    "title" => $notification->getTitle(),
                    "description" => $notification->getDescription(),
                    "name" => $notification->getName()
                ]
            );

            yield $dataRow;
        }
    }

    /**
     * @param Notification[] $notifications
     */
    protected function orderRows(array $notifications, Order $order): array
    {
        [$aspect, $direction] = $order->join('', function ($i, $k, $v) {
            return [$k, $v];
        });


        usort($notifications, static function (Notification $a, Notification $b) use ($aspect): int {
            switch ($aspect) {
                default:
                case "title":
                    return strcmp($a->getTitle(), $b->getTitle());
                case "description":
                    return strcmp($a->getDescription(), $b->getDescription());
                case "name":
                    return strcmp($a->getName(), $b->getName());
            }
        });

        if ($direction === $order::DESC) {
            $notifications = array_reverse($notifications);
        }
        return $notifications;
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        $notifications = self::notifications4plugin()->notifications()->getNotifications();
        return count($notifications);
    }
}
