<?php

namespace AlibabaCloud\Dara\Util;

/**
 * This is a console module.
 */
class Console
{
    private static $stderrStream = null;
    private static $stdoutStream = null;

    /**
     * Set custom stderr stream for testing
     */
    public static function setStderrStream($stream)
    {
        self::$stderrStream = $stream;
    }

    /**
     * Set custom stdout stream for testing
     */
    public static function setStdoutStream($stream)
    {
        self::$stdoutStream = $stream;
    }

    /**
     * Reset streams to default
     */
    public static function resetStreams()
    {
        self::$stderrStream = null;
        self::$stdoutStream = null;
    }

    /**
     * Console val with log level into stdout.
     *
     * @param string $val the printing string
     */
    public static function log($val)
    {
        self::output($val, 'LOG');
    }

    /**
     * Console val with info level into stdout.
     *
     * @param string $val the printing string
     */
    public static function info($val)
    {
        self::output($val, 'INFO');
    }

    /**
     * Console val with warning level into stdout.
     *
     * @param string $val the printing string
     */
    public static function warning($val)
    {
        self::output($val, 'WARNING');
    }

    /**
     * Console val with debug level into stdout.
     *
     * @param string $val the printing string
     */
    public static function debug($val)
    {
        self::output($val, 'DEBUG');
    }

    /**
     * Console val with error level into stderr.
     *
     * @param string $val the printing string
     */
    public static function error($val)
    {
        self::output($val, 'ERROR', true);
    }

    /**
     * Outputs the message to the appropriate output stream.
     *
     * @param string $val    The message to log
     * @param string $level  The log level label
     * @param bool   $stderr Whether to output to stderr
     */
    private static function output($val, $level, $stderr = false)
    {
        $prefix = sprintf("[%s] ", $level);
        $message = $prefix . $val . PHP_EOL;
        
        if ($stderr) {
            // 使用自定义 stderr 流或默认的 stderr
            if (self::$stderrStream !== null) {
                fwrite(self::$stderrStream, $message);
            } else {
                file_put_contents('php://stderr', $message);
            }
        } else {
            // 使用自定义 stdout 流或默认的 echo
            if (self::$stdoutStream !== null) {
                fwrite(self::$stdoutStream, $message);
            } else {
                echo $message;
            }
        }
    }
}
