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

namespace DayKoala;

use pocketmine\plugin\PluginBase;

use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper as Helper;

use pocketmine\world\World;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\block\tile\TileFactory;

use DayKoala\provider\PropertiesData;
use DayKoala\provider\GlobalEntityData;

use DayKoala\item\SpawnerItemsManager;

use DayKoala\entity\GlobalStackableEntity;
use DayKoala\entity\GlobalEntitySelector;

use DayKoala\entity\object\SpawnerBlockHolder;

use DayKoala\block\tile\GlobalStackableSpawner;

use DayKoala\item\GlobalEntityDropsManager;

use DayKoala\command\SakuraSpawnersCommand;

final class SakuraSpawners extends PluginBase{

    private static $propertiesData = null;
    private static $globalEntityData = null;

    public static function getPropertiesData() : ?PropertiesData{
        return self::$propertiesData;
    }

    public static function getGlobalEntityData() : ?GlobalEntityData{
        return self::$globalEntityData;
    }
    
    protected function onEnable() : void{
        foreach([
            "Properties.yml",
            "Entities.yml"
        ] as $resource) $this->saveResource($resource);

        self::$propertiesData = new PropertiesData($this);
        self::$propertiesData->create();

        self::$globalEntityData = new GlobalEntityData($this);
        self::$globalEntityData->create();

        SpawnerItemsManager::registerAll();

        $factory = EntityFactory::getInstance();
        $factory->register(GlobalStackableEntity::class, function(World $world, CompoundTag $nbt) : GlobalStackableEntity{
            $data = SakuraSpawners::getGlobalEntityData();
            $entity = new GlobalStackableEntity(Helper::parseLocation($nbt, $world), $entityId = $nbt->getString(GlobalEntitySelector::TAG_ENTITY_ID, ":"), $nbt);

            $entity->setCustomName($data->getEntityName($entityId));
            $entity->setScale($data->getEntityScale($entityId));
            $entity->setXpDropAmount($data->getEntityXPAmount($entityId));

            if(!GlobalEntityDropsManager::isEntityDropsWrited($entityId)){
                GlobalEntityDropsManager::writeEntityDrops($entityId);
            }

            $entity->setDrops(GlobalEntityDropsManager::readEntityDrops($entityId));
            $entity->setNameTagAlwaysVisible();
            return $entity;
        }, ["GlobalStackableEntity", "SakuraSpawners:GlobalStackableEntity"]);
        $factory->register(SpawnerBlockHolder::class, function(World $world, CompoundTag $nbt) : SpawnerBlockHolder{
            return new SpawnerBlockHolder(
                Helper::parseLocation($nbt, $world), null, $nbt
            );
        }, ["SpawnerBlockHolder", "SakuraSpawners:SpawnerBlockHolder"]);
        TileFactory::getInstance()->register(GlobalStackableSpawner::class, ["MobSpawner", "minecraft:mob_spawner"]);

        $this->getServer()->getCommandMap()->register("SakuraSpawners", new SakuraSpawnersCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new SakuraSpawnersListener(), $this);
    }

    protected function onDisable() : void{
        if(self::$propertiesData !== null) self::$propertiesData->save();
        if(self::$globalEntityData !== null) self::$globalEntityData->save();
    }

}