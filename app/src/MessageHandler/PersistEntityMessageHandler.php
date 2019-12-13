<?php
namespace App\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Pbxg33k\MessagePack\Message\PersistEntityMessage;
use phpDocumentor\Reflection\Types\Object_;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Entity;
use Pbxg33k\MessagePack\DTO;

class PersistEntityMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    private $classMap = [
        DTO\CompanyDTO::class => Entity\Company::class,
        DTO\CompanyRoleDTO::class => Entity\CompanyRole::class,
        DTO\ImageDTO::class => Entity\Image::class,
        DTO\InodeDTO::class => Entity\Inode::class,
        DTO\JavFileDTO::class => Entity\JavFile::class,
        DTO\ModelDTO::class => Entity\Model::class,
        DTO\TagDTO::class => Entity\Tag::class,
        DTO\TitleDTO::class => Entity\Title::class
    ];

    private $repositories = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(PersistEntityMessage $message)
    {
        $this->entityManager->persist($this->handleObject($message->getObject()));
    }

    /**
     * @param $object
     * @return object
     */
    private function handleObject($object)
    {
        if($entityClassName = $this->getEntityClass($object)) {

        }
    }

    /**
     * Compares and merges entity
     *
     * @param $entity
     */
    private function handleEntity($entity)
    {

    }

    private function getEntityFromDatabase($entity)
    {
        $className = get_class($entity);
        if(!isset($this->repositories[get_class($entity)])) {
            $this->repositories[get_class($entity)] = $this->entityManager->getRepository(get_class($entity));
        }
    }

    private function getEntityClass($object) {
        $sourceClassName = get_class($object);

        return array_key_exists($sourceClassName, $this->classMap)
            ? $this->classMap[$sourceClassName]
            : false;
    }
}
