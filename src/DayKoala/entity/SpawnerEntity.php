<?php

namespace DayKoala\entity;

use pocketmine\entity\Living;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Attribute;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

use pocketmine\entity\Location;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use pocketmine\item\VanillaItems;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;

use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\AddActorPacket;

use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;

use DayKoala\entity\traits\StackableTrait;

use DayKoala\utils\SpawnerNames;

class SpawnerEntity extends Living{

    use StackableTrait;

    public static function getNetworkTypeId() : String{ return EntityIds::AGENT; }

    protected string $networkTypeId;
    protected int $legacyNetworkTypeId;

    public function __construct(Location $location, ?CompoundTag $nbt = null){
        $this->legacyNetworkTypeId = LegacyEntityIdToStringIdMap::getInstance()->stringToLegacy($this->networkTypeId = $nbt->getString("id", ":")) ?? 0;
        parent::__construct($location, $nbt);

        $this->setNameTagVisible(true);
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
        return [];
    }

    public function getXpDropAmount() : Int{
        return mt_rand(1, 5);
    }

    public function attack(EntityDamageEvent $source) : Void{
        if($source->isCancelled()){
           return;
        }
        if($source instanceof EntityDamageByEntityEvent){
           $source->setKnockBack(0);
        }
        if($this->getHealth() < $source->getFinalDamage() and $this->getStackSize() > 1){
           $source->cancel();
           
           $this->reduceStackSize(1);
           $this->setHealth($this->getMaxHealth());

           $event = new EntityDeathEvent($this, $this->getDrops(), $this->getXpDropAmount());
           $event->call();

           foreach($event->getDrops() as $item){
              $this->getWorld()->dropItem($this->location, $item);
           }

           $this->getWorld()->dropExperience($this->location, $event->getXpDropAmount());
        }
        parent::attack($source);
    }

    public function onUpdate(Int $currentTick) : Bool{
        if($this->closed){
           return false;
        }
        parent::onUpdate($currentTick);

        $this->setNameTag(SpawnerNames::getName($this->legacyNetworkTypeId) ." x". $this->getStackSize());
        return true;
    }

    protected function sendSpawnPacket(Player $player) : Void{
        $player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
            $this->getId(),
            $this->getId(),
            $this->networkTypeId,
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

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.5, 1.5);
    }

}