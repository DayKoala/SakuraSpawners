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

use pocketmine\world\Position;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use pocketmine\entity\Location;
use pocketmine\entity\Entity;

use pocketmine\utils\Utils;

use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ReturnType;
use DaveRandom\CallbackValidator\ParameterType;

use DayKoala\SakuraSpawners;

use DayKoala\item\GlobalEntityDropsManager;

final class GlobalEntitySelector{

    public const TAG_ENTITY_ID = "EntityIdentifier";

    private static $instance = null;

    public static function getInstance() : self{
        return self::$instance ?? (self::$instance = new self);
    }

    private array $entities = [];
    private array $names = [];
    
    private \Closure $globalCallback;

    private function __construct(){
        $this->globalCallback = function(Position $position, String $entityId) : ?GlobalStackableEntity{
            $entityMap = array_values(LegacyEntityIdToStringIdMap::getInstance()->getLegacyToStringMap());
            if(!in_array($entityId, $entityMap)){
                return null;
            }
            $data = SakuraSpawners::getGlobalEntityData();
            $properties = SakuraSpawners::getPropertiesData();

            $entity = new GlobalStackableEntity(new Location($position->x + 0.5, $position->y + 1, $position->z + 0.5, $position->world, lcg_value() * 360, 0), $entityId);

            $entity->setCustomName($data->getEntityName($entityId));
            $entity->setScale($data->getEntityScale($entityId));
            $entity->setXpDropAmount($data->getEntityXPAmount($entityId));

            $entity->setMaxStackSize($properties->getInt("ENTITY.MAX.STACK.SIZE"));
            $entity->setStackSize($properties->getInt("ENTITY.MIN.STACK.SIZE"));

            if(!GlobalEntityDropsManager::isEntityDropsWrited($entityId)){
                GlobalEntityDropsManager::writeEntityDrops($entityId);
            }

            $entity->setDrops(GlobalEntityDropsManager::readEntityDrops($entityId));
            $entity->setNameTagAlwaysVisible();
            return $entity;
        };
    }

    public function exists(string $entityId) : bool{
        return isset($this->entities[$entityId]);
    }

    public function getName(string $entityId) : string{
        return $this->names[$entityId] ?? GlobalStackableEntity::class;
    }

    public function get(string $entityId, Position $position) : ?Entity{
        if(isset($this->entities[$entityId])){
            return $this->entities[$entityId]($position, $entityId);
        }
        return ($this->globalCallback)($position, $entityId);
    }

    public function register(string $entityId, string $className, \Closure $callback, bool $override = false){
        if(isset($this->entities[$entityId]) and !$override){
            return;
        }
        Utils::testValidInstance($className, Entity::class);
        Utils::validateCallableSignature(new CallbackType(
            new ReturnType(Entity::class),
            new ParameterType("position", Position::class),
            new ParameterType("entityId", "string")
        ), $callback);

        $this->names[$entityId] = $className;
        $this->entities[$entityId] = $callback;
    }

    public function unregister(string $entityId) : void{
        if(isset($this->names[$entityId])) unset($this->names[$entityId]);
        if(isset($this->entities[$entityId])) unset($this->entities[$entityId]);
    }

}