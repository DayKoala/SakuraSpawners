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

namespace DayKoala\block;

use pocketmine\block\MonsterSpawner;
use pocketmine\block\Block;

use pocketmine\world\BlockTransaction;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;

use pocketmine\math\Vector3;

use pocketmine\player\Player;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use DayKoala\block\tile\Spawner;

use DayKoala\item\SakuraSpawnersItems;

class SpawnerBlock extends MonsterSpawner{

    protected string $entityTypeId = ':';
    protected int $legacyEntityId = 0;

    public function getMaxStackSize() : Int{ return 64; }

    public function isAffectedBySilkTouch() : Bool{ return true; }

    public function place(BlockTransaction $tx, Item $item, Block $replace, Block $clicked, Int $face, Vector3 $click, ?Player $player = null) : Bool{
        $this->setLegacyEntityId(SakuraSpawnersItems::getSpawnerEntityId($item));
        return parent::place($tx, $item, $replace, $clicked, $face, $click, $player);
    }

    public function setLegacyEntityId(Int $id) : self{
        $this->entityTypeId = LegacyEntityIdToStringIdMap::getInstance()->legacyToString($this->legacyEntityId = $id) ?? ':';
        return $this;
    }

    public function getLegacyEntityId() : Int{
        return $this->legacyEntityId;
    }

    public function onScheduledUpdate() : Void{
        $tile = $this->position->getWorld()->getTile($this->position);
        if(
            $tile instanceof Spawner and
            $tile->onUpdate()
        ) $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    public function readStateFromWorld() : Block{
        parent::readStateFromWorld();

        $tile = $this->position->getWorld()->getTile($this->position);

        if(
            $tile instanceof Spawner and 
            $tile->getEntityId() !== ':'
        ){
            $this->entityTypeId = $tile->getEntityId();
            $this->legacyEntityId = $tile->getLegacyEntityId();
        }

        return $this;
    }

    public function writeStateToWorld() : Void{
        parent::writeStateToWorld();

        $tile = $this->position->getWorld()->getTile($this->position);

        assert($tile instanceof Spawner);

        if($tile->getEntityId() == ':') $tile->setLegacyEntityId($this->legacyEntityId);
    }

    public function getSilkTouchDrops(Item $item) : Array{
        $id = ($tile = $this->position->getWorld()->getTile($this->position)) instanceof Spawner ? $tile->getLegacyEntityId() : $this->legacyEntityId;
        return [StringToItemParser::getInstance()->parse('52:'. $id) ?? SakuraSpawnersItems::MONSTER_SPAWNER()];
    }

}