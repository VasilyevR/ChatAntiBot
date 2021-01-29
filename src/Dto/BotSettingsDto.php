<?php
declare(strict_types=1);

namespace App\Dto;

class BotSettingsDto
{
    /**
     * @var string
     */
    private $botUserName;

    /**
     * @var int
     */
    private $timeOutPuzzleReply;

    /**
     * @var string
     */
    private $puzzleType;

    /**
     * @return string
     */
    public function getBotUserName(): string
    {
        return $this->botUserName;
    }

    /**
     * @return int
     */
    public function getTimeOutPuzzleReply(): int
    {
        return $this->timeOutPuzzleReply;
    }

    /**
     * @return string
     */
    public function getPuzzleType(): string
    {
        return $this->puzzleType;
    }

    /**
     * @param string $botUserName
     */
    public function setBotUserName(string $botUserName): void
    {
        $this->botUserName = $botUserName;
    }

    /**
     * @param int $timeOutPuzzleReply
     */
    public function setTimeOutPuzzleReply(int $timeOutPuzzleReply): void
    {
        $this->timeOutPuzzleReply = $timeOutPuzzleReply;
    }

    /**
     * @param string $puzzleType
     */
    public function setPuzzleType(string $puzzleType): void
    {
        $this->puzzleType = $puzzleType;
    }
}
