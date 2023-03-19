<?php

namespace DayKoala\item;

use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;

use pocketmine\world\Position;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper as Helper;
use pocketmine\entity\EntityFactory;

use pocketmine\player\Player;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use DayKoala\entity\SpawnerEntity;

use DayKoala\block\tile\Spawner;

class SpawnEgg extends Item{

    protected function createEntity(Position $pos) : Entity{
        $nbt = CompoundTag::create()
        ->setString("id", LegacyEntityIdToStringIdMap::getInstance()->legacyToString($this->getMeta()) ?? ":")
        ->setTag("Pos", new ListTag([
            new DoubleTag($pos->x + 0.5),
            new DoubleTag($pos->y + 1.2),
            new DoubleTag($pos->z + 0.5)
        ]))
        ->setTag("Rotation", new ListTag([
            new FloatTag(lcg_value() * 360),
            new FloatTag(0.0)
        ]));
        return EntityFactory::getInstance()->createFromData($pos->getWorld(), $nbt) ?? new SpawnerEntity(Helper::parseLocation($nbt, $pos->getWorld()), $nbt);
    }

    public function onInteractBlock(Player $player, Block $replace, Block $clicked, Int $face, Vector3 $click) : ItemUseResult{
        $tile = $player->getWorld()->getTile($clicked->getPosition());
        if($tile instanceof Spawner){
           if($tile->getLegacyEntityId() === $this->getMeta()){
              return ItemUseResult::FAIL();
           }
           $tile->setEntityId(LegacyEntityIdToStringIdMap::getInstance()->legacyToString($this->getMeta()) ?? ":");
        }else{
           $entity = $this->createEntity($clicked->getPosition());
           $entity->spawnToAll();
        }
        $this->pop();
        return ItemUseResult::SUCCESS();
    }

    public function onInteractEntity(Player $player, Entity $entity, Vector3 $click) : Bool{
        if(!$entity instanceof SpawnerEntity or $entity->getModifiedLegacyNetworkTypeId() !== $this->getMeta()){
           return false;
        }
        $entity->addStackSize(1);
        $this->pop();
        return true;
    }

}