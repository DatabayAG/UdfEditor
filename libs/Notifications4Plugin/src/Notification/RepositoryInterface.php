<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification;

interface RepositoryInterface
{
    public function deleteNotification(NotificationInterface $notification): void;


    /**
     * @internal
     */
    public function dropTables(): void;


    public function duplicateNotification(NotificationInterface $notification): NotificationInterface;


    public function factory(): FactoryInterface;


    public function getNotificationById(int $id): ?NotificationInterface;


    public function getNotificationByName(string $name): ?NotificationInterface;


    /**
     * @return NotificationInterface[]
     */
    public function getNotifications(): array;


    public function getNotificationsCount(): int;


    /**
     * @internal
     */
    public function installTables(): void;


    /**
     * @param string $name |null
     * @deprecated
     */
    public function migrateFromOldGlobalPlugin(string $name = null): ?NotificationInterface;


    public function storeNotification(NotificationInterface $notification): void;
}
