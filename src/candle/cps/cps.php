<?php

namespace candle\cps;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;

class cps implements Listener
{
    private static $clicks = [];

    public function addClick(Player $player): void
    {
        if(!isset(self::$clicks[$player->getName()]) || empty(self::$clicks[$player->getName()])){
            self::$clicks[$player->getName()][] = microtime(true);
        }else{
            array_unshift(self::$clicks[$player->getName()], microtime(true));
            if (count(self::$clicks[$player->getName()]) >= 100) {
                array_pop(self::$clicks[$player->getName()]);
            }
            $player->sendTip(TextFormat::RED . "CPS§f: " . TextFormat::RESET . self::getCps($player));
            if(self::$clicks === 1){
                $player->sendMessage('Your clicking above 1 please lower your cps.');
            }
            }
        }


    public function onPacketReceive(DataPacketReceiveEvent $event)
    {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof InventoryTransactionPacket) {
            if ($packet->trData->getTypeId() == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) {
                $this->addClick($event->getOrigin()->getPlayer());
            }
        }
        if ($packet instanceof LevelSoundEventPacket and $packet->sound == 42) {
            $this->addClick($player);
        }
        if ($event->getPacket()->pid() === AnimatePacket::NETWORK_ID) {
            $event->getOrigin()->getPlayer()->getServer()->broadcastPackets($event->getOrigin()->getPlayer()->getViewers(), [$event->getPacket()]);
            $event->cancel();
        }
    }
    public static function getCps(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1): float
    {
        if (empty(self::$clicks[$player->getName()])) {
            return 0.0;
        }
        $mt = microtime(true);
        return round(count(array_filter(self::$clicks[$player->getName()], static function (float $t) use ($deltaTime, $mt): bool {
                return ($mt - $t) <= $deltaTime;
            })) / $deltaTime, $roundPrecision);
    }


}
