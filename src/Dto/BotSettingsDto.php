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
    private $puzzleReplyTimeOut;

    /**
     * @var int
     */
    private $puzzleReplyAttemptCount;

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
    public function getPuzzleReplyTimeOut(): int
    {
        return $this->puzzleReplyTimeOut;
    }

    /**
     * @return int
     */
    public function getPuzzleReplyAttemptCount(): int
    {
        return $this->puzzleReplyAttemptCount;
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
     * @param int $puzzleReplyTimeOut
     */
    public function setPuzzleReplyTimeOut(int $puzzleReplyTimeOut): void
    {
        $this->puzzleReplyTimeOut = $puzzleReplyTimeOut;
    }

    /**
     * @param int $puzzleReplyAttemptCount
     */
    public function setPuzzleReplyAttemptCount(int $puzzleReplyAttemptCount): void
    {
        $this->puzzleReplyAttemptCount = $puzzleReplyAttemptCount;
    }

    /**
     * @param PuzzlesSettingsDto $puzzlesSettingsDto
     */
    public function setPuzzlesSettings(PuzzlesSettingsDto $puzzlesSettingsDto): void
    {
        $this->puzzlesSettings = $puzzlesSettingsDto;
    }
}
