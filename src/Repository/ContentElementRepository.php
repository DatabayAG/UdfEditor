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

namespace ILIAS\Plugin\UdfEditor\Repository;

use ilDBConstants;
use ilDBInterface;
use ILIAS\Plugin\UdfEditor\Model\ContentElement;

class ContentElementRepository
{
    public const string TABLE_NAME = "xudf_element";

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            global $DIC;
            $this->db = $DIC->database();
        }
    }

    /**
     * @return list<ContentElement>
     */
    public function readAllByObjId(int $obj_id, bool $with_separators = false, bool $order_by_sort = false): array
    {
        $order_by = $order_by_sort ? "sort" : "id";

        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME
            . " WHERE obj_id = %s"
            . (
                $with_separators
                    ? ""
                    : " AND is_separator = " . $this->db->quote(false, ilDBConstants::T_INTEGER)
            )
            . " ORDER BY $order_by",
            [ilDBConstants::T_INTEGER],
            [$obj_id]
        );

        $data = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $data[] = $this->map($row);
        }

        return $data;
    }

    public function read(int $id): ?ContentElement
    {
        $result = $this->db->queryF(
            "SELECT * FROM " . self::TABLE_NAME . " WHERE id = %s",
            [ilDBConstants::T_INTEGER],
            [$id]
        );

        $row = $this->db->fetchAssoc($result);

        if (!$row) {
            return null;
        }

        return $this->map($row);
    }

    public function create(ContentElement $content_element): bool
    {
        $id = $this->db->nextId(self::TABLE_NAME);
        $content_element->setId($id);

        return $this->db->insert(
            self::TABLE_NAME,
            [
                    "id" => [ilDBConstants::T_INTEGER, $content_element->getId()],
                    "obj_id" => [ilDBConstants::T_INTEGER, $content_element->getObjId()],
                    "title" => [ilDBConstants::T_TEXT, $content_element->getTitle()],
                    "description" => [ilDBConstants::T_TEXT, $content_element->getDescription()],
                    "sort" => [ilDBConstants::T_INTEGER, $content_element->getSort()],
                    "udf_field" => [ilDBConstants::T_INTEGER, $content_element->getUdfField()],
                    "is_separator" => [ilDBConstants::T_INTEGER, $content_element->isSeparator()],
                    "is_required" => [ilDBConstants::T_INTEGER, $content_element->isRequired()],
                ]
        ) === 1;
    }

    public function update(ContentElement $content_element): bool
    {
        return $this->db->update(
            self::TABLE_NAME,
            [
                    "obj_id" => [ilDBConstants::T_INTEGER, $content_element->getObjId()],
                    "title" => [ilDBConstants::T_TEXT, $content_element->getTitle()],
                    "description" => [ilDBConstants::T_TEXT, $content_element->getDescription()],
                    "sort" => [ilDBConstants::T_INTEGER, $content_element->getSort()],
                    "udf_field" => [ilDBConstants::T_INTEGER, $content_element->getUdfField()],
                    "is_separator" => [ilDBConstants::T_INTEGER, $content_element->isSeparator()],
                    "is_required" => [ilDBConstants::T_INTEGER, $content_element->isRequired()],
                ],
            ["id" => [ilDBConstants::T_INTEGER, $content_element->getId()]]
        ) === 1;
    }

    public function store(ContentElement $content_element): bool
    {
        if ($this->exists($content_element->getId())) {
            return $this->update($content_element);
        }

        return $this->create($content_element);
    }

    public function delete(ContentElement $content_element): bool
    {
        return $this->deleteById($content_element->getId());
    }

    public function deleteById(int $id): bool
    {
        return $this->db->manipulateF(
            "DELETE FROM " . self::TABLE_NAME . " WHERE id = %s",
            [ilDBConstants::T_INTEGER],
            [$id]
        ) === 1;
    }

    public function exists(int $id): bool
    {
        $result = $this->db->queryF(
            "SELECT EXISTS(SELECT 1 FROM " . self::TABLE_NAME . " WHERE id = %s) AS does_exist",
            [ilDBConstants::T_INTEGER],
            [$id]
        );

        return (bool) $this->db->fetchAssoc($result)["does_exist"];
    }

    private function map(array $row): ContentElement
    {
        return (new ContentElement(
            (int) $row["obj_id"],
            (string) $row["title"],
            (string) $row["description"],
            (int) $row["sort"],
            $row["udf_field"] ? (int) $row["udf_field"] : null,
            (bool) $row["is_separator"],
            (bool) $row["is_required"],
        ))->setId((int) $row["id"]);
    }
}
