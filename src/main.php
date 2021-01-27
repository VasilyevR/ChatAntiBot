<?php
declare(strict_types=1);

namespace App;

include_once 'vendor/autoload.php';
require_once 'config/parameters.php';
require_once 'config/riddles.php';

use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use SQLite3;
use TgBotApi\BotApiBase\ApiClient;
use TgBotApi\BotApiBase\BotApi;
use TgBotApi\BotApiBase\BotApiNormalizer;

if (empty($BOT_API_KEY)) {
    echo 'Please create BOT_API_KEY constant in config/parameters.php';
    return;
}

if (empty($TIME_OUT_PUZZLE_REPLY)) {
    echo 'Please create TIME_OUT_PUZZLE_REPLY constant in config/parameters.php';
    return;
}

$database = new SQLite3('var/db.sqlite', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
$requestFactory = new RequestFactory();
$streamFactory = new StreamFactory();
$client = new Client();
$apiClient = new ApiClient($requestFactory, $streamFactory, $client);
$bot = new BotApi($BOT_API_KEY, $apiClient, new BotApiNormalizer());

DatabaseService::init($database);
$updateProcessor = new UpdateProcessor($bot, $database, $TIME_OUT_PUZZLE_REPLY);
$updateProcessor->run();
