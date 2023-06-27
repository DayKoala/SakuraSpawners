<?php

/*
 *   _____       __                    _____                                          
 *  / ___/____ _/ /____  ___________ _/ ___/____  ____ __      ______  ___  __________
 *  \__ \/ __ `/ //_/ / / / ___/ __ `/\__ \/ __ \/ __ `/ | /| / / __ \/ _ \/ ___/ ___/
 *  ___/ / /_/ / ,< / /_/ / /  / /_/ /___/ / /_/ / /_/ /| |/ |/ / / / /  __/ /  (__  ) 
 * /____/\__,_/_/|_|\__,_/_/   \__,_//____/ .___/\__,_/ |__/|__/_/ /_/\___/_/  /____/  
 *                                        /_/                                           
 *
 * This program is free software made for PocketMine-MP,
 * currently under the GNU Lesser General Public License published by
 * the Free Software Foundation, use according to the license terms.
 * 
 * @author DayKoala
 * @social https://twitter.com/DayKoala
 * @link https://github.com/DayKoala/SakuraSpawners
 * 
 * 
*/

namespace DayKoala;

use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\player\Player;

use pocketmine\item\ItemBlock;
use pocketmine\item\Pickaxe;
use pocketmine\item\StringToItemParser;

use pocketmine\block\MonsterSpawner;

use pocketmine\item\enchantment\VanillaEnchantments;

use DayKoala\entity\SpawnerEntity;

use DayKoala\block\SpawnerBlock;

use DayKoala\block\tile\Spawner;

use DayKoala\item\SakuraSpawnersItems;

final class SakuraSpawnersListener implements Listener{

    private static array $hitkill = [];

    public static function hasKiller(Player|String $player) : Bool{
        return isset(self::$hitkill[$player instanceof Player ? $player->getName() : $player]);
    }

    public static function addKiller(Player $player) : Void{
        self::$hitkill[$player->getName()] = $player;
    }

    public static function removeKiller(Player|String $player) : Void{
        if(isset(self::$hitkill[($player = $player instanceof Player ? $player->getName() : $player)])) unset(self::$hitkill[$player]);
    }

    public function onDamage(EntityDamageEvent $event){
        if(
            $event->isCancelled() or
            !$event instanceof EntityDamageByEntityEvent
        ){
            return;
        }
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        if(
            !$entity instanceof SpawnerEntity or 
            !$damager instanceof Player
        ){
            return;
        }
        if(self::hasKiller($damager)) $entity->kill();
    }

    public function onPlace(BlockPlaceEvent $event){
        if($event->isCancelled()){
            return;
        }
        $item = $event->getItem();
        if(!$item instanceof ItemBlock){
            return;
        }
        $block = $item->getBlock();
        if(
            !$block instanceof MonsterSpawner or
            $block instanceof SpawnerBlock
        ){
            return;
        }
        $transaction = $event->getTransaction();

        foreach($transaction->getBlocks() as [$x, $y, $z, $blocks]){
            $transaction->addBlock($blocks->getPosition(), SakuraSpawnersItems::MONSTER_SPAWNER()->setLegacyEntityId(SakuraSpawnersItems::getSpawnerEntityId($item)));
        }

    }

    public function onBreak(BlockBreakEvent $event){
        if($event->isCancelled()){
            return;
        }
        $item = $event->getItem();
        $tile = ($position = $event->getBlock()->getPosition())->getWorld()->getTile($position);
        if(
            !$tile instanceof Spawner or
            !$item instanceof Pickaxe or
            !$item->hasEnchantment(VanillaEnchantments::SILK_TOUCH())
        ){
            return;
        }
        $event->setDrops([StringToItemParser::getInstance()->parse('52:'. $tile->getLegacyEntityId()) ?? SakuraSpawnersItems::MONSTER_SPAWNER()->asItem()]);
    }

    public function onQuit(PlayerQuitEvent $event){
        if(self::hasKiller($player = $event->getPlayer())) self::removeKiller($player);
    }

}