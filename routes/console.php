<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('shippingbo:poll-orders')->everyFiveMinutes();
Schedule::command('shippingbo:sync --products')->everySixHours();
Schedule::command('shippingbo:sync --orders')->hourly();
