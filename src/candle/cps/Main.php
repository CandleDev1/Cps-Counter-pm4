<?php

namespace candle\cps;

use candle\cps\cps;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{

    public function onEnable(): void
    {
        $this->getLogger()->info('CPS enabled');
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getListeners();
    }


    public function getListeners()
    {
        $this->getServer()->getPluginManager()->registerEvents(new CPS(), $this);
    }

    public function onDisable(): void
    {
        $this->getLogger()->info('CPS disabled');
    }
}
