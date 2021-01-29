<?php
declare(strict_types=1);

namespace App\Dto;

use App\Enum\TelegramUpdateEnum;

class NewMemberTelegramUpdateDto implements TelegramUpdateDtoInterface
{
    /**
     * @var int
     */
    private $updateId;

    /**
     * @var int
     */
    private $chatId;

    /**
     * @var int
     */
    private $messageId;

    /**
     * @var UserDto[]
     */
    private $newMembers;

    /**
     * @param int $updateId
     * @param int $chatId
     * @param int $messageId
     * @param UserDto[] $newMembers
     */
    public function __construct(int $updateId, int $chatId, int $messageId, array $newMembers)
    {
        $this->updateId = $updateId;
        $this->chatId = $chatId;
        $this->messageId = $messageId;
        $this->newMembers = $newMembers;
    }

    /**
     * @return int
     */
    public function getUpdateId(): int
    {
        return $this->updateId;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return TelegramUpdateEnum::NEW_MEMBER;
    }

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->chatId;
    }

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * @return UserDto[]
     */
    public function getNewMembers(): array
    {
        return $this->newMembers;
    }
}
