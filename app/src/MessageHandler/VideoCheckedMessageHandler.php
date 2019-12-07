<?php


namespace App\MessageHandler;


use Pbxg33k\MessagePack\Message\CalculateFileHashesMessage;
use Pbxg33k\MessagePack\Message\GenerateThumbnailMessage;
use Pbxg33k\MessagePack\Message\VideoCheckedMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class VideoCheckedMessageHandler
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(VideoCheckedMessage $message)
    {
        if($message->isChecked() && $message->isConsistent()) {
            $this->messageBus->dispatch(new GenerateThumbnailMessage($message->getPath()));
            $this->messageBus->dispatch(new CalculateFileHashesMessage($message->getPath(), CalculateFileHashesMessage::HASH_XXHASH | CalculateFileHashesMessage::HASH_MD5));
        }
    }
}
