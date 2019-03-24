<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

trait SectionedCommandTrait
{
    /**
     * @var ConsoleSectionOutput
     */
    protected $progressSection;

    /**
     * @var ConsoleSectionOutput
     */
    protected $stateSection;

    /**
     * Backup output if for some reasons ConsoleOutput is not used.
     *
     * @var OutputInterface
     */
    protected $output;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        // Set sections if $output is an instance of ConsoleOutput (which it almost always is
        if ($output instanceof ConsoleOutput) {
            $this->stateSection = $output->section();
            $this->progressSection = $output->section();
        }
    }

    protected function updateStateMessage(string $message)
    {
        $this->writeToSection($message, $this->stateSection);
    }

    protected function updateProgressOutput(string $message)
    {
        $this->writeToSection($message, $this->progressSection);
    }

    protected function writeToSection(string $message, ?ConsoleSectionOutput $section)
    {
        if ($section) {
            $section->clear();
            $section->overwrite($message);
        } else {
            $this->output->writeln($message);
        }
    }
}
