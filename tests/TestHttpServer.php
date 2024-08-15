<?php

declare(strict_types=1);

namespace Uzulla\ForLegacy\HttpRequest\Tests;

use RuntimeException;

class TestHttpServer
{
    const SERVER_HOST = '127.0.0.1';
    const SERVER_PORT = 8080;
    const SERVER_HOST_PORT = self::SERVER_HOST . ':' . self::SERVER_PORT;
    const PID_FILE_PATH = __DIR__ . '/server.pid';
    const PHP_INFO_SCRIPT = __DIR__ . "/php_info.php";
    /** @var int|null */
    public $pid = null;

    public function __construct()
    {
        // ポートが利用されていないか確認
        $test_connection = @fsockopen(
            self::SERVER_HOST,
            self::SERVER_PORT,
            $errno,
            $errstr,
            1);
        if ($test_connection) {
            fclose($test_connection);
            throw new RuntimeException("Port 8080 is already in use on 127.0.0.1.");
        }

        // 多重起動などしていないかチェック
        if (file_exists(self::PID_FILE_PATH)) {
            $pid = (int)file_get_contents(self::PID_FILE_PATH);
            if (posix_kill($pid, 0)) {
                // プロセスがしているので例外
                throw new RuntimeException("Test server is already running. please stop it first.");
            } else {
                unlink(self::PID_FILE_PATH);
            }
        }
    }

    public function start()
    {
        // コマンドをバックグラウンドで実行し、PIDファイルを作成
        $command = "php -S " . self::SERVER_HOST_PORT . " " . self::PHP_INFO_SCRIPT . " > /dev/null 2>&1 & echo $! > " . self::PID_FILE_PATH;
        exec($command);
        // wait for startup server.
        sleep(1);
        // PIDファイルからPIDを取得
        $this->pid = (int)file_get_contents(self::PID_FILE_PATH);
    }

    public function __destruct()
    {
        if (is_int($this->pid)) {
            if (!posix_kill($this->pid, 15/* SIGTERM */)) {
                echo "Failed to terminate server with PID {$this->pid}" . PHP_EOL;
            } else {
                sleep(1);//waif for server shutdown.
            }
        }

        if (file_exists(self::PID_FILE_PATH)) {
            unlink(self::PID_FILE_PATH);
        }
    }
}

