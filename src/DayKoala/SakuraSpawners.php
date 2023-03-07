<?php

namespace DayKoala;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;

use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper as Helper;
use pocketmine\entity\EntitySizeInfo;

use pocketmine\utils\Config;

use pocketmine\world\World;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\block\tile\TileFactory;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockToolType;

use pocketmine\item\ToolTier;
use pocketmine\item\Item;

use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;

use pocketmine\network\mcpe\convert\TypeConverter;

use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;

use DayKoala\utils\SpawnerNames;

use DayKoala\entity\SpawnerEntity;

use DayKoala\block\tile\SpawnerTile;

use DayKoala\block\SpawnerBlock;

use DayKoala\command\SakuraSpawnersCommand;

final class SakuraSpawners extends PluginBase implements Listener{

    public const TAG_ENTITY_NAME = 'entity.name';
    public const TAG_SPAWNER_NAME = 'spawner.name';

    public const TAG_SPAWNER_DROPS = 'spawner.drops';
    public const TAG_SPAWNER_XP = 'spawner.xp';

    public const TAG_SPAWNER_HEIGHT = 'spawner.height';
    public const TAG_SPAWNER_WIDTH = 'spawner.width';

    private static $instance = null;

    public static function getInstance() : ?self{
        return self::$instance;
    }

    private array $settings;

    private array $drops;
    private array $size;

    protected function onLoad() : Void{
        self::$instance = $this;
    }

    protected function onEnable() : Void{
        $this->saveResource('Names.yml');

        SpawnerNames::init($folder = $this->getDataFolder());

        $this->settings = (new Config($folder .'Settings.json', Config::JSON))->getAll();

        EntityFactory::getInstance()->register(SpawnerEntity::class, function(World $world, CompoundTag $nbt) : SpawnerEntity{
            return new SpawnerEntity(Helper::parseLocation($nbt, $world), $nbt);
        }, ['SpawnerEntity']);
        TileFactory::getInstance()->register(SpawnerTile::class, ['MobSpawner', 'minecraft:mob_spawner']);
        BlockFactory::getInstance()->register(new SpawnerBlock(new BlockIdentifier(BlockLegacyIds::MOB_SPAWNER, 0, null, SpawnerTile::class), 'Monster Spawner', new BlockBreakInfo(5.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())), true);

        $this->getServer()->getCommandMap()->register('SakuraSpawners', new SakuraSpawnersCommand($this));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->writeSpawnerSettings();
    }

    protected function onDisable() : Void{
        if($this->settings === null){
           return;
        }
        $settings = new Config($this->getDataFolder() .'Settings.json', Config::JSON);
        $settings->setAll($this->settings);
        $settings->save();
    }

    public function onReceive(DataPacketSendEvent $event) : Void{
        $packets = $event->getPackets();
        foreach($packets as $packet){
           if($packet instanceof InventoryContentPacket){
              foreach($packet->items as $index => $item){
                 $itemStack = $this->writeSpawnerName($item->getItemStack());
                 if($itemStack instanceof ItemStack) $packet->items[$index] = new ItemStackWrapper($item->getStackId(), $itemStack);
              }
           }
           if($packet instanceof InventorySlotPacket){
              $itemStack = $this->writeSpawnerName($packet->item->getItemStack());
              if($itemStack instanceof ItemStack) $packet->item = new ItemStackWrapper($packet->item->getStackId(), $itemStack);
           }
        }
    }

    public function getSettings() : Array{
        return $this->settings ?? [];
    }

    public function getDefaultEntityName() : String{
        return isset($this->settings[self::TAG_ENTITY_NAME]) ? $this->settings[self::TAG_ENTITY_NAME] : "{name} x{stack}";
    }

    public function setDefaultEntityName(String $name) : Void{
        $this->settings[self::TAG_ENTITY_NAME] = $name;
    }

    public function getDefaultSpawnerName() : String{
        return isset($this->settings[self::TAG_SPAWNER_NAME]) ? $this->settings[self::TAG_ENTITY_NAME] : "{name} Spawner";
    }

