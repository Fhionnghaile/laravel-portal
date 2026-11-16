<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Verify boldly.');
})->purpose('Display an inspiring quote');
