<?php
declare(strict_types=1);

namespace App\Dto;

class PuzzleTaskDto
{
    /**
     * @var int
     */
    private $chatId;

    /**
     * @var string
     */
    private $answer;

    /**
     * @var int
     */
    private $taskMessageId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $attempt;

    /**
     * @param int $chatId
     * @param int $userId
     * @param string $answer
     * @param int $taskMessageId
     * @param int $attempt
     */
    public function __construct(int $chatId, int $userId, string $answer, int $taskMessageId, int $attempt)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->answer = $answer;
        $this->taskMessageId = $taskMessageId;
        $this->attempt = $attempt;
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
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }

    /**
     * @return int
     */
    public function getTaskMessageId(): int
    {
        return $this->taskMessageId;
    }

    /**
     * @return int
     */
    public function getAttempt(): int
    {
        return $this->attempt;
    }
}
