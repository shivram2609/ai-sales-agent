<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:about', function () {
    $this->info('AI Sales Agent Laravel app is ready.');
});
Schedule::command('sending:process-queue')->everyMinute()->withoutOverlapping();
