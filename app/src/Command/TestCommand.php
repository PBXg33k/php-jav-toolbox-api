<?php

namespace App\Command;

use Pbxg33k\MessagePack\DTO\InodeDTO;
use Pbxg33k\MessagePack\DTO\JavFileDTO;
use App\MessageHandler\PersistEntityMessageHandler;
use Pbxg33k\MessagePack\Message\PersistEntityMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'app:test';

    /**
     * @var PersistEntityMessageHandler
     */
    private $handler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PersistEntityMessageHandler $handler,
        LoggerInterface $logger,
        ?string $name = null
    ) {
        $this->handler = $handler;
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Add a short description for your command')
            ->addArgument('path',InputArgument::REQUIRED,'path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileInfo =  new \SplFileInfo($input->getArgument('path'));

        $inodeDTO = new InodeDTO();
        $inodeDTO->id = $fileInfo->getInode();
        $inodeDTO->filesize = $fileInfo->getSize();

        $testEntity = new JavFileDTO();
        $testEntity->inode = $inodeDTO;
        $testEntity->path = $fileInfo->getRealPath();


        $testMessage = new PersistEntityMessage($testEntity);

        $this->handler->__invoke($testMessage);
    }
}
