<?php
declare(strict_types=1);

namespace App;

include_once 'vendor/autoload.php';
require_once 'config/parameters.php';
require_once 'config/riddles.php';

use App\Dto\BotSettingsDto;
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SQLite3;
use TgBotApi\BotApiBase\ApiClient;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\BotApiNormalizer;

$logger = new Logger('bot');
$logger->pushHandler(new StreamHandler('var/bot.log', Logger::WARNING));

if (empty($BOT_API_KEY)) {
    $logger->critical('Please create $BOT_API_KEY variable in config/parameters.php');
    return;
}

if (empty($TIME_OUT_PUZZLE_REPLY)) {
    $logger->critical('Please create $TIME_OUT_PUZZLE_REPLY variable in config/parameters.php');
    return;
}

if (empty($PUZZLE_TYPE)) {
    $logger->critical('Please create $PUZZLE_TYPE variable in config/parameters.php');
    return;
}

$botSettingsDto = new BotSettingsDto();
$botSettingsDto->setTimeOutPuzzleReply($TIME_OUT_PUZZLE_REPLY);
$botSettingsDto->setPuzzleType($PUZZLE_TYPE);

$database = new SQLite3('var/db.sqlite', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

$requestFactory = new RequestFactory();
$streamFactory = new StreamFactory();
$client = new Client();
$apiClient = new ApiClient($requestFactory, $streamFactory, $client);
$botApi = new BotApi($BOT_API_KEY, $apiClient, new BotApiNormalizer());

$botClient = new TelegramBotClient($botApi);

DatabaseService::init($database);
$updateProcessor = new UpdateProcessor();
$updateProcessor->run($botClient, $database, $logger, $botSettingsDto);
