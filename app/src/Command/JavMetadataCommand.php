<?php

namespace App\Command;

use App\Entity\JavFile;
use App\Repository\JavFileRepository;
use App\Service\MediaProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class JavMetadataCommand extends Command
{
    protected static $defaultName = 'jav:metadata';

    /**
     * @var JavFileRepository
     */
    private $javFileRepository;

    /**
     * @var MediaProcessorService
     */
    private $mediaProcessorService;

    public function __construct(EntityManagerInterface $entityManager, MediaProcessorService $mediaProcessorService, string $name = null)
    {
        $this->javFileRepository = $entityManager->getRepository(JavFile::class);
        $this->mediaProcessorService = $mediaProcessorService;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Get metadata for file')
            ->addOption('path', 'p',InputOption::VALUE_OPTIONAL, 'Path to file')
            ->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'JavID file id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getOption('path');
        $id = $input->getOption('id');

        if(!$path && !$id) {
            $io->error('No path and id given. One of these two is required');
            return 1;
        }

        if($path) {
            $javFile = $this->javFileRepository->findOneByPath($path);
        } elseif($id) {
            $javFile = $this->javFileRepository->find($id);
        }

        if($javFile) {
            $this->mediaProcessorService->getMetadata($javFile);

            var_dump($javFile);die();

        } else {
            $io->error('Unable to find JavFile. Exiting');
            return 2;
        }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
