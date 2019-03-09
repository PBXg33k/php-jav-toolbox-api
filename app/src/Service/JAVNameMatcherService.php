<?php

namespace App\Service;

use App\Entity\Inode;
use App\Entity\JavFile;
use App\Entity\Title;
use App\Exception\JavIDExtractionException;
use App\Model\JAVIDExtractionResult;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JAVNameMatcherService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var bool
     */
    private $flushRequired;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
    }

    public function extractIDFromFileInfo(\SplFileInfo $fileInfo): Title
    {
        return $this->resultToEntity($this->extractID($fileInfo));
    }

    private function resultToEntity(JAVIDExtractionResult $result): Title
    {
        $fileinfo = $result->getFileInfo();
        $catalogNumber = sprintf('%s-%s', strtoupper($result->getLabel()), $result->getRelease());
        $title = $this->entityManager->getRepository(Title::class)->findOneBy([
            'catalognumber' => $catalogNumber,
        ]);

        if (!$title) {
            $title = (new Title())
                ->setCatalognumber($catalogNumber);

            $this->persist($title);
        }

        $javFile = $this->entityManager->getRepository(JavFile::class)->findOneByFileInfo($result->getFileInfo());

        if (!$javFile) {
            $inode = $this->entityManager->getRepository(Inode::class)->find($fileinfo->getInode());
            if (!$inode) {
                $inode = (new Inode())
                    ->setId($fileinfo->getInode())
                    ->setFilesize($fileinfo->getSize());

                $this->persist($inode);
            }

            $javFile = (new JavFile())
                ->setFilename($fileinfo->getFilename())
                ->setPath($fileinfo->getPathname())
                ->setPart(($result->getPart()) ?: 1)
                ->setInode($inode);

            $title->addFile($javFile);

            $this->persist($javFile);
        }

        if ($this->flushRequired) {
            $this->entityManager->flush();
        }

        return $title;
    }

    private function persist($entity)
    {
        $this->entityManager->persist($entity);
        $this->flushRequired = true;
    }

    private function extractID(\SplFileInfo $fileInfo): JAVIDExtractionResult
    {
        $filename = $fileInfo->getFilename();
        $matchers = [
            FilenameParser\CustomMarozParser::class,
            FilenameParser\CustomParserHjd2048::class,
            FilenameParser\ProcessedFilenameParser::class,
            FilenameParser\Level1Parser::class,
            FilenameParser\Level2Parser::class,
            FilenameParser\Level3Parser::class,
            FilenameParser\Level5Parser::class,
            FilenameParser\Hack5Parser::class,
            FilenameParser\Level6Parser::class,
            FilenameParser\Level7Parser::class,
            FilenameParser\Level10Parser::class,
            FilenameParser\Level11Parser::class,
//            FilenameParser\Level40Parser::class,
            FilenameParser\CustomSkyParser::class,
            FilenameParser\Hack1Parser::class,
            FilenameParser\Hack2Parser::class,
            FilenameParser\Hack3Parser::class,
            FilenameParser\Hack4Parser::class,
            FilenameParser\Level12Parser::class,
        ];

        foreach ($matchers as $matcher) {
            /** @var FilenameParser\BaseParser $matcherInstance */
            $matcherInstance = new $matcher();

            if ($matcherInstance->hasMatch($filename)) {
                return ($matcherInstance->getParts())->setFileInfo($fileInfo);
            }
        }

        throw new JavIDExtractionException('', $fileInfo, $matchers);
    }
}
