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

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\item\Pickaxe;
use pocketmine\item\StringToItemParser;
use pocketmine\item\ItemBlock;

use pocketmine\item\enchantment\VanillaEnchantments;

use pocketmine\block\MonsterSpawner;

use DayKoala\block\tile\StackableSpawner;
use DayKoala\block\tile\Spawner;

use DayKoala\entity\GlobalEntitySelector;

use DayKoala\item\SpawnerItems;
use DayKoala\item\SpawnerItemsManager;

use DayKoala\block\SpawnerBlock;

final class SakuraSpawnersListener implements Listener{

    public function onInteract(PlayerInteractEvent $event) : void{
        if(
            $event->isCancelled() or
            $event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK
        ){
            return;
        }
        $item = $event->getItem();
        $position = $event->getBlock()->getPosition();
        $tile = $position->getWorld()->getTile($position);
        if(
            !$tile instanceof StackableSpawner or
            $tile->hasMaxStackSize() or
            $tile->getEntityId() !== $item->getNamedTag()->getString(GlobalEntitySelector::TAG_ENTITY_ID, ":")
        ){
            return;
        }
        $item->pop();
        $tile->addStackSize(1);
        $event->getPlayer()->getInventory()->setItemInHand($item);
        $event->cancel();
    }

    public function onBreak(BlockBreakEvent $event) : void{
        if($event->isCancelled()){
            return;
        }
        $item = $event->getItem();
        $position = $event->getBlock()->getPosition();
        $tile = $position->getWorld()->getTile($position);
        if(
            !$tile instanceof Spawner or 
            !$item instanceof Pickaxe or 
            !$item->hasEnchantment(VanillaEnchantments::SILK_TOUCH())
        ){
            return;
        }
        $drops = StringToItemParser::getInstance()->parse("52:". $tile->getEntityId()) ?? SpawnerItemsManager::writeEntityId(SpawnerItems::SPAWNER(), $tile->getEntityId());
        $event->setDrops([$tile instanceof StackableSpawner ? $drops->setCount($tile->getStackSize()) : $drops]);
    }

    public function onPlace(BlockPlaceEvent $event) : void{
        if($event->isCancelled()){
            return;
        }
        $item = $event->getItem();
        $block = $item?->getBlock();
        if(
            !$item instanceof ItemBlock or 
            !$block instanceof MonsterSpawner or 
            $block instanceof SpawnerBlock
        ){
            return;
        }
        $entityId = $item->getNamedTag()->getString(GlobalEntitySelector::TAG_ENTITY_ID, ":");
        $transaction = $event->getTransaction();
        foreach($transaction->getBlocks() as [$x, $y, $z, $blockTarget]){
            if(!$blockTarget instanceof MonsterSpawner){
                continue;
            }
            $transaction->addBlock($blockTarget->getPosition(), SpawnerItems::MONSTER_SPAWNER()->setEntityId($entityId));
        }
    }

}