<?php
namespace App\Command;

use App\Entity\JavFile;
use Pbxg33k\MessagePack\Message\ScanFileMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CheckCommand extends Command
{
    use SectionedCommandTrait;
    use ProgressBarCommandTrait;

    protected static $defaultName = 'jav:check';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProgressBar
     */
    private $overallProgressBar;

    /**
     * @var ProgressBar
     */
    private $stepProgressBar;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        MessageBusInterface $messageBus,
        ?string $name = null
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->messageBus = $messageBus;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Generate jobs for unprocessed inodes directly. This command skips the scan');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initSections($input, $output);

        if($output instanceof ConsoleOutput) {
            $this->overallProgressBar = $this->initProgressBar(new ProgressBar($this->stateSection, 2));
            $this->stepProgressBar = $this->initProgressBar(new ProgressBar($this->progressSection, 100));

            $this->updateProgressBarWithMessage($this->overallProgressBar, 'Getting data from database');

            $uncheckedFiles = $this->entityManager->getRepository(JavFile::class)->findUnchecked();

            $this->stepProgressBar->setMaxSteps(count($uncheckedFiles));
            $this->updateProgressBarWithMessage($this->overallProgressBar, 'Dispatching jobs');

            /** @var JavFile $file */
            foreach ($uncheckedFiles as $file) {
                $finfo = new \SplFileInfo($file->getPath());
                if($finfo->isFile()) {
                    $this->messageBus->dispatch(new ScanFileMessage($finfo->getPathname(), ltrim($finfo->getPath(), ''), ltrim($finfo->getPathname(), '')));
                }
                $this->updateProgressBarWithMessage($this->stepProgressBar, $file->getFilename());
            }
        }
        $this->progressSection->overwrite('Finished');
    }
}