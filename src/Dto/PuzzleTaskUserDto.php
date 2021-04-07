<?php
declare(strict_types=1);

namespace App\Dto;

class PuzzleTaskUserDto
{
    /**
     * @var int
     */
    private $chatId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $messageId;

    /**
     * @param int $chatId
     * @param int $userId
     * @param int $messageId
     */
    public function __construct(int $chatId, int $userId, int $messageId) {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->messageId = $messageId;
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
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }
}
