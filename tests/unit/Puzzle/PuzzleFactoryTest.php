<?php

namespace unit\Puzzle;

use App\Dto\PuzzleSettingsDto;
use App\Dto\PuzzlesSettingsDto;
use App\Enum\PuzzleTypeEnum;
use App\Puzzle\FirstNumbersPuzzleGenerator;
use App\Puzzle\MathPuzzleGenerator;
use App\Puzzle\PuzzleFactory;
use App\Puzzle\RandomNumbersPuzzleGenerator;
use App\Puzzle\RiddlePuzzleGenerator;
use App\Puzzle\SimpleMathPuzzleGenerator;
use PHPUnit\Framework\TestCase;

class PuzzleFactoryTest extends TestCase
{
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
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testGetFirstNumbersPuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::FIRST_NUMBERS,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(FirstNumbersPuzzleGenerator::class, $puzzleGenerator);
    }

    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testGetMathPuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::MATH,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(MathPuzzleGenerator::class, $puzzleGenerator);
    }

    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testRandomNumbersPuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::RANDOM_NUMBERS,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(RandomNumbersPuzzleGenerator::class, $puzzleGenerator);
    }

    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testSimpleMathPuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::SIMPLE_MATH,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(SimpleMathPuzzleGenerator::class, $puzzleGenerator);
    }

    /**
     * @covers \App\Puzzle\PuzzleFactory::getPuzzleGenerator
     * @covers \App\Puzzle\AbstractPuzzleGenerator::__construct
     * @covers \App\Dto\PuzzleSettingsDto::__construct
     * @covers \App\Dto\PuzzleSettingsDto::getMaxChoicesCount
     * @covers \App\Dto\PuzzleSettingsDto::getSettings
     * @covers \App\Dto\PuzzlesSettingsDto::__construct
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleSettings
     * @covers \App\Dto\PuzzlesSettingsDto::getPuzzleType
     */
    public function testRiddlePuzzleGenerator(): void
    {
        $puzzleSettings = new PuzzlesSettingsDto(
            PuzzleTypeEnum::RIDDLES,
            new PuzzleSettingsDto(4, [])
        );
        $puzzleGenerator = PuzzleFactory::getPuzzleGenerator($puzzleSettings);
        $this->assertInstanceOf(RiddlePuzzleGenerator::class, $puzzleGenerator);
    }
}
