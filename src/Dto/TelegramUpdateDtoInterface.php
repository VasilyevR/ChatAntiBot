<?php
declare(strict_types=1);

namespace App\Dto;

interface TelegramUpdateDtoInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return int
     */
    public function getUpdateId(): int;
}
