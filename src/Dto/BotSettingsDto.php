<?php
declare(strict_types=1);

namespace App\Dto;

class BotSettingsDto
{
    /**
     * @var string
     */
    private $botApiKey;

    /**
     * @var string
     */
    private $botUserName;

    /**
     * @var int
     */
    private $timeOutPuzzleReply;

    /**
     * @var PuzzlesSettingsDto
     */
    private $puzzlesSettings;

    /**
     * @return string
     */
    public function getBotApiKey(): string
    {
        return $this->botApiKey;
    }

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
     * @return PuzzlesSettingsDto
     */
    public function getPuzzlesSettings(): PuzzlesSettingsDto
    {
        return $this->puzzlesSettings;
    }

    /**
     * @param string $botApiKey
     */
    public function setBotApiKey(string $botApiKey): void
    {
        $this->botApiKey = $botApiKey;
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
     * @param PuzzlesSettingsDto $puzzlesSettingsDto
     */
    public function setPuzzlesSettings(PuzzlesSettingsDto $puzzlesSettingsDto): void
    {
        $this->puzzlesSettings = $puzzlesSettingsDto;
    }
}
