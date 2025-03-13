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

namespace DayKoala\entity\object;

use pocketmine\entity\object\FallingBlock;

use pocketmine\entity\Location;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\block\VanillaBlocks;

use pocketmine\entity\EntitySizeInfo;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\SetActorDataPacket;

use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;

use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;

use DayKoala\entity\SpawnerHolder;

use DayKoala\block\tile\Spawner;
use DayKoala\block\tile\StackableSpawner;

use DayKoala\SakuraSpawners;

class SpawnerBlockHolder extends FallingBlock implements SpawnerHolder{

    protected ?Spawner $tile;

    public function __construct(Location $location, ?Spawner $tile = null, ?CompoundTag $nbt = null){
        $this->tile = $tile;
        parent::__construct($location, VanillaBlocks::AIR(), $nbt);

        $this->setNameTagAlwaysVisible();
        $this->setScale(0.01);
        $this->setNoClientPredictions();
    }

    public function getTile() : ?Spawner{
        return $this->tile;
    }

    public function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(0, 0);
    }

    public function hasViewerInPosition(?Vector3 $position = null, int $distance = 5) : bool{
        $has = false;
        $pos = $position ?? $this->location;
        $players = $this->location->getWorld()->getViewersForPosition($pos);
        if(count($players) > 0){
            foreach($players as $player){
                if($player->getLocation()->distance($this->location) > $distance){
                    continue;
                }
                $has = true;
                break;
            }
        }
        return $has;
    }

    public function sendNameTag(string $value = "") : void{
        $this->location->getWorld()->broadcastPacketToViewers($this->location, SetActorDataPacket::create(
            $this->getId(), 
            [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->nameTag = $value)], 
            new PropertySyncData([], []), 0
        ));
        $this->networkPropertiesDirty = true;
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        if($this->closed){
            return false;
        }
        if($this->tile !== null and !$this->tile->isClosed()){
            $this->sendNameTag($this->hasViewerInPosition() ? $this->toDisplay() : "");
        }else{
            $this->close();
            return false;
        }
        $this->tile->onUpdate();
        return true;
    }

    public function toDisplay() : string{
        $args = ["{name}" => SakuraSpawners::getGlobalEntityData()->getEntityName($this->tile->getEntityId())];
        if($this->tile instanceof StackableSpawner){
            $args["{stack}"] = $this->tile->getStackSize();
            $args["{max-stack}"] = $this->tile->getMaxStackSize();
        }
        return str_replace(
            array_keys($args),
            array_values($args),
            SakuraSpawners::getPropertiesData()->getString("SPAWNER.TITLE.NAME.FORMAT")
        );
    }

}