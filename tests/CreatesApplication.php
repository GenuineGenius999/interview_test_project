<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $testEnvironment = [
            'APP_ENV' => 'testing',
            'BCRYPT_ROUNDS' => '4',
            'CACHE_DRIVER' => 'array',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => 'mysql',
            'DB_PORT' => '3306',
            'DB_DATABASE' => 'interview_test_testing',
            'DB_USERNAME' => 'interview_test',
            'DB_PASSWORD' => 'interview_test',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
        ];

        foreach ($testEnvironment as $name => $value) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
