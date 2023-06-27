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

namespace DayKoala\utils;

use pocketmine\utils\Config;

use pocketmine\item\Item;

use pocketmine\entity\EntitySizeInfo;

use DayKoala\SakuraSpawners;

use DayKoala\item\ItemHandler;

final class SpawnerSettings{

    public const TAG_DEFAULT_SPAWNER_ENTITY_NAME = 'spawner.default.entity.name';

    public const TAG_DEFAULT_SPAWNER_ENTITY_MIN_STACK = 'spawner.default.entity.min.stack';
    public const TAG_DEFAULT_SPAWNER_ENTITY_MAX_STACK = 'spawner.default.entity.max.stack';

    public const TAG_DEFAULT_SPAWNER_ENTITY_SPAWN_DISTANCE = 'spawner.default.entity.spawn.distance';
    public const TAG_DEFAULT_SPAWNER_ENTITY_STACK_DISTANCE = 'spawner.default.entity.stack.distance';

    public const TAG_DEFAULT_SPAWNER_ENTITY_KNOCKBACK = 'spawner.default.entity.knockback';

    public const TAG_DEFAULT_SPAWNER_NAME = 'spawner.default.name';

    public const TAG_DEFAULT_SPAWNER_EGG_NAME = 'spawner.default.egg.name';

    private const TAG_ENTITY_XP = 'entity.xp';
    private const TAG_ENTITY_DROPS = 'entity.drops';
    private const TAG_ENTITY_HEIGHT = 'entity.height';
    private const TAG_ENTITY_WIDTH = 'entity.width';

    private array $settings;

    private array $drops = [];
    private array $sizes = [];

    public function __construct(
        private SakuraSpawners $plugin
    ){
        $plugin->saveResource('Settings.yml');
        $this->loadSettings();
    }

    public function setDefault(String $key, $value) : Void{
        $this->settings[$key] = $value;
    }

    public function getDefault(String $key) : String|Int{
        return match($key){
            self::TAG_DEFAULT_SPAWNER_ENTITY_NAME => (isset($this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_NAME]) ? (string) $this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_NAME] : '{name} x{stack}'),
            self::TAG_DEFAULT_SPAWNER_ENTITY_MIN_STACK => (isset($this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_MIN_STACK]) ? (int) $this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_MIN_STACK] : 1),
            self::TAG_DEFAULT_SPAWNER_ENTITY_MAX_STACK => (isset($this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_MAX_STACK]) ? (int) $this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_MAX_STACK] : 300),
            self::TAG_DEFAULT_SPAWNER_ENTITY_SPAWN_DISTANCE => (isset($this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_SPAWN_DISTANCE]) ? (int) $this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_SPAWN_DISTANCE] : 5),
            self::TAG_DEFAULT_SPAWNER_ENTITY_STACK_DISTANCE => (isset($this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_STACK_DISTANCE]) ? (int) $this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_STACK_DISTANCE] : 5),
            self::TAG_DEFAULT_SPAWNER_ENTITY_KNOCKBACK => (isset($this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_KNOCKBACK]) ? (bool) $this->settings[self::TAG_DEFAULT_SPAWNER_ENTITY_KNOCKBACK] : false),
            self::TAG_DEFAULT_SPAWNER_NAME => (isset($this->settings[self::TAG_DEFAULT_SPAWNER_NAME]) ? (string) $this->settings[self::TAG_DEFAULT_SPAWNER_NAME] : '{name} Spawner'),
            self::TAG_DEFAULT_SPAWNER_EGG_NAME => (isset($this->settings[self::TAG_DEFAULT_SPAWNER_EGG_NAME]) ? (string) $this->settings[self::TAG_DEFAULT_SPAWNER_EGG_NAME] : '{name} Egg'),
            default => 0
        };
    }

    public function hasEntitySettings(Int $id) : Bool{
        return isset($this->settings[$id]);
    }

    public function getEntitySettings(Int $id) : Array{
        return $this->settings[$id] ?? [];
    }

    public function hasEntityXp(Int $id) : Bool{
        return isset($this->settings[$id], $this->settings[$id][self::TAG_ENTITY_XP]);
    }

    public function getEntityXp(Int $id) : Int{
        return isset($this->settings[$id], $this->settings[$id][self::TAG_ENTITY_XP]) ? (int) $this->settings[$id][self::TAG_ENTITY_XP] : 0;
    }

    public function setEntityXp(Int $id, Int $amount) : Void{
        $this->settings[$id][self::TAG_ENTITY_XP] = $amount < 0 ? 0 : $amount;
    }

    public function hasEntityDrops(Int $id) : Bool{
        return isset($this->drops[$id]);
    }

    public function getEntityDrops(Int $id) : Array{
        return $this->drops[$id] ?? [];
    }

    public function hasEntityDrop(Int $id, Item $item) : Bool{
        return isset($this->drops[$id], $this->drops[$id][$item->__toString()]);
    }

    public function addEntityDrop(Int $id, Item $item) : Void{
        $this->settings[$id][self::TAG_ENTITY_DROPS][$item->__toString()] = ItemHandler::fromItem($item);
        $this->drops[$id][$item->__toString()] = $item;
    }

    public function removeEntityDrop(Int $id, Item $item) : Void{
        if(isset($this->drops[$id], $this->drops[$id][$item->__toString()])) unset($this->drops[$id][$item->__toString()]);
        if(isset($this->settings[$id], $this->settings[$id][self::TAG_ENTITY_DROPS], $this->settings[$id][self::TAG_ENTITY_DROPS][$item->__toString()])) unset($this->settings[$id][self::TAG_ENTITY_DROPS][$item->__toString()]);
    }

    public function hasEntitySize(Int $id) : Bool{
        return isset($this->sizes[$id]);
    }

    public function getEntitySize(Int $id) : EntitySizeInfo{
        return $this->sizes[$id] ?? new EntitySizeInfo(1.3, 1.3);
    }

    public function setEntitySize(Int $id, Float $height, Float $width) : Void{
        $this->settings[$id][self::TAG_ENTITY_HEIGHT] = $height = $height < 0.5 ? 0.5 : $height;
        $this->settings[$id][self::TAG_ENTITY_WIDTH] = $width = $width < 0.5 ? 0.5 : $width;
        $this->sizes[$id] = new EntitySizeInfo($height, $width);
    }

    public function saveSettings() : Void{
        if(empty($this->settings)){
            return;
        }
        $settings = new Config($this->plugin->getDataFolder() .'Settings.yml', Config::YAML);
        $settings->setAll($this->settings);
        $settings->save();
    }

    public function loadSettings() : Void{
        $this->settings = (new Config($this->plugin->getDataFolder() .'Settings.yml', Config::YAML))->getAll();
        if(!empty($this->settings)){
            foreach($this->settings as $id => $data){
                if(!is_numeric($id)){
                   continue;
                }
                if(isset($data[self::TAG_ENTITY_DROPS])){
                    foreach($data[self::TAG_ENTITY_DROPS] as $name => $item) $this->drops[$id][$name] = ItemHandler::fromString($item);
                }
                if(isset($data[self::TAG_ENTITY_HEIGHT], $data[self::TAG_ENTITY_WIDTH])) $this->sizes[$id] = new EntitySizeInfo($data[self::TAG_ENTITY_HEIGHT], $data[self::TAG_ENTITY_WIDTH]);
            }
        }
    }

}