<?php

namespace Phpais\AiPlugin\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AiPluginBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}