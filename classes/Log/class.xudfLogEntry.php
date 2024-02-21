<?php


class xudfLogEntry extends ActiveRecord
{
    public const TABLE_NAME = 'xudf_log_entry';


    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @var int
     *
     * @con_has_field    true
     * @con_sequence     true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     * @con_is_primary   true
     */
    protected $id;
    /**
     * @var int
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     */
    protected $obj_id;
    /**
     * @var int
     *
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_is_notnull   true
     */
    protected $usr_id;
    /**
     * @var array
     *
     * @con_has_field  true
     * @con_fieldtype  clob
     * @con_is_notnull true
     */
    protected $values = [];
    /**
     * @var ilDateTime
     *
     * @con_has_field  true
     * @con_fieldtype  timestamp
     * @con_index      true
     * @con_is_notnull true
     */
    protected $timestamp;


    /**
     * @param $obj_id int
     * @param $usr_id int
     * @param $values array
     *
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
     *
     * @return false|int|mixed|string
     */
    public function sleep($field_name)
    {
        switch ($field_name) {
            case 'values':
                return json_encode($this->values);
            case 'timestamp':
                return $this->timestamp->get(IL_CAL_DATETIME);
            default:
                return parent::sleep($field_name);
        }
    }


    /**
     *
     * @return ilDateTime|mixed
     * @throws ilDateTimeException
     */
    public function wakeUp($field_name, $field_value)
    {
        switch ($field_name) {
            case 'values':
                return json_decode($field_value, true);
            case 'timestamp':
                return new ilDateTime($field_value, IL_CAL_DATETIME);
        }

        return parent::wakeUp($field_name, $field_value);
    }
}
