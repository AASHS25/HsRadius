<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('radius:check-expired')->hourly();
Schedule::command('radius:generate-invoices')->dailyAt('06:00');
Schedule::command('tenants:check-billing')->dailyAt('07:00');
