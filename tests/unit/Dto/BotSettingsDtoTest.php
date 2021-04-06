<?php

namespace unit\Dto;

use App\Dto\BotSettingsDto;
use App\Dto\PuzzleSettingsDto;
use App\Dto\PuzzlesSettingsDto;
use PHPUnit\Framework\TestCase;

class BotSettingsDtoTest extends TestCase
{
    private const BOT_API_KEY = 'BOT_API_KEY';
    private const PUZZLE_REPLY_TIME_OUT = 'PUZZLE_REPLY_TIME_OUT';
    private const PUZZLE_REPLY_ATTEMPT_COUNT = 'PUZZLE_REPLY_ATTEMPT_COUNT';
    private const PUZZLE_SETTINGS = 'PUZZLE_SETTINGS';
    private const USER_NAME = 'USER_NAME';

    /**
     * @return array
     */
    public function goodConfigProvider(): array
    {
        $config = [
            'BOT_API_KEY' => 'SOME_KEY',
            'PUZZLE_REPLY_TIME_OUT' => 2,
            'PUZZLE_REPLY_ATTEMPT_COUNT' => 1,
            'PUZZLE_SETTINGS' => [
                'PUZZLE_TYPE' => 'riddles',
                'SETTINGS' => [
                    'first_numbers' => [
                        'MAX_CHOICES_COUNT' => 5,
                        'SETTINGS' => [],
                    ],
                    'random_numbers' => [
                        'MAX_CHOICES_COUNT' => 5,
                        'SETTINGS' => [],
                    ],
                    'simple_math' => [
                        'MAX_CHOICES_COUNT' => 5,
                        'SETTINGS' => [],
                    ],
                    'math' => [
                        'MAX_CHOICES_COUNT' => 5,
                        'SETTINGS' => [],
                    ],
                    'riddles' => [
                        'MAX_CHOICES_COUNT' => 4,
                        'SETTINGS' => [
                            'ромашка' => 'Стоит в саду кудряшка - белая рубашка, сердечко золотое',
                            'елка' => 'Зимой и летом одним цветом',
                            'гроза' => 'Сперва блеск, за блеском - Треск!',
                            'комар' => 'Не зверь, не птица, носок как спица, летит - пищит, сядет - молчит.',
                            'ежик' => 'Вот иголки и булавки выползают из-под лавки, на меня они глядят, молока они хотят',
                            'кошка' => 'Ночью не спит, мышей сторожит',
                            'снежинка' => 'Зимой - звезда, весной - вода',
                            'солнце' => 'Не пекарь, а печёт-румянит',
                            'морковь' => 'Красна девица сидит в темнице, а коса на улице',
                            'одуванчик' => 'На зелёной длинной ножке вырос шарик у дорожки',
                            'подсолнух' => 'Посадили зёрнышко - вырастили солнышко',
                            'заяц' => 'Косил косой косой косой',
                            'жираф' => 'Узнать его нам просто, узнать его легко: высокого он роста и видит далеко',
                            'собака' => 'Не говорит, не поет, а кто к хозяину идет она знать дает',
                            'дикобраз' => 'Ежик вырос в десять раз, получился ...',
                            'медведь' => 'Хозяин лесной, просыпается весной, а зимой, под вьюжный вой, спит в избушке снеговой',
                            'верблюд' => 'Зверь я горбатый, а нравлюсь ребятам',
                            'осел' => 'Сер, да не волк, длинноух, да не заяц, с копытами, да не лошадь',
                            'корова' => 'Сама пестрая, ест зеленое, дает белое',
                            'свинья' => 'Пятак есть, а ничего не купит',
                            'змея' => 'Вьется веревка, на конце - головка',
                        ],
                    ],
                ],
            ],
        ];

        return [
            [
                $config
            ]
        ];
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::getBotApiKey
     * @dataProvider goodConfigProvider
     */
    public function testSetGetBotApiKey(array $goodConfig): void
    {
        $botApiKeyValue = $goodConfig[self::BOT_API_KEY];

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setBotApiKey($botApiKeyValue);
        self::assertEquals($botApiKeyValue, $botSettingsDto->getBotApiKey());
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyTimeOut
     * @dataProvider goodConfigProvider
     */
    public function testSetGetPuzzleReplyTimeOut(array $goodConfig): void
    {
        $botApiKeyValue = $goodConfig[self::PUZZLE_REPLY_TIME_OUT];

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setPuzzleReplyTimeOut($botApiKeyValue);
        self::assertEquals($botApiKeyValue, $botSettingsDto->getPuzzleReplyTimeOut());
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setBotUserName
     * @covers \App\Dto\BotSettingsDto::getBotUserName
     */
    public function testSetGetBotUserName(): void
    {
        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setBotUserName(self::USER_NAME);
        self::assertEquals(self::USER_NAME, $botSettingsDto->getBotUserName());
    }

    /**
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::getPuzzleReplyAttemptCount
     * @dataProvider goodConfigProvider
     */
    public function testSetGetPuzzleReplyAttemptCount(array $goodConfig): void
    {
        $puzzleReplyAttemptCount = $goodConfig[self::PUZZLE_REPLY_ATTEMPT_COUNT];

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setPuzzleReplyAttemptCount($puzzleReplyAttemptCount);
        self::assertEquals($puzzleReplyAttemptCount, $botSettingsDto->getPuzzleReplyAttemptCount());
    }

    /**
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\BotSettingsDto::getPuzzlesSettings
     */
    public function testSetGetPuzzlesSettings(): void
    {
        $puzzlesSettings = $this->getMockPuzzlesSettingsDto();

        $botSettingsDto = new BotSettingsDto();
        $botSettingsDto->setPuzzlesSettings($puzzlesSettings);
        self::assertEquals($puzzlesSettings, $botSettingsDto->getPuzzlesSettings());
    }

    /**
     * @return PuzzlesSettingsDto
     */
    protected function getMockPuzzlesSettingsDto(): PuzzlesSettingsDto
    {
        $puzzleSettingsMock = $this->getMockPuzzleSettingsDto();

        return $this
            ->getMockBuilder(PuzzlesSettingsDto::class)
            ->setConstructorArgs(
                [
                    'puzzle_type',
                    $puzzleSettingsMock
                ]
            )
            ->getMock();
    }

    /**
     * @return PuzzleSettingsDto
     */
    protected function getMockPuzzleSettingsDto(): PuzzleSettingsDto
    {
        return $this
            ->getMockBuilder(PuzzleSettingsDto::class)
            ->setConstructorArgs(
                [
                    1,
                    []
                ]
            )
            ->getMock();
    }
}
