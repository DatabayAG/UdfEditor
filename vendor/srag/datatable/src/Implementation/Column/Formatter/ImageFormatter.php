<?php

namespace srag\DataTableUI\UdfEditor\Implementation\Column\Formatter;

use srag\DataTableUI\UdfEditor\Component\Column\Column;
use srag\DataTableUI\UdfEditor\Component\Data\Row\RowData;
use srag\DataTableUI\UdfEditor\Component\Format\Format;

/**
 * Class ImageFormatter
 *
 * @package srag\DataTableUI\UdfEditor\Implementation\Column\Formatter
 */
class ImageFormatter extends DefaultFormatter
{

    /**
     * @inheritDoc
     */
    public function formatRowCell(Format $format, $image, Column $column, RowData $row, string $table_id) : string
    {
        if (!empty($image)) {
            return self::output()->getHTML(self::dic()->ui()->factory()->image()->responsive($image, ""));
        } else {
            return "";
        }
    }
}
