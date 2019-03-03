<?php

namespace App\MessageHandler;

use App\Message\FooMessage;

class FooMessageHandler
{
    public function __invoke(FooMessage $message)
    {
        return;
    }
}
