<?php
declare(strict_types=1);

namespace App;

use SQLite3;

class DatabaseService
{
    /**
     * @param SQLite3 $database
     */
    public static function init(SQLite3 $database): void
    {
        $tables = $database->query("SELECT name FROM sqlite_master WHERE type='table' AND name='telegram';");
        $tableNotExists = empty($tables->fetchArray());
        if (!$tableNotExists) {
            return;
        }
        $database->exec(
            'CREATE TABLE telegram (id INTEGER, offset INTEGER)'
        );
        $database->exec(
            'CREATE UNIQUE INDEX telegram_id_uindex ON telegram (id)'
        );
        $database->exec(
            'CREATE TABLE puzzle_task (chat_id INTEGER, user_id INTEGER, answer STRING, message_id STRING, puzzle_id STRING, attempt INTEGER, date_time INTEGER)'
        );
        $database->exec(
            'CREATE UNIQUE INDEX puzzle_task_chat_id_user_id_uindex ON puzzle_task (chat_id, user_id);'
        );
    }
}
