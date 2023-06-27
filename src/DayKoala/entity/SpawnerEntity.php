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

use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Attribute;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;

use pocketmine\network\mcpe\protocol\AddActorPacket;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\player\Player;

use DayKoala\entity\traits\StackableTrait;

use DayKoala\SakuraSpawners;

use DayKoala\utils\SpawnerSettings;

class SpawnerEntity extends Living{

    use StackableTrait;

    public static function getNetworkTypeId() : String{ return EntityIds::AGENT; }

    protected string $networkTypeId;

    protected int $legacyNetworkTypeId;

    public function __construct(Location $location, ?CompoundTag $nbt = null){

        $this->legacyNetworkTypeId = array_search(
            $this->networkTypeId = $nbt->getString('id', static::getNetworkTypeId()), LegacyEntityIdToStringIdMap::getInstance()->getLegacyToStringMap()
        );

        parent::__construct($location, $nbt);

        $max = SakuraSpawners::getSettings()->getDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_MAX_STACK);
        $min = SakuraSpawners::getSettings()->getDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_MIN_STACK);

        if($max > 0){
            if($min < $max){
                $this->stack = $min;
            }
            $this->maxStack = $max;
        }

        if($this->maxStack !== 1) $this->setNameTagAlwaysVisible(true);

    }

    final public function getNetworkId() : String{ return $this->networkTypeId; }

    final public function getLegacyNetworkId() : Int{ return $this->legacyNetworkTypeId; }

    protected function getInitialSizeInfo() : EntitySizeInfo{ return SakuraSpawners::getSettings()->getEntitySize($this->legacyNetworkTypeId); }

    public function getName() : String{ return SakuraSpawners::getNames()->getName($this->legacyNetworkTypeId); }

    public function getDrops() : Array{ return SakuraSpawners::getSettings()->getEntityDrops($this->legacyNetworkTypeId); }

    public function getXpDropAmount() : Int{ return SakuraSpawners::getSettings()->getEntityXp($this->legacyNetworkTypeId); }

    public function attack(EntityDamageEvent $source) : Void{
        if($source->isCancelled()){
            return;
        }
        if(
            $source instanceof EntityDamageByEntityEvent and 
            !SakuraSpawners::getSettings()->getDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_KNOCKBACK)
        ){
            $source->setKnockBack(0);
        }
        if(
            $source->getFinalDamage() >= $this->getHealth() and 
            $this->stack > 1
        ){
            $source->cancel();
            $this->onDeath();
        }
        parent::attack($source);
    }

    public function kill() : Void{
        if($this->stack > 1){
           for($i = 1; $i <= $this->stack; $i++) $this->onDeath();
        }
        parent::kill();
    }

    public function onDeath() : Void{
        if($this->stack > 1){
            $this->stack--;
            $this->setHealth($this->getMaxHealth());
        }
        parent::onDeath();
    }

    public function onUpdate(Int $currentTick) : Bool{
        if($this->closed){
            return false;
        }
        $this->setNameTag($this->getDisplayName());
        return parent::onUpdate($currentTick);
    }

    public function startDeathAnimation() : Void{
        if(!$this->isAlive()) parent::startDeathAnimation();
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
			}, $this->attributeMap->getAll()),
			$this->getAllNetworkData(),
			new PropertySyncData([], []),
			[]
		));
    }

    public function getDisplayName() : String{
        $args = ['{health}' => $this->getHealth(), '{max-health}' => $this->getMaxHealth(), '{stack}' => $this->getStackSize(), '{max-stack}' => $this->getMaxStackSize(), '{name}' => $this->getName()];
        return str_replace(array_keys($args), array_values($args), SakuraSpawners::getSettings()->getDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_NAME));
    }

}