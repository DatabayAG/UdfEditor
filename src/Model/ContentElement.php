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

use ILIAS\Plugin\UdfEditor\Exception\UDFNotFoundException;
use ilUserDefinedFields;
use JsonSerializable;

class ContentElement implements JsonSerializable
{
    private int $id;

    public function __construct(
        private readonly int $obj_id,
        private string $title,
        private string $description = "",
        private int $sort = 0,
        private ?int $udf_field = null,
        private bool $separator = false,
        private bool $required = false
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

    public function getTitle(): string
    {
        if (!$this->isSeparator()) {
            try {
                $udfFieldDefinition = $this->getUdfFieldDefinition();
            } catch (UDFNotFoundException) {
                $udfFieldDefinition = null;
            }
            if ($udfFieldDefinition) {
                return $udfFieldDefinition['field_name'];

            }
        }

        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    public function getUdfField(): ?int
    {
        return $this->udf_field;
    }

    public function setUdfField(?int $udf_field): self
    {
        $this->udf_field = $udf_field;
        return $this;
    }

    public function isSeparator(): bool
    {
        return $this->separator;
    }

    public function setSeparator(bool $separator): self
    {
        $this->separator = $separator;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @throws UDFNotFoundException
     */
    public function getUdfFieldDefinition(): array
    {
        $definition = ilUserDefinedFields::_getInstance()->getDefinition($this->getUdfField());
        if (empty($definition)) {
            throw new UDFNotFoundException('udf with id ' . $this->getUdfField() . ' could not be found and was probably deleted');
        }

        return $definition;
    }

    public function jsonSerialize(): array
    {
        return [
            "id" => $this->getId(),
            "obj_id" => $this->getObjId(),
            "sort" => $this->getSort(),
            "separator" => $this->isSeparator(),
            "udf_field" => $this->getUdfField(),
            "title" => $this->getTitle(),
            "description" => $this->getDescription(),
            "required" => $this->isRequired()
        ];
    }
}
