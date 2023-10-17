<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomyScore;

use pocketmine\plugin\PluginBase;

final class BedrockEconomyScore extends PluginBase
{
    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }
}
