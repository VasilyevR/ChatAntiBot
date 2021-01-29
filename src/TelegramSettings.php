<?php
declare(strict_types=1);

namespace App;

use SQLite3;

class TelegramSettings
{
    /**
     * @var SQLite3
     */
    private $database;

    public function __construct(SQLite3 $database)
    {
        $this->database = $database;
    }

    /**
     * @param int $updateId
     */
    public function setMessageOffset(int $updateId): void
    {
        $this->database->query(sprintf('REPLACE INTO telegram (id, offset) VALUES (1, %d)', $updateId));
    }

    /**
     * @return int|null
     */
    public function getMessageOffset(): ?int
    {
        $results = $this->database->query('SELECT offset FROM telegram');
        if (false === $results) {
            return null;
        }
        $row = $results->fetchArray();
        if (false === $row) {
            return null;
        }
        return $row['offset'];
    }
}
