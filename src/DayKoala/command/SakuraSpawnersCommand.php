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

namespace DayKoala\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginOwned;

use pocketmine\player\Player;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use pocketmine\item\StringToItemParser;

use DayKoala\SakuraSpawners;

use DayKoala\item\GlobalEntityDropsManager;

use DayKoala\item\SpawnerItems;
use DayKoala\item\SpawnerItemsManager;

final class SakuraSpawnersCommand extends Command implements PluginOwned{

    private const PREFIX = "§l§dSPAWNERS §r§d";

    public function __construct(private SakuraSpawners $plugin){
        parent::__construct('spawner', 'main command of SakuraSpawners', '/spawner');

        $this->setPermission('sakuraspawners.command.main');
    }

    public function getOwningPlugin() : SakuraSpawners{
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool{
        if($sender instanceof Player){
            if(!$this->testPermission($sender)){
                return false;
            }
            if(count($args) < 2){
                $sender->sendMessage(self::PREFIX ."Invalid subcommand and entity id");
                return false;
            }
            $subcommand = (string) array_shift($args);
            $entityId = (string) array_shift($args);
            if(is_numeric($entityId)){
                $entityId = LegacyEntityIdToStringIdMap::getInstance()->legacyToString((int) $entityId) ?? ":";
            }
            $data = SakuraSpawners::getGlobalEntityData();
            if(!$data->existsEntity($entityId)){
                $sender->sendMessage(self::PREFIX ."Invalid entity id");
                return false;
            }
            switch(strtolower($subcommand)){
                case "name":
                    $data->setEntityName($entityId, $name = (string) array_shift($args));
                    $sender->sendMessage(self::PREFIX ."Name ". $name ." set to ". $entityId);
                    return true;
                case "size":
                    $data->setEntitySize($entityId, $height = (float) array_shift($args), $width = array_shift($args));
                    $sender->sendMessage(self::PREFIX ."Height ". $height ." Width ". $width ." set to ". $entityId);
                    return true;
                case "scale":
                    $data->setEntityScale($entityId, $scale = (float) array_shift($args));
                    $sender->sendMessage(self::PREFIX ."Scale ". $scale ." set to ". $entityId);
                    return true;
                case "xp":
                    $data->setEntityXPAmount($entityId, $xp = (int) array_shift($args));
                    $sender->sendMessage(self::PREFIX ."XP ". $xp ." set to ". $entityId);
                    return true;
                case "drop":
                    $item = $sender->getInventory()->getItemInHand();
                    if($item->isNull()){
                        $sender->sendMessage(self::PREFIX ."This item isnt valid as a drop");
                        return false;
                    }
                    $name = $item->getVanillaName();
                    if($data->hasEntityDrop($entityId, $name)){
                        $data->removeEntityDrop($entityId, $name);
                        $message = "Item ". $name ." removed from entity ". $entityId;
                    }else{
                        $data->addEntityDrop($entityId, $name, $item->getCount());
                        $message = "Item ". $name ." added to entity ". $entityId;
                    }
                    GlobalEntityDropsManager::writeEntityDrops($entityId);
                    $sender->sendMessage(self::PREFIX . $message);
                    return true;
                case "get":
                    $item = (StringToItemParser::getInstance()->parse("52:". $entityId) ?? SpawnerItemsManager::writeEntityId(SpawnerItems::SPAWNER(), $entityId))->setCount(64);
                    if(!$sender->getInventory()->canAddItem($item)){
                        $sender->sendMessage(self::PREFIX ."Inventory full");
                        return false;
                    }
                    $sender->getInventory()->addItem($item);
                    $sender->sendMessage(self::PREFIX ."Added ". $entityId ." x64 spawners on your inventory");
                    return true;
            }
            $sender->sendMessage(self::PREFIX ."/spawner [name/size/scale/xp/drop/get] [entity-id] (value...)");
            return true;
        }
        $sender->sendMessage(self::PREFIX ."Command valid only in game");
        return true;
    }

}