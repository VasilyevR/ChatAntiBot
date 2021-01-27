<?php
declare(strict_types=1);

namespace App;

use SQLite3;

class ChatSettings
{
    /**
     * @var SQLite3
     */
    private $database;

    /**
     * @param SQLite3 $database
     */
    public function __construct(SQLite3 $database)
    {
        $this->database = $database;
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function getPuzzleType(int $id): ?string
    {
        $results = $this->database->query(sprintf('SELECT type FROM chat_puzzle_type WHERE chat_id = %d', $id));
        if (false === $results) {
            return null;
        }
        $row = $results->fetchArray();
        if (false === $row) {
            return null;
        }
        return $row['type'];
    }

    //TODO: Add bot command to change puzzle type for chat
    /**
     * @param int $id
     * @param string $type
     */
    public function setPuzzleType(int $id, string $type): void
    {
        $this->database->query(
            sprintf(
                'INSERT OR REPLACE INTO chat_puzzle_type (chat_id, type) VALUES (%d, %s)',
                $id,
                $type
            )
        );
    }
}
