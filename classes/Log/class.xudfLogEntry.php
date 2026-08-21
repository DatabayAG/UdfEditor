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

class xudfLogEntry extends ActiveRecord
{
    public const TABLE_NAME = 'xudf_log_entry';

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @con_has_field    true
     * @con_sequence     true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     * @con_is_primary   true
     */
    protected ?int $id = null;

    /**
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     */
    protected int $obj_id;

    /**
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     */
    protected int $usr_id;

    /**
     * @con_has_field  true
     * @con_fieldtype  clob
     * @con_is_notnull true
     */
    protected array $values = [];

    /**
     * @con_has_field  true
     * @con_fieldtype  timestamp
     * @con_index      true
     * @con_is_notnull true
     */
    protected ilDateTime $timestamp;

    /**
     * @throws ilDateTimeException
     */
    public static function createNew(int $obj_id, int $usr_id, array $values): self
    {
        $new = new self();
        $new->obj_id = $obj_id;
        $new->usr_id = $usr_id;
        $new->values = $values;
        $new->timestamp = new ilDateTime(time(), IL_CAL_UNIX);
        $new->create();

        return $new;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getTimestamp(): ilDateTime
    {
        return $this->timestamp;
    }

    /**
     * @return false|int|mixed|string
     */
    public function sleep($field_name): mixed
    {
        return match ($field_name) {
            'values' => json_encode($this->values, JSON_THROW_ON_ERROR),
            'timestamp' => $this->timestamp->get(IL_CAL_DATETIME),
            default => parent::sleep($field_name),
        };
    }

    /**
     * @return ilDateTime|mixed
     * @throws ilDateTimeException
     */
    public function wakeUp($field_name, $field_value): mixed
    {
        return match ($field_name) {
            'values' => json_decode($field_value, true, 512, JSON_THROW_ON_ERROR),
            'timestamp' => new ilDateTime($field_value, IL_CAL_DATETIME),
            default => parent::wakeUp($field_name, $field_value),
        };
    }
}
