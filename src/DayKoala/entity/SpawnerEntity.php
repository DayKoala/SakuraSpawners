<?php

namespace DayKoala\entity;

use pocketmine\entity\Living;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Attribute;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

use pocketmine\entity\Location;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\AddActorPacket;

use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;

use DayKoala\utils\traits\StackableTrait;

use DayKoala\utils\SpawnerNames;

use DayKoala\SakuraSpawners;

class SpawnerEntity extends Living{

    use StackableTrait;

    public static function getNetworkTypeId() : String{ return EntityIds::AGENT; }

    protected string $networkTypeId;
    protected int $legacyNetworkTypeId;

    protected string $display;

    public function __construct(Location $location, ?CompoundTag $nbt = null){
        $this->legacyNetworkTypeId = LegacyEntityIdToStringIdMap::getInstance()->stringToLegacy($this->networkTypeId = $nbt->getString("id", static::getNetworkTypeId())) ?? 0;
        $this->display = SakuraSpawners::getInstance()->getDefaultEntityName();
        parent::__construct($location, $nbt);

        $this->setNameTagAlwaysVisible(true);
    }

    public function getModifiedNetworkTypeId() : String{
        return $this->networkTypeId;
    }

    public function getModifiedLegacyNetworkTypeId() : Int{
        return $this->legacyNetworkTypeId;
    }

    public function getName() : String{
        return SpawnerNames::getName($this->legacyNetworkTypeId);
    }

    public function getDrops() : Array{
        return SakuraSpawners::getInstance()->getSpawnerDrops($this->legacyNetworkTypeId);
    }

    public function getXpDropAmount() : Int{
        return SakuraSpawners::getInstance()->getSpawnerXp($this->legacyNetworkTypeId);
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return SakuraSpawners::getInstance()->getSpawnerSize($this->legacyNetworkTypeId);
    }

    public function getNameTag() : String{
        $args = [
            '{health}' => $this->getHealth(),
            '{max-health}' => $this->getMaxHealth(),
            '{stack}' => $this->getStackSize(),
            '{max-stack}' => $this->getMaxStackSize(),
            '{name}' => $this->getName()
        ];
        return str_replace(array_keys($args), array_values($args), $this->display);
    }

    public function attack(EntityDamageEvent $source) : Void{
        if($source->isCancelled()){
           return;
        }
        if($source instanceof EntityDamageByEntityEvent){
           $source->setKnockBack(0);
        }
        if($source->getFinalDamage() >= $this->getHealth() and $this->stack > 1){
           $source->cancel();
           $this->onDeath();
        }
        parent::attack($source);
    }

    protected function onDeath() : Void{
        if($this->stack > 1){
           $this->stack--;
           $this->setHealth($this->getMaxHealth());
        }
        parent::onDeath();
    }

    protected function startDeathAnimation() : Void{
        if(!$this->isAlive()) parent::startDeathAnimation();
    }

    public function onUpdate(Int $currentTick) : Bool{
        if($this->closed){
           return false;
        }
        $this->setNameTag($this->getNameTag());
        return parent::onUpdate($currentTick);
    }
    
    protected function sendSpawnPacket(Player $player) : Void{
        $player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
            $this->getId(),
            $this->getId(),
            $this->getModifiedNetworkTypeId(),
            $this->location->asVector3(),
            $this->getMotion(),
            $this->location->pitch,
            $this->location->yaw,
            $this->location->yaw,
            $this->location->yaw,
            array_map(function(Attribute $attr) : NetworkAttribute{
                return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue(), []);
            }, $this->attributeMap->getAll()), $this->getAllNetworkData(), new PropertySyncData([], []), []
        ));
    }

}