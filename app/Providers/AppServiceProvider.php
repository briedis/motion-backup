<?php

namespace App\Providers;

use App\Lib\FileBackup;
use ChrisWhite\B2\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function () {
            return new Client(env('B2_APPLICATION_KEY_ID'), env('B2_APPLICATION_KEY'));
        });

        $this->app->bind(FileBackup::class, function () {
            return new FileBackup(
                app(Client::class),
                env('SOURCE_DIR'),
                env('B2_BUCKET_ID')
            );
        });
    }
}
