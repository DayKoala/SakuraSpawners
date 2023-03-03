<?php

namespace DayKoala;

use pocketmine\plugin\PluginBase;

use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper as Helper;

use pocketmine\world\World;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\block\tile\TileFactory;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockToolType;

use pocketmine\item\ToolTier;

use DayKoala\utils\SpawnerNames;

use DayKoala\entity\SpawnerEntity;

use DayKoala\block\tile\SpawnerTile;

use DayKoala\block\SpawnerBlock;

final class SakuraSpawners extends PluginBase{

    protected function onEnable() : Void{
        $this->saveResource('Names.yml');

        SpawnerNames::init($this->getDataFolder());

        EntityFactory::getInstance()->register(SpawnerEntity::class, function(World $world, CompoundTag $nbt) : SpawnerEntity{
            return new SpawnerEntity(Helper::parseLocation($nbt, $world), $nbt);
        }, ['SpawnerEntity']);

        TileFactory::getInstance()->register(SpawnerTile::class, ['MobSpawner', 'minecraft:mob_spawner']);
        BlockFactory::getInstance()->register(new SpawnerBlock(new BlockIdentifier(BlockLegacyIds::MOB_SPAWNER, 0, null, SpawnerTile::class), 'Monster Spawner', new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())), true);
    }

}