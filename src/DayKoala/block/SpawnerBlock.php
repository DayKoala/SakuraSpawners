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

use pocketmine\math\Vector3;

use pocketmine\player\Player;

use DayKoala\entity\GlobalEntitySelector;

use DayKoala\block\tile\Spawner;

class SpawnerBlock extends MonsterSpawner{

    protected string $entityId = ":";

    public function getMaxStackSize() : int{
        return 64;
    }

    public function isAffectedBySilkTouch() : bool{
        return true;
    }

    public function getEntityId() : string{
        return $this->entityId;
    }

    public function setEntityId(string $entityId) : self{
        $this->entityId = $entityId;
        return $this;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        $this->entityId = $item->getNamedTag()->getString(GlobalEntitySelector::TAG_ENTITY_ID, ":");
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function readStateFromWorld() : self{
        parent::readStateFromWorld();

        $tile = $this->position->getWorld()->getTile($this->position);

        if($tile instanceof Spawner){
            if($tile->getEntityId() !== ":") $this->entityId = $tile->getEntityId();
        }
        return $this;
    }

    public function writeStateToWorld() : void{
        parent::writeStateToWorld();

        $tile = $this->position->getWorld()->getTile($this->position);

        assert($tile instanceof Spawner);

        if($tile->getEntityId() === ":") $tile->setEntityId($this->entityId); 
    }

}