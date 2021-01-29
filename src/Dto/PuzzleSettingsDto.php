<?php
declare(strict_types=1);

namespace App\Dto;


class PuzzleSettingsDto
{
    /**
     * @var int
     */
    private $maxChoicesCount;

    /**
     * @var string[]
     */
    private $settings;

    /**
     * @param int $maxChoicesCount
     * @param string[] $settings
     */
    public function __construct(int $maxChoicesCount, array $settings)
    {
        $this->maxChoicesCount = $maxChoicesCount;
        $this->settings = $settings;
    }

    /**
     * @return int
     */
    public function getMaxChoicesCount(): int
    {
        return $this->maxChoicesCount;
    }

    /**
     * @return string[]
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}