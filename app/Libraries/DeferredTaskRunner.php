<?php

namespace App\Libraries;

class DeferredTaskRunner
{
    /** @var array<int, array{task: callable, context: string}> */
    private static array $tasks = [];
    private static bool $registered = false;
    private static bool $running = false;

    public static function enqueue(callable $task, string $context = 'deferred task'): void
    {
        self::$tasks[] = [
            'task' => $task,
            'context' => $context,
        ];

        if (self::$registered) {
            return;
        }

        self::$registered = true;
        register_shutdown_function([self::class, 'run']);
    }

    public static function run(): void
    {
        if (self::$running || self::$tasks === []) {
            return;
        }

        self::$running = true;
        self::releaseClientConnection();

        foreach (self::$tasks as $entry) {
            try {
                ($entry['task'])();
            } catch (\Throwable $e) {
                log_message('error', $entry['context'] . ' failed: ' . $e->getMessage());
            }
        }

        self::$tasks = [];
        self::$running = false;
    }

    private static function releaseClientConnection(): void
    {
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }

        if (function_exists('session_write_close')) {
            @session_write_close();
        }

        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
            return;
        }

        @flush();
    }
}
