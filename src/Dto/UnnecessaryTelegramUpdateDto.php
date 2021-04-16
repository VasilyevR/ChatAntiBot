<?php
declare(strict_types=1);

namespace App\Dto;

use App\Enum\TelegramUpdateEnum;

class UnnecessaryTelegramUpdateDto implements TelegramUpdateDtoInterface
{
    /**
     * @var int
     */
    private $updateId;

    /**
     * @param int $updateId
     */
    public function __construct(int $updateId)
    {
        $this->updateId = $updateId;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return TelegramUpdateEnum::UNNECESSARY;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateId(): int
    {
        return $this->updateId;
    }
}
