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

namespace DayKoala\entity;

use pocketmine\entity\Location;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Attribute;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;

use pocketmine\network\mcpe\protocol\AddActorPacket;

use DayKoala\SakuraSpawners;

class GlobalStackableEntity extends StackableEntity{

    public static function getNetworkTypeId() : string{
        return EntityIds::AGENT;
    }

    protected string $entityId;

    protected string $customName = "GlobalStackableEntity";

    protected int $xp = 0;

    protected array $drops = [];

    public function __construct(Location $location, string $id, ?CompoundTag $nbt = null){
        $this->entityId = $id;
        parent::__construct($location, $nbt);
    }

    public function getName() : string{
        return "GlobalStackableEntity";
    }

    public function getEntityId() : string{
        return $this->entityId;
    }

    public function getCustomName() : string{
        return $this->customName;
    }

    public function setCustomName(string $name) : void{
        $this->customName = $name;
    }

    public function getXpDropAmount() : int{
        return $this->xp;
    }

    public function setXpDropAmount(int $xp) : void{
        $this->xp = $xp;
    }

    public function getDrops() : array{
        return $this->drops;
    }

    public function setDrops(array $drops) : void{
        $this->drops = $drops;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        $size = SakuraSpawners::getGlobalEntityData()->getEntitySize($this->entityId);
        return new EntitySizeInfo((float) $size[0], (float) $size[1]);
    }

    public function onUpdate(int $currentTick) : bool{
        if($this->closed){
            return false;
        }
        $this->setNameTag($this->toDisplay());
        return parent::onUpdate($currentTick);
    }

    public function onDeath() : void{
        if($this->currentStack > 1){
            $this->currentStack--;
            $this->setHealth($this->getMaxHealth());
        }
        parent::onDeath();
    }

    public function attack(EntityDamageEvent $source) : void{
        if($source->isCancelled()){
            return;
        }
        if($source instanceof EntityDamageByEntityEvent){
            $source->setKnockBack(0);
        }
        if($source->getFinalDamage() >= $this->getHealth() and $this->currentStack > 1){
            $source->cancel();
            $this->onDeath();
        }
        parent::attack($source);
    }

    public function kill() : void{
        if($this->currentStack > 1){
            for($i = 1; $i <= $this->currentStack; $i++) $this->onDeath();
        }
        parent::kill();
    }

    public function startDeathAnimation() : void{
        if(!$this->isAlive()) parent::startDeathAnimation();
    }

    final protected function sendSpawnPacket(Player $player) : void{
        $player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
            $this->getId(),
            $this->getId(),
            $this->entityId,
            $this->location->asVector3(),
            $this->getMotion(),
            $this->location->pitch,
            $this->location->yaw,
            $this->location->yaw,
            $this->location->yaw,
            array_map(function(Attribute $attr) : NetworkAttribute{
                return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue(), []);
            }, $this->attributeMap->getAll()),
            $this->getAllNetworkData(),
            new PropertySyncData([], []),
            []
        ));
    }

    public function saveNBT() : CompoundTag{
        $nbt = parent::saveNBT()
            ->setString(GlobalEntitySelector::TAG_ENTITY_ID, $this->entityId);
        return $nbt;
    }

    public function toDisplay() : string{
        $args = [
            "{health}" => $this->getHealth(),
            "{max-health}" => $this->getMaxHealth(),
            "{stack}" => $this->currentStack,
            "{max-stack}" => $this->maxStack,
            "{name}" => $this->customName
        ];
        return str_replace(
            array_keys($args), 
            array_values($args), 
            SakuraSpawners::getPropertiesData()->getString("SPAWNER.ENTITY.NAME.FORMAT")
        );
    }

}