<?php

protected function schedule(Schedule $schedule)
{
    $schedule->command('posts:generate')->dailyAt('00:00'); // Runs daily at midnight
}
