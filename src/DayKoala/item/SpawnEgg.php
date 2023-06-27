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

    protected string $entityTypeId = ':';
    protected int $legacyEntityId = 0;

    public function setLegacyEntityId(Int $id) : self{
        $this->entityTypeId = LegacyEntityIdToStringIdMap::getInstance()->legacyToString($this->legacyEntityId = $id) ?? ':';
        return $this;
    }

    protected function createEntity(Position $pos) : Entity{
        $nbt = CompoundTag::create()
        ->setString("id", $this->entityTypeId)
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

    public function onInteractBlock(Player $player, Block $replace, Block $clicked, Int $face, Vector3 $clickVector, Array &$returnedItems) : ItemUseResult{
        $tile = $player->getWorld()->getTile($clicked->getPosition());
        if($tile instanceof Spawner){
            if(
                $tile->getLegacyEntityId() === $this->legacyEntityId or
                !$player->hasPermission('sakuraspawners.change')
            ){
                return ItemUseResult::FAIL();
            }
            $tile->setEntityId($this->legacyEntityId);
        }else{
            $entity = $this->createEntity($clicked->getPosition());
            $entity->spawnToAll();
        }
        $this->pop();
        return ItemUseResult::SUCCESS();
    }

    public function onInteractEntity(Player $player, Entity $entity, Vector3 $click) : Bool{
        if(
            !$player->hasPermission('sakuraspawners.stack') or
            !$entity instanceof SpawnerEntity or 
            $entity->getLegacyNetworkId() !== $this->legacyEntityId
        ){
            return false;
        }
        $entity->addStackSize(1);
        $this->pop();
        return true;
    }

}