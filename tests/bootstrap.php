<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Mock WordPress functions for unit testing
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p(string $path): bool
    {
        return @mkdir($path, 0755, true) || is_dir($path);
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir(): array
    {
        return [
            'basedir' => sys_get_temp_dir(),
            'baseurl' => 'http://example.com/wp-content/uploads',
        ];
    }
}
