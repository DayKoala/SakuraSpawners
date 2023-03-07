<?php

namespace DayKoala\block;

use DayKoala\SakuraSpawners;
use pocketmine\block\MonsterSpawner;
use pocketmine\block\Block;

use pocketmine\world\BlockTransaction;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

use pocketmine\math\Vector3;

use pocketmine\player\Player;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use DayKoala\block\tile\SpawnerTile;

class SpawnerBlock extends MonsterSpawner{

    protected string $entityTypeId = ":";
    protected int $legacyEntityId = 0;

    public function getMaxStackSize() : Int{
        return 1;
    }

    public function isAffectedBySilkTouch() : Bool{
        return true;
    }
    
    public function place(BlockTransaction $transaction, Item $item, Block $replace, Block $clicked, Int $face, Vector3 $click, ?Player $player = null) : Bool{
        $result = parent::place($transaction, $item, $replace, $clicked, $face, $click, $player);
        if($result){
           $this->entityTypeId = LegacyEntityIdToStringIdMap::getInstance()->legacyToString($this->legacyEntityId = $item->getMeta()) ?? ":";
        }
        return $result;
    }

    public function writeStateToWorld() : Void{
        parent::writeStateToWorld();

        $tile = $this->position->getWorld()->getTile($this->position);
        if($tile instanceof SpawnerTile){
           if($tile->getEntityId() !== ":"){
              $this->entityTypeId = $tile->getEntityId();
              $this->legacyEntityId = $tile->getLegacyEntityId();
           }else $tile->setEntityId($this->entityTypeId);
        }
    }

    public function onScheduledUpdate() : Void{
        $tile = $this->position->getWorld()->getTile($this->position);
        if($tile instanceof SpawnerTile and $tile->onUpdate()) $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    public function getSilkTouchDrops(Item $item) : Array{
        $tile = $this->position->getWorld()->getTile($this->position);
        return $tile instanceof SpawnerTile ? [ItemFactory::getInstance()->get(ItemIds::MONSTER_SPAWNER, $tile->getLegacyEntityId())] : [ItemFactory::getInstance()->get(ItemIds::MONSTER_SPAWNER, $this->legacyEntityId)];
    }

}