<?php

declare(strict_types=1);

namespace WPZylos\Framework\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * PSR-3 compliant logger for WordPress.
 *
 * Writes to plugin-specific log files and optionally to error_log when WP_DEBUG is enabled.
 *
 * @package WPZylos\Framework\Logger
 */
class Logger implements LoggerInterface
{
    /**
     * @var ContextInterface Plugin context
     */
    private ContextInterface $context;

    /**
     * @var string|null Log file path
     */
    private ?string $logPath;

    /**
     * @var string Minimum log level
     */
    private string $minLevel;

    /**
     * @var bool Whether to use error_log
     */
    private bool $useErrorLog;

    /**
     * Log level priority mapping.
     */
    private const LEVELS = [
        LogLevel::DEBUG     => 0,
        LogLevel::INFO      => 1,
        LogLevel::NOTICE    => 2,
        LogLevel::WARNING   => 3,
        LogLevel::ERROR     => 4,
        LogLevel::CRITICAL  => 5,
        LogLevel::ALERT     => 6,
        LogLevel::EMERGENCY => 7,
    ];

    /**
     * Create logger instance.
     *
     * @param ContextInterface $context Plugin context
     * @param string|null $logPath Log file path (null for default)
     * @param string $minLevel Minimum level to log
     * @param bool $useErrorLog Also write to error_log when WP_DEBUG
     */
    public function __construct(
        ContextInterface $context,
        ?string $logPath = null,
        string $minLevel = LogLevel::DEBUG,
        bool $useErrorLog = true
    ) {
        $this->context     = $context;
        $this->logPath     = $logPath;
        $this->minLevel    = $minLevel;
        $this->useErrorLog = $useErrorLog;
    }

    /**
     * {@inheritDoc}
     */
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog($level)) {
            return;
        }

        $formatted = $this->format($level, (string) $message, $context);

        // Write to error_log if WP_DEBUG and enabled
        if ($this->useErrorLog && defined('WP_DEBUG') && WP_DEBUG) {
            error_log($formatted);
        }

        // Write to a file
        $this->writeToFile($formatted);
    }

    /**
     * Check if the level should be logged.
     *
     * @param string $level Log level
     *
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        $levelPriority = self::LEVELS[ $level ] ?? 0;
        $minPriority   = self::LEVELS[ $this->minLevel ] ?? 0;

        return $levelPriority >= $minPriority;
    }

    /**
     * Format log message.
     *
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context array
     *
     * @return string Formatted message
     */
    private function format(string $level, string $message, array $context): string
    {
        $message = $this->interpolate($message, $context);

        // Include exception stack trace if present
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $message .= PHP_EOL . $context['exception']->getTraceAsString();
        }

        return sprintf(
            "[%s] [%s] [%s] %s",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $this->context->slug(),
            $message
        );
    }

    /**
     * Interpolate context values into message placeholders.
     *
     * @param string $message Message with placeholders
     * @param array $context Context values
     *
     * @return string Interpolated message
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if ($key === 'exception') {
                continue;
            }

            if (is_string($val) || is_numeric($val) || ( is_object($val) && method_exists($val, '__toString') )) {
                $replace[ '{' . $key . '}' ] = (string) $val;
            } elseif (is_bool($val)) {
                $replace[ '{' . $key . '}' ] = $val ? 'true' : 'false';
            } elseif (is_null($val)) {
                $replace[ '{' . $key . '}' ] = 'null';
            }
        }

        return strtr($message, $replace);
    }

    /**
     * Write a message to a log file.
     *
     * @param string $message Formatted message
     *
     * @return void
     */
    private function writeToFile(string $message): void
    {
        $path = $this->getLogPath();
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            wp_mkdir_p($dir);

            // Protect the log directory
            $htaccess = $dir . '/.htaccess';
            if (! file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all\n");
            }

            $index = $dir . '/index.php';
            if (! file_exists($index)) {
                file_put_contents($index, "<?php // Silence is golden.\n");
            }
        }

        file_put_contents($path, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get a log file path.
     *
     * @return string Log file path
     */
    private function getLogPath(): string
    {
        if ($this->logPath !== null) {
            return $this->logPath;
        }

        $uploads = wp_upload_dir();

        return sprintf(
            '%s/%s/logs/%s.log',
            $uploads['basedir'],
            $this->context->slug(),
            date('Y-m-d')
        );
    }

    /**
     * Clean old log files.
     *
     * @param int $daysToKeep Number of days to retain logs
     *
     * @return int Number of files deleted
     */
    public function cleanOldLogs(int $daysToKeep = 30): int
    {
        $uploads = wp_upload_dir();
        $logDir  = $uploads['basedir'] . '/' . $this->context->slug() . '/logs';

        if (! is_dir($logDir)) {
            return 0;
        }

        $deleted = 0;
        $cutoff  = time() - ( $daysToKeep * 86400 );

        foreach (glob($logDir . '/*.log') as $file) {
            if (( filemtime($file) < $cutoff ) && unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
