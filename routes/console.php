<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('shippingbo:sync --products')->everySixHours();
Schedule::command('shippingbo:sync --orders')->hourly();
