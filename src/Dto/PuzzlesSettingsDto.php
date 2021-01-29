<?php
declare(strict_types=1);

namespace App\Dto;


class PuzzlesSettingsDto
{
    /**
     * @var string
     */
    private $puzzleType;

    /**
     * @var PuzzleSettingsDto
     */
    private $puzzleSettings;

    /**
     * @param string $puzzleType
     * @param PuzzleSettingsDto $puzzlesSettings
     */
    public function __construct(string $puzzleType, PuzzleSettingsDto $puzzlesSettings)
    {

        $this->puzzleType = $puzzleType;
        $this->puzzleSettings = $puzzlesSettings;
    }

    /**
     * @return string
     */
    public function getPuzzleType(): string
    {
        return $this->puzzleType;
    }

    /**
     * @return PuzzleSettingsDto
     */
    public function getPuzzleSettings(): PuzzleSettingsDto
    {
        return $this->puzzleSettings;
    }
}
