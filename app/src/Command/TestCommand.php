<?php

namespace App\Command;

use App\Entity\JavFile;
use App\Model\JAVTitle;
use App\Service\FilenameParser\Hack5Parser;
use App\Service\FilenameParser\Level10Parser;
use App\Service\FilenameParser\Level11Parser;
use App\Service\FilenameParser\Level3Parser;
use App\Service\FilenameParser\CustomParserHjd2048;
use App\Service\FilenameParser\CustomMarozParser;
use App\Service\FilenameParser\Level1Parser;
use App\Service\FilenameParser\Level4Parser;
use App\Service\FilenameParser\Level5Parser;
use App\Service\FilenameParser\ProcessedFilenameParser;
use App\Service\FilenameParser\Level2Parser;
use App\Service\FilenameParser\CustomSkyParser;
use App\Service\JAVProcessorService;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    protected static $defaultName = 'app:test';

    /**
     * @var MediaProcessorService
     */
    private $mediaProcessorService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        MediaProcessorService $mediaProcessorService,
        EntityManagerInterface $entityManager,
        ?string $name = null
    )
    {
        $this->mediaProcessorService = $mediaProcessorService;
        $this->entityManager         = $entityManager;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Add a short description for your command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        include_once __DIR__.'/../../var/filenames.php';

        /**
         * @var $iv \SplFileInfo
         */
        $i = 1;
        foreach($jav_file as $ik => $iv)
        {
            /** @var JAVTitle $result */
            $result = JAVProcessorService::extractIDFromFilename(pathinfo($iv['filename'], PATHINFO_FILENAME));

            $doNotLog = [
//                ProcessedFilenameParser::class,
//                Level1Parser::class,
//                Level2Parser::class,
//                Level4Parser::class,
//                Level5Parser::class,
//                Hack5Parser::class,
//                CustomParserHjd2048::class
            ];

            if(
                !in_array($result->getParser(), $doNotLog)
//                && $result->getPart() !== NULL
            ) {
//            if($result->getParser() === Level30Parser::class) {
                $io->success(sprintf(
                    "(%d/%d)LABEL: %s  RELEASE: %d  PART: %d    \nRAW: %s\nCLEAN: %s\n%s",
                    $i,
                    count($jav_file),
                    $result->getLabel(),
                    $result->getRelease(),
                    $result->getPart(),
                    $iv['filename'],
                    $result->getCleanName(),
                    $result->getParser()
                ));
            }
            $i++;
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
