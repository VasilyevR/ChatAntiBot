<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tests\DataProviders\GoodConfigProvider;

class GoodConfigTest extends TestCase
{
    /**
     * @coversNothing
     * @dataProvider Tests\DataProviders\GoodConfigProvider::getGoodConfig()
     */
    public function testGoodConfig(array $goodConfig): void
    {
        self::assertIsArray($goodConfig);
    }
}
