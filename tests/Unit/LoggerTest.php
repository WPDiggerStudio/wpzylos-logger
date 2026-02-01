<?php

declare(strict_types=1);

namespace WPZylos\Framework\Logger\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use WPZylos\Framework\Core\Contracts\ContextInterface;
use WPZylos\Framework\Logger\Logger;

/**
 * Tests for Logger class.
 */
class LoggerTest extends TestCase
{
    private ContextInterface $context;
    private string $logPath;

    protected function setUp(): void
    {
        $this->logPath = sys_get_temp_dir() . '/wpzylos-test-' . uniqid() . '.log';

        $this->context = $this->createMock(ContextInterface::class);
        $this->context->method('slug')->willReturn('test-plugin');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logPath)) {
            unlink($this->logPath);
        }
    }

    public function testDebugLogsMessage(): void
    {
        $logger = new Logger($this->context, $this->logPath, LogLevel::DEBUG, false);

        $logger->debug('Test debug message');

        $this->assertFileExists($this->logPath);
        $content = file_get_contents($this->logPath);
        $this->assertStringContainsString('[DEBUG]', $content);
        $this->assertStringContainsString('Test debug message', $content);
        $this->assertStringContainsString('[test-plugin]', $content);
    }

    public function testInfoLogsMessage(): void
    {
        $logger = new Logger($this->context, $this->logPath, LogLevel::DEBUG, false);

        $logger->info('Test info message');

        $content = file_get_contents($this->logPath);
        $this->assertStringContainsString('[INFO]', $content);
    }

    public function testWarningLogsMessage(): void
    {
        $logger = new Logger($this->context, $this->logPath, LogLevel::DEBUG, false);

        $logger->warning('Test warning');

        $content = file_get_contents($this->logPath);
        $this->assertStringContainsString('[WARNING]', $content);
    }

    public function testErrorLogsMessage(): void
    {
        $logger = new Logger($this->context, $this->logPath, LogLevel::DEBUG, false);

        $logger->error('Test error');

        $content = file_get_contents($this->logPath);
        $this->assertStringContainsString('[ERROR]', $content);
    }

    public function testMinLevelFiltersLogs(): void
    {
        $logger = new Logger($this->context, $this->logPath, LogLevel::WARNING, false);

        $logger->debug('Should not appear');
        $logger->info('Should not appear');
        $logger->warning('Should appear');

        $content = file_get_contents($this->logPath);
        $this->assertStringNotContainsString('[DEBUG]', $content);
        $this->assertStringNotContainsString('[INFO]', $content);
        $this->assertStringContainsString('[WARNING]', $content);
    }

    public function testContextInterpolation(): void
    {
        $logger = new Logger($this->context, $this->logPath, LogLevel::DEBUG, false);

        $logger->info('User {username} logged in from {ip}', [
            'username' => 'john',
            'ip' => '192.168.1.1',
        ]);

        $content = file_get_contents($this->logPath);
        $this->assertStringContainsString('User john logged in from 192.168.1.1', $content);
    }

    public function testBooleanContextValues(): void
    {
        $logger = new Logger($this->context, $this->logPath, LogLevel::DEBUG, false);

        $logger->info('Active: {active}', ['active' => true]);

        $content = file_get_contents($this->logPath);
        $this->assertStringContainsString('Active: true', $content);
    }

    public function testImplementsPsr3Interface(): void
    {
        $logger = new Logger($this->context, $this->logPath);

        $this->assertInstanceOf(\Psr\Log\LoggerInterface::class, $logger);
    }
}
