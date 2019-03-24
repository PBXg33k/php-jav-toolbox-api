<?php
namespace App\Command;

use Symfony\Component\Console\Helper\ProgressBar;

trait ProgressBarCommandTrait
{
    protected function updateProgressBarWithMessage(ProgressBar $progressBar, string $message, int $steps = 1): void
    {
        $progressBar->setMessage($message);
        $progressBar->advance($steps);
        $progressBar->display();
    }

    protected function initProgressBar(ProgressBar $progressBar): ProgressBar
    {
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% - %message%');
        $progressBar->setRedrawFrequency(100);
        $progressBar->display();

        return $progressBar;
    }
}