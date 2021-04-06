<?php
declare(strict_types=1);

namespace App;

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\Config;
use Noodlehaus\Parser\Php;
use SQLite3;
use TgBotApi\BotApiBase\ApiClient;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\BotApiNormalizer;

$logger = new Logger('bot');
$logger->pushHandler(new StreamHandler('var/bot.log', Logger::WARNING));

$config = new Config('config/parameters.php', new Php());
$botSettingsLoader = new BotSettingsLoader($config, $logger);
$botSettingsDto = $botSettingsLoader->getBotSettings();

$database = new SQLite3('var/db.sqlite', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

$requestFactory = new RequestFactory();
$streamFactory = new StreamFactory();
$client = new Client();
$apiClient = new ApiClient($requestFactory, $streamFactory, $client);
$botApi = new BotApi($botSettingsDto->getBotApiKey(), $apiClient, new BotApiNormalizer());

$botClient = new TelegramBotClient($botApi);

DatabaseService::init($database);
$updateManager = new UpdateManager();
$updateManager->run($botClient, $database, $logger, $botSettingsDto);
