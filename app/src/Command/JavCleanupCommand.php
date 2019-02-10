<?php

namespace App\Command;

use App\Entity\Title;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class JavCleanupCommand extends Command
{
    protected static $defaultName = 'jav:cleanup';

    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ?string $name = null
    )
    {
        $this->entityManager = $entityManager;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Will cleanup')
//            ->addOption('no-interaction', null, InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL, 'Require no user interaction, implicit yes to all')
            ->addOption('dry-run', null, InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL, 'Only print actions, do not execute')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $brokenTitles = $this->entityManager->getRepository(Title::class)->findWithBrokenFiles();

        if($brokenTitles) {
            $table = new Table($output);
            $table->setHeaders([
                'CatalogID',
                'Inode',
                'Part',
                'Filesize',
                'Filename',
            ]);

            $collectiveSize = 0;

            /** @var Title $title */
            foreach($brokenTitles as $title) {
                foreach($title->getFiles() as $file) {
                    $tableRow = [
                        'catalog-id' => $title->getCatalognumber(),
                        'inode'      => $file->getInode()->getId(),
                        'part'       => $file->getPart(),
                        'filesize'   => $file->getInode()->getFilesize(),
                        'filename'   => $file->getFilename()
                    ];

                    $table->addRow($tableRow);

                    $collectiveSize += $file->getInode()->getFilesize();
                }
            }

            $table->setFooterTitle(sprintf('Titles %d  Size %d bytes', count($brokenTitles), $collectiveSize));
            $table->render();
        } else {
            $io->success('No broken titles found');
        }



//        if ($input->getOption('option1')) {
//
//        }
    }
}
