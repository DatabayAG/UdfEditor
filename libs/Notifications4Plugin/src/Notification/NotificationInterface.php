<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Notification;

use ilDateTime;

interface NotificationInterface
{
    public const array DEFAULT_PARSER_OPTIONS
        = [
            "autoescape" => false
        ];


    public static function getTableName(): string;


    public function getCreatedAt(): ilDateTime;


    public function getDescription(): string;


    public function getId(): int;


    public function getName(): string;


    public function getParser(): string;


    /**
     * @return mixed
     */
    public function getParserOption(string $key);


    public function getParserOptions(): array;


    public function getSubject(?string $lang_key = null, bool $use_default_if_not_set = true): string;


    public function getSubjects(): array;


    public function getText(?string $lang_key = null, bool $use_default_if_not_set = true): string;


    public function getTexts(): array;


    public function getTitle(): string;


    public function getUpdatedAt(): ilDateTime;


    public function setCreatedAt(ilDateTime $created_at): void;


    public function setDescription(string $description): void;


    public function setId(int $id): void;


    public function setName(string $name): void;


    public function setParser(string $parser): void;


    /**
     * @param mixed $value
     */
    public function setParserOption(string $key, $value): void;


    public function setParserOptions(array $parser_options = self::DEFAULT_PARSER_OPTIONS): void;


    public function setSubject(string $subject, string $lang_key): void;


    public function setSubjects(array $subjects): void;


    public function setText(string $text, string $lang_key): void;


    public function setTexts(array $texts): void;


    public function setTitle(string $title): void;


    public function setUpdatedAt(ilDateTime $updated_at): void;
}
