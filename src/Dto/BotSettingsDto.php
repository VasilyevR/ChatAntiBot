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
     * @var string
     */
    private $welcomeMessage;

    /**
     * @var string
     */
    private $introMessage;

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
     * @return string
     */
    public function getWelcomeMessage(): string
    {
        return $this->welcomeMessage;
    }

    /**
     * @return string
     */
    public function getIntroMessage(): string
    {
        return $this->introMessage;
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
     * @param string $welcomeMessage
     */
    public function setWelcomeMessage(string $welcomeMessage): void
    {
        $this->welcomeMessage = $welcomeMessage;
    }

    /**
     * @param string $introMessage
     */
    public function setIntroMessage(string $introMessage): void
    {
        $this->introMessage = $introMessage;
    }

    /**
     * @param PuzzlesSettingsDto $puzzlesSettingsDto
     */
    public function setPuzzlesSettings(PuzzlesSettingsDto $puzzlesSettingsDto): void
    {
        $this->puzzlesSettings = $puzzlesSettingsDto;
    }
}
