<?php
declare(strict_types=1);

namespace App\UpdateProcessor;

use App\Dto\NewMemberTelegramUpdateDto;
use App\Dto\TelegramUpdateDtoInterface;
use App\Exception\BotClientResponseException;
use App\Puzzle\PuzzleFactory;
use App\PuzzleTask;
use App\TelegramBotClient;
use Psr\Log\LoggerInterface;
use SQLite3;

class NewMembersProcessor implements UpdateProcessorInterface
{
    /**
     * @var TelegramBotClient
     */
    private $botClient;

    /**
     * @var PuzzleTask
     */
    private $puzzleTaskService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TelegramBotClient $botClient
     * @param SQLite3 $database
     * @param LoggerInterface $logger
     */
    public function __construct(TelegramBotClient $botClient, SQLite3 $database, LoggerInterface $logger)
    {
        $this->botClient = $botClient;
        $this->puzzleTaskService = new PuzzleTask($database);
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     * @param NewMemberTelegramUpdateDto $updateDto
     */
    public function processUpdate(TelegramUpdateDtoInterface $updateDto, $botSettingsDto): void
    {
        $newChatMembers = $updateDto->getNewMembers();
        if (empty($newChatMembers)) {
            return;
        }
        $chatId = $updateDto->getChatId();
        foreach ($newChatMembers as $newChatMember) {
            if ($newChatMember->username === $botSettingsDto->getBotUserName()) {
                $this->sendInitInformation($chatId, $botSettingsDto->getTimeOutPuzzleReply());
                continue;
            }
            $newChatMemberId = $newChatMember->id;
            $existPuzzleTaskDto = $this->puzzleTaskService->getPuzzleTask($chatId, $newChatMemberId);
            if (null !== $existPuzzleTaskDto) {
                continue;
            }
            $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($botSettingsDto->getPuzzleType());
            $puzzleDto = $puzzleGenerator->generate();
            $this->puzzleTaskService->savePuzzleTask(
                $chatId,
                $newChatMemberId,
                $puzzleDto->getAnswer(),
                $updateDto->getMessageId()
            );
            try {
                $this->botClient->sendKeyboardMarkupMessage(
                    $chatId,
                    $updateDto->getMessageId(),
                    $puzzleDto->getQuestion(),
                    $puzzleDto->getChoices()
                );
            } catch (BotClientResponseException $e) {
                $this->logger->error(
                    'Error to send puzzle',
                    [
                        'chatId' => $chatId,
                        'messageId' => $updateDto->getMessageId(),
                        'errorCode' => $e->getCode(),
                        'error' => $e->getMessage()
                    ]
                );
                continue;
            }
            try {
                $this->botClient->muteUser($chatId, $newChatMemberId);
            } catch (BotClientResponseException $e) {
                $this->logger->error(
                    'Error to mute new member',
                    [
                        'chatId' => $chatId,
                        'userId' => $newChatMemberId,
                        'errorCode' => $e->getCode(),
                        'error' => $e->getMessage()
                    ]
                );
            }
        }
    }

    /**
     * @param int $chatId
     * @param int $timeOut
     */
    private function sendInitInformation(int $chatId, int $timeOut): void
    {
        $information = 'Здравствуйте! Я бот!
Я умею задавать входящим пользователям простые вопросы.
Тот кто не ответил на них правильно в течение %d минут вероятней всего бот.
Либо просто не захотел или не успел ответить.
Он будет удален мной из этого чата, но может быть добавлен любым админом.
Не забудьте меня сделать админом этого чата!';
        $text = sprintf($information, $timeOut);
        try {
            $this->botClient->sendChatMessage($chatId, $text);
        } catch (BotClientResponseException $e) {
            $this->logger->warning(
                'Warning to send init message',
                [
                    'chatId' => $chatId,
                    'errorCode' => $e->getCode(),
                    'error' => $e->getMessage()
                ]
            );
        }
    }
}
