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

namespace DayKoala\item;

use pocketmine\utils\CloningRegistryTrait;

use pocketmine\data\bedrock\block\BlockTypeNames;

use pocketmine\data\bedrock\item\ItemTypeNames;

use pocketmine\item\{Item, ToolTier, ItemIdentifier, ItemTypeIds};

use pocketmine\block\{Block, BlockIdentifier, BlockTypeIds, BlockTypeInfo, BlockBreakInfo, BlockToolType};

use DayKoala\block\SpawnerBlock;

use DayKoala\block\tile\SpawnerTile;

final class SakuraSpawnersItems{

    use CloningRegistryTrait;

    /**
     * 
     *  @method static SpawnerBlock MONSTER_SPAWNER()
     *  @method static SpawnEgg SPAWN_EGG() 
     *
    */

    public const TAG_MONSTER_SPAWNER_ENTITY_ID = 'SpawnerEntityId';

    public const MONSTER_SPAWNER_ID = BlockTypeNames::MOB_SPAWNER;
    public const SPAWN_EGG_ID = ItemTypeNames::AXOLOTL_SPAWN_EGG;

    private static int $spawnerRuntimeId = 0;
    private static int $spawnEggRuntimeId = 0;

    public static function getSpawnerRuntimeId() : Int{ return self::$spawnerRuntimeId; }

    public static function getSpawnEggRuntimeId() : String{ return self::$spawnEggRuntimeId; }

    public static function getSpawnerEntityId(Item $item) : Int{ return $item->getNamedTag()->getInt(self::TAG_MONSTER_SPAWNER_ENTITY_ID, 0); }

    public static function setSpawnerEntityId(Item $item, Int $id) : Item{
        $namedtag = $item->getNamedTag();
        $namedtag->setInt(self::TAG_MONSTER_SPAWNER_ENTITY_ID, $id);
        $item->setNamedTag($namedtag);
        return $item;
    }

    protected static function setup() : Void{ 
        self::register('monster_spawner', new SpawnerBlock(new BlockIdentifier(self::$spawnerRuntimeId = BlockTypeIds::MONSTER_SPAWNER, SpawnerTile::class), 'Sakura Monster Spawner', new BlockTypeInfo(new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel()))));
        self::register('spawn_egg', new SpawnEgg(new ItemIdentifier(self::$spawnEggRuntimeId = ItemTypeIds::newId()), 'Spawn Egg'));
    }

    protected static function register(String $name, Block|Item $result) : Void{
        self::_registryRegister($name, $result);
    }

    private function __construct(){}

}