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

use pocketmine\world\World;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\block\tile\TileFactory;

use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper as Helper;

use pocketmine\item\StringToItemParser;

use pocketmine\utils\TextFormat;

use DayKoala\utils\SpawnerSettings;
use DayKoala\utils\SpawnerNames;

use DayKoala\entity\SpawnerEntity;

use DayKoala\block\tile\SpawnerTile;

use DayKoala\item\ItemHandler;

use DayKoala\item\SakuraSpawnersItems;

use DayKoala\command\SakuraSpawnersCommand;
use DayKoala\command\HitKillCommand;

final class SakuraSpawners extends PluginBase{

    private static $instance = null;
    private static $settings = null;
    private static $names = null;

    public static function getInstance() : ?self{
        return self::$instance;
    }

    public static function getSettings() : ?SpawnerSettings{
        return self::$settings;
    }

    public static function getNames() : ?SpawnerNames{
        return self::$names;
    }

    protected function onLoad() : Void{
        self::$instance = $this;
        self::$settings = new SpawnerSettings($this);
        self::$names = new SpawnerNames($this);
    }

    protected function onEnable() : Void{

        EntityFactory::getInstance()->register(SpawnerEntity::class, function(World $world, CompoundTag $nbt) : SpawnerEntity{
            return new SpawnerEntity(Helper::parseLocation($nbt, $world), $nbt);
        }, ['SpawnerEntity']);
        TileFactory::getInstance()->register(SpawnerTile::class, ['MobSpawner', 'minecraft:mob_spawner']);
        ItemHandler::registerItem(SakuraSpawnersItems::SPAWN_EGG_ID, SakuraSpawnersItems::SPAWN_EGG(), ['383', 'spawn_egg']);

        $parser = StringToItemParser::getInstance();

        foreach(self::$names->getNames() as $meta => $name){
            $parser->override('52:'. $meta = (int) $meta, fn() => SakuraSpawnersItems::setSpawnerEntityId(
                SakuraSpawnersItems::MONSTER_SPAWNER()->asItem(), $meta
            )->setCustomName(
                str_replace('{name}', $name = TextFormat::clean($name), self::$settings->getDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_NAME)))
            );
            $parser->override('383:'. $meta, fn() => SakuraSpawnersItems::SPAWN_EGG()->setLegacyEntityId($meta)->setCustomName(
                str_replace('{name}', $name, self::$settings->getDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_EGG_NAME)))
            );
        }

        $this->getServer()->getPluginManager()->registerEvents(new SakuraSpawnersListener(), $this);
        $this->getServer()->getCommandMap()->registerAll('SakuraSpawners', [new SakuraSpawnersCommand($this), new HitKillCommand($this)]);
    }

    protected function onDisable() : Void{
        if(self::$settings) self::$settings->saveSettings();
        if(self::$names) self::$names->saveNames();
    }

}