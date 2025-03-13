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

use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockToolType;
use pocketmine\block\Block;

use pocketmine\item\ToolTier;
use pocketmine\item\Item;

use DayKoala\block\SpawnerBlock;

use DayKoala\block\tile\GlobalStackableSpawner;

final class SpawnerItems{

    /**
     * 
     * @method static SpawnerBlock MONSTER_SPAWNER()
     * @method static Item SPAWNER()
     * 
     */

    use CloningRegistryTrait;

    protected static function setup() : void{
        self::register('monster_spawner', $block = new SpawnerBlock(new BlockIdentifier(BlockTypeIds::MONSTER_SPAWNER, GlobalStackableSpawner::class), 'Sakura Monster Spawner', new BlockTypeInfo(new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel()))));
        self::register('spawner', $block->asItem());
    }

    protected static function register(string $name, Block|Item $result) : void{
        self::_registryRegister($name, $result);
    }

    private function __construct(){}

}