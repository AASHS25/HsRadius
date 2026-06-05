<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('radius:check-expired')->hourly();
Schedule::command('radius:generate-invoices')->dailyAt('06:00');
