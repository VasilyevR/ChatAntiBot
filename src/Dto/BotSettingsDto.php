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

}
