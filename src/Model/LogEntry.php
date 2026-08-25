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

namespace ILIAS\Plugin\UdfEditor\Model;

use ilDateTime;

class LogEntry
{
    private int $id;

    public function __construct(
        private readonly int $obj_id,
        private readonly int $usr_id,
        private readonly ilDateTime $timestamp,
        private array $values = [],
    ) {
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
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

    public function getTimestamp(): ilDateTime
    {
        return $this->timestamp;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): self
    {
        $this->values = $values;
        return $this;
    }
}