    public function getSpawnerNameFormat(Int $meta) : String{
        return str_replace("{name}", SpawnerNames::getName($meta), $this->getDefaultSpawnerName());
    }

    public function setDefaultSpawnerName(String $name) : Void{
        $this->settings[self::TAG_SPAWNER_NAME] = $name;
    }

    public function hasSpawner(Int $id) : Bool{
        return isset($this->settings[$id]);
    }

    public function getSpawner(Int $id) : Array{
        return $this->settings[$id] ?? [];
    }

    public function hasSpawnerXp(Int $id) : Bool{
        return isset($this->settings[$id], $this->settings[$id][self::TAG_SPAWNER_XP]);
    }

    public function getSpawnerXp(Int $id) : Int{
        return isset($this->settings[$id], $this->settings[$id][self::TAG_SPAWNER_XP]) ? $this->settings[$id][self::TAG_SPAWNER_XP] : 0;
    }

    public function setSpawnerXp(Int $id, Int $amount) : Void{
        $this->settings[$id][self::TAG_SPAWNER_XP] = $amount < 0 ? 0 : $amount;
    }

    public function hasSpawnerDrops(Int $id) : Bool{
        return isset($this->drops[$id]);
    }

    public function getSpawnerDrops(Int $id) : Array{
        return $this->drops[$id] ?? [];
    }

    public function hasSpawnerDrop(Int $id, Item $item) : Bool{
        return isset($this->drops[$id], $this->drops[$id][$item->__toString()]);
    }

    public function addSpawnerDrop(Int $id, Item $item) : Void{
        $this->settings[$id][self::TAG_SPAWNER_DROPS][$item->__toString()] = $item->jsonSerialize();
        $this->drops[$id][$item->__toString()] = $item;
    }

    public function removeSpawnerDrop(Int $id, Item $item) : Void{
        if(isset($this->drops[$id], $this->drops[$id][$item->__toString()])) unset($this->drops[$id][$item->__toString()]);
        if(isset($this->settings[$id], $this->settings[$id][self::TAG_SPAWNER_DROPS], $this->settings[$id][self::TAG_SPAWNER_DROPS][$item->__toString()])) unset($this->settings[$id][self::TAG_SPAWNER_DROPS][$item->__toString()]);
    }

    public function hasSpawnerSize(Int $id) : Bool{
        return isset($this->size[$id]);
    }

    public function getSpawnerSize(Int $id) : EntitySizeInfo{
        return $this->size[$id] ?? new EntitySizeInfo(1.0, 1.0);
    }

    public function setSpawnerSize(Int $id, Float $height, Float $width) : Void{
        $this->settings[$id][self::TAG_SPAWNER_HEIGHT] = $height = $height < 0.5 ? 0.5 : $height;
        $this->settings[$id][self::TAG_SPAWNER_WIDTH] = $width = $width < 0.5 ? 0.5 : $width;
        $this->size[$id] = new EntitySizeInfo($height, $width);
    }

    private function writeSpawnerSettings() : Void{
        if($this->settings === null){
           return;
        }
        foreach($this->settings as $id => $data){
           if(isset($data[self::TAG_SPAWNER_DROPS])){
              foreach($data[self::TAG_SPAWNER_DROPS] as $name => $item) $this->drops[$id][$name] = Item::jsonDeserialize($item);
           }
           if(isset($data[self::TAG_SPAWNER_HEIGHT], $data[self::TAG_SPAWNER_WIDTH])) $this->size[$id] = new EntitySizeInfo($data[self::TAG_SPAWNER_HEIGHT], $data[self::TAG_SPAWNER_WIDTH]);
        }
    }

    private function writeSpawnerName(ItemStack $itemStack) : ?ItemStack{
        $item = TypeConverter::getInstance()->netItemStackToCore($itemStack);
        if($item->getId() !== BlockLegacyIds::MOB_SPAWNER){
           return null;
        }
        $name = $this->getSpawnerNameFormat($item->getMeta());
        return $item->getCustomName() !== $name ? TypeConverter::getInstance()->coreItemStackToNet($item->setCustomName($name)) : null;
    }

}