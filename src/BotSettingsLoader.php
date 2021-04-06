<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use App\Dto\PuzzleSettingsDto;
use App\Dto\PuzzlesSettingsDto;
use Noodlehaus\Config;
use Psr\Log\LoggerInterface;
use RuntimeException;

class BotSettingsLoader
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(Config $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return BotSettingsDto
     */
    public function getBotSettings(): BotSettingsDto
    {
        $botApiKey = $this->config->get('BOT_API_KEY');
        if (empty($botApiKey)) {
            $errorMessage = 'Please create $BOT_API_KEY setting in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $puzzleReplyTimeOut = $this->config->get('PUZZLE_REPLY_TIME_OUT');
        if (empty($puzzleReplyTimeOut)) {
            $errorMessage = 'Please create $PUZZLE_REPLY_TIME_OUT setting in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $puzzleReplyAttemptCount = $this->config->get('PUZZLE_REPLY_ATTEMPT_COUNT');
        if (empty($puzzleReplyAttemptCount)) {
            $errorMessage = 'Please create $PUZZLE_REPLY_ATTEMPT_COUNT setting in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $puzzleSettings = $this->config->get('PUZZLE_SETTINGS');
        if (empty($puzzleSettings)) {
            $errorMessage = 'Please create $PUZZLE_SETTINGS settings in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }
        $puzzleSettingsDto = $this->getPuzzleSettingsDto($puzzleSettings);

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setBotApiKey($botApiKey);
        $botSettingsDto->setPuzzleReplyTimeOut($puzzleReplyTimeOut);
        $botSettingsDto->setPuzzleReplyAttemptCount($puzzleReplyAttemptCount);
        $botSettingsDto->setPuzzlesSettings($puzzleSettingsDto);

        return $botSettingsDto;
    }

    /**
     * @param string[] $puzzleSettings
     * @return PuzzlesSettingsDto
     */
    private function getPuzzleSettingsDto(array $puzzleSettings): PuzzlesSettingsDto
    {
        $puzzleType = $puzzleSettings['PUZZLE_TYPE'];
        $maxChoicesCount = (int)$puzzleSettings['SETTINGS'][$puzzleType]['MAX_CHOICES_COUNT'];
        $settings = (array)$puzzleSettings['SETTINGS'][$puzzleType]['SETTINGS'];
        $puzzleSettingsDto = new PuzzleSettingsDto($maxChoicesCount, $settings);

        return new PuzzlesSettingsDto($puzzleType, $puzzleSettingsDto);
    }
}
