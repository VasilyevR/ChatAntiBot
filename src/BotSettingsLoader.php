<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
use App\Dto\PuzzleSettingsDto;
use App\Dto\PuzzlesSettingsDto;
use Noodlehaus\Config;
use Noodlehaus\Parser\Php;
use Psr\Log\LoggerInterface;
use RuntimeException;

class BotSettingsLoader
{
    /**
     * @var string
     */
    private $configFilePath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $configFilePath
     * @param LoggerInterface $logger
     */
    public function __construct(string $configFilePath, LoggerInterface $logger)
    {
        $this->configFilePath = $configFilePath;
        $this->logger = $logger;
    }

    /**
     * @return BotSettingsDto
     */
    public function getBotSettings(): BotSettingsDto
    {
        $config = new Config($this->configFilePath, new Php());

        $botApiKey = $config->get('BOT_API_KEY');
        if (empty($botApiKey)) {
            $errorMessage = 'Please create $BOT_API_KEY setting in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $puzzleReplyTimeOut = $config->get('PUZZLE_REPLY_TIME_OUT');
        if (empty($botApiKey)) {
            $errorMessage = 'Please create $PUZZLE_REPLY_TIME_OUT setting in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $puzzleReplyAttemptCount = $config->get('PUZZLE_REPLY_ATTEMPT_COUNT');
        if (empty($botApiKey)) {
            $errorMessage = 'Please create $PUZZLE_REPLY_ATTEMPT_COUNT setting in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $puzzleSettings = $config->get('PUZZLE_SETTINGS');
        if (empty($botApiKey)) {
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
