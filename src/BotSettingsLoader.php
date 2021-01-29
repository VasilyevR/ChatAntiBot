<?php
declare(strict_types=1);

namespace App;

use App\Dto\BotSettingsDto;
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
            $errorMessage = 'Please create $BOT_API_KEY variable in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $timeOutPuzzleReply = $config->get('TIME_OUT_PUZZLE_REPLY');
        if (empty($botApiKey)) {
            $errorMessage = 'Please create $TIME_OUT_PUZZLE_REPLY variable in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $puzzleType = $config->get('PUZZLE_TYPE');
        if (empty($botApiKey)) {
            $errorMessage = 'Please create $PUZZLE_TYPE variable in config/parameters.php';
            $this->logger->critical($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setBotApiKey($botApiKey);
        $botSettingsDto->setTimeOutPuzzleReply($timeOutPuzzleReply);
        $botSettingsDto->setPuzzleType($puzzleType);

        return $botSettingsDto;
    }
}
