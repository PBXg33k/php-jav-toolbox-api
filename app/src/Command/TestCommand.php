<?php

namespace App\Command;

use App\Entity\JavFile;
use App\Exception\PreProcessFileException;
use App\Model\JAVTitle;
use App\Repository\JavFileRepository;
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
        /** @var JavFileRepository $javFileRepository */
        $javFileRepository = $this->entityManager->getRepository(JavFile::class);

        $javfile = $javFileRepository->find(1);

        $javFileRepository->findOneByOrCreate($javfile);

//        $io = new SymfonyStyle($input, $output);
//        $sockSucces = fopen(__DIR__.'/../../var/success.csv', 'w');
//        $sockFail   = fopen(__DIR__.'/../../var/fail.csv', 'w');
//
//        include_once __DIR__.'/../../var/filenames.php';
//
//        /**
//         * @var $iv \SplFileInfo
//         */
//        $i = 1;
//        foreach($jav_file as $ik => $iv)
//        {
//            try {
//                /** @var JAVTitle $result */
//                $result = JAVProcessorService::extractIDFromFilename(pathinfo($iv['filename'], PATHINFO_FILENAME));
//
//                $doNotLog = [];
//
//                if (
//                !in_array($result->getParser(), $doNotLog)
////                && $result->getPart() !== NULL
//                ) {
////                    $io->success(sprintf(
////                        "(%d/%d)LABEL: %s  RELEASE: %d  PART: %d    \nRAW: %s\nCLEAN: %s\n%s",
////                        $i,
////                        count($jav_file),
////                        $result->getLabel(),
////                        $result->getRelease(),
////                        $result->getPart(),
////                        $iv['filename'],
////                        $result->getCleanName(),
////                        $result->getParser()
////                    ));
//
//                    fputcsv($sockSucces, [
//                        $iv['filename'],
//                        $result->getLabel(),
//                        $result->getRelease(),
//                        $result->getPart(),
//                        $result->getCleanName(),
//                        $result->getParser()
//                    ]);
//                }
//            } catch (PreProcessFileException $e) {
//                $mockParser = new Level3Parser();
//                $cleaned    = $mockParser->cleanUp($iv['filename']);
//                $csvLine = [
//                    $iv['filename'],
//                    $cleaned
//                ];
//                fputcsv($sockFail, $csvLine);
////                $io->error(sprintf(
////                    "RAW: %s\nCLEAN: %s\nERR: %s",
////                    $iv['filename'],
////                    $cleaned,
////                    $e->getMessage()
////                ));
//            }
//            $i++;
//        }
//
//        fclose($sockSucces);
//        fclose($sockFail);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
