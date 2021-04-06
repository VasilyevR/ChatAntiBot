<?php
declare(strict_types=1);

namespace unit;

use App\BotSettingsLoader;
use App\Dto\BotSettingsDto;
use Monolog\Logger;
use Noodlehaus\Parser\Php;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Noodlehaus\Config;
use Psr\Log\LoggerInterface;
use RuntimeException;

class BotSettingsLoaderTest extends TestCase
{
    private const BOT_API_KEY = 'BOT_API_KEY';
    private const PUZZLE_REPLY_TIME_OUT = 'PUZZLE_REPLY_TIME_OUT';
    private const PUZZLE_REPLY_ATTEMPT_COUNT = 'PUZZLE_REPLY_ATTEMPT_COUNT';
    private const PUZZLE_SETTINGS = 'PUZZLE_SETTINGS';

    /**
     * @var string[][]
     */
    protected $goodConfig;

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
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @covers \App\BotSettingsLoader::getPuzzleSettingsDto
     * @covers \App\Dto\BotSettingsDto::setBotApiKey
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyAttemptCount
     * @covers \App\Dto\BotSettingsDto::setPuzzleReplyTimeOut
     * @covers \App\Dto\BotSettingsDto::setPuzzlesSettings
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @dataProvider goodConfigProvider
     */
    public function testGetBotSettings(array $goodConfig): void
    {
        $config = $this->getMockConfig();

        $config->expects($this->exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, $goodConfig[self::BOT_API_KEY]],
                    [self::PUZZLE_REPLY_TIME_OUT, null, $goodConfig[self::PUZZLE_REPLY_TIME_OUT]],
                    [self::PUZZLE_REPLY_ATTEMPT_COUNT, null, $goodConfig[self::PUZZLE_REPLY_ATTEMPT_COUNT]],
                    [self::PUZZLE_SETTINGS, null, $goodConfig[self::PUZZLE_SETTINGS]]
                ]
            );

        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsDto = $botSettingsLoader->getBotSettings();
        $this->assertInstanceOf(BotSettingsDto::class, $botSettingsDto);
    }

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     */
    public function testGetBotSettingsWithoutApiKey(): void
    {
        $config = $this->getMockConfig();

        $config->expects($this->once())
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, null],
                ]
            );

        $this->expectException(RuntimeException::class);
        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsLoader->getBotSettings();
    }

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @dataProvider goodConfigProvider
     */
    public function testGetBotSettingsWithoutReplyTimeOut(array $goodConfig): void
    {
        $config = $this->getMockConfig();

        $config->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, $goodConfig[self::BOT_API_KEY]],
                    [self::PUZZLE_REPLY_TIME_OUT, null, null],
                ]
            );

        $this->expectException(RuntimeException::class);
        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsLoader->getBotSettings();
    }

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @dataProvider goodConfigProvider
     */
    public function testGetBotSettingsWithoutReplyTimeCount(array $goodConfig): void
    {
        $config = $this->getMockConfig();

        $config->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, $goodConfig[self::BOT_API_KEY]],
                    [self::PUZZLE_REPLY_TIME_OUT, null, $goodConfig[self::PUZZLE_REPLY_TIME_OUT]],
                    [self::PUZZLE_REPLY_ATTEMPT_COUNT, null, null],
                ]
            );

        $this->expectException(RuntimeException::class);
        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsLoader->getBotSettings();
    }

    /**
     * @covers \App\BotSettingsLoader::__construct
     * @covers \App\BotSettingsLoader::getBotSettings
     * @dataProvider goodConfigProvider
     */
    public function testGetBotSettingsWithoutPuzzleSettings(array $goodConfig): void
    {
        $config = $this->getMockConfig();

        $config->expects($this->exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [self::BOT_API_KEY, null, $goodConfig[self::BOT_API_KEY]],
                    [self::PUZZLE_REPLY_TIME_OUT, null, $goodConfig[self::PUZZLE_REPLY_TIME_OUT]],
                    [self::PUZZLE_REPLY_ATTEMPT_COUNT, null, $goodConfig[self::PUZZLE_REPLY_ATTEMPT_COUNT]],
                    [self::PUZZLE_SETTINGS, null, null]
                ]
            );

        $this->expectException(RuntimeException::class);
        $logger = $this->getMockLogger();
        $botSettingsLoader = new BotSettingsLoader($config, $logger);
        $botSettingsLoader->getBotSettings();
    }

    /**
     * @return Config|MockObject
     */
    protected function getMockConfig(): Config
    {
        return $this->getMockBuilder(Config::class)
                        ->setConstructorArgs(['config/parameters.php', new Php()])
                        ->setMethods(['get'])
                        ->getMock();
    }

    /**
     * @return LoggerInterface
     */
    protected function getMockLogger(): LoggerInterface
    {
        return $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['bot'])
            ->setMethods(['critical'])
            ->getMock();
    }
}
