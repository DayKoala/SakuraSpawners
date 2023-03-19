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
 * @link https://github.com/DayKoala/SakuraSpawners
 * 
 * 
*/

namespace DayKoala\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginOwned;

use pocketmine\player\Player;

use pocketmine\item\ItemIds;
use pocketmine\item\StringToItemParser;

use pocketmine\block\BlockLegacyIds;

use DayKoala\utils\SpawnerNames;

use DayKoala\entity\SpawnerEntity;

use DayKoala\SakuraSpawners;

final class SakuraSpawnersCommand extends Command implements PluginOwned{
    
    private const PREFIX = "§l§dSPAWNERS §r§d";

    public function __construct(
        private SakuraSpawners $plugin
    ){
        parent::__construct(
            'spawner',
            'main command of SakuraSpawners',
            '/spawner'
        );
        $this->setPermission('sakuraspawners.command.spawner');
    }

    public function getOwningPlugin() : SakuraSpawners{
        return $this->plugin;
    }

    public function execute(CommandSender $sender, String $label, Array $args) : Bool{
        if($sender instanceof Player){
           if(!$this->testPermission($sender)){
              return false;
           }
           switch(strtolower(array_shift($args))){
              case 'name':
                 if(!isset($args[0])){
                    $sender->sendMessage(self::PREFIX ."Invalid spawner name format.");
                    return false;
                 }
                 $this->plugin->setDefaultSpawnerName(implode(" ", $args));
                 $sender->sendMessage(self::PREFIX ."Spawner name changed.");
                 return true;
              case 'ename':
                 if(!isset($args[0])){
                    $sender->sendMessage(self::PREFIX ."Invalid entity name format.");
                    return false;
                 }
                 $this->plugin->setDefaultEntityName(implode(" ", $args));
                 $sender->sendMessage(self::PREFIX ."Entity name changed.");
                 return true;
              case 'drops':
                 if(!isset($args[0]) or !is_numeric($args[0])){
                    $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                    return false;
                 }
                 $drops = $this->plugin->getSpawnerDrops($args[0]);
                 if(empty($drops)){
                    $sender->sendMessage(self::PREFIX ."Invalid entity drops.");
                    return false;
                 }
                 $message = self::PREFIX . SpawnerNames::getName($args[0]) ." drops: ";
                 foreach($drops as $item){
                    $message .= $item->getName() .", ";
                 }
                 $sender->sendMessage(substr($message, 0, -2));
                 return true;
              case 'adddrop':
                 if(!isset($args[0]) or !is_numeric($args[0])){
                    $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                    return false;
                 }
                 $item = $sender->getInventory()->getItemInHand();
                 if($item->getId() === ItemIds::AIR){
                    $sender->sendMessage(self::PREFIX ."Invalid item in hand.");
                    return false;
                 }
                 $this->plugin->addSpawnerDrop($args[0], $item);
                 $sender->sendMessage(self::PREFIX . SpawnerNames::getName($args[0]) ." drop ". $item->getName() ." added.");
                 return true;
              case 'removedrop':
                 if(!isset($args[0]) or !is_numeric($args[0])){
                    $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                    return false;
                 }
                 $item = $sender->getInventory()->getItemInHand();
                 if(!$this->plugin->hasSpawnerDrop($args[0], $item)){
                    $sender->sendMessage(self::PREFIX ."Invalid item in hand.");
                    return false;
                 }
                 $this->plugin->removeSpawnerDrop($args[0], $item);
                 $sender->sendMessage(self::PREFIX . SpawnerNames::getName($args[0]) ." drop ". $item->getName() ." removed.");
                 return true;
              case 'xp':
                 if(count($args) < 2){
                    $sender->sendMessage(self::PREFIX ."Invalid entity id and xp amount.");
                    return false;
                 }
                 $id = array_shift($args);
                 if(!is_numeric($id)){
                    $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                    return false;
                 }
                 $this->plugin->setSpawnerXp($id, $xp = intval(array_shift($args)));
                 $sender->sendMessage(self::PREFIX . SpawnerNames::getName($id) ." xp drop amount set to ". ($xp < 0 ? 0 : $xp) .".");
                 return true;
              case 'size':
                 if(count($args) < 3){
                    $sender->sendMessage(self::PREFIX ."Invalid entity id, height and width.");
                    return false;
                 }
                 $id = array_shift($args);
                 if(!is_numeric($id)){
                    $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                    return false;
                 }
                 $height = floatval(array_shift($args));
                 $width = floatval(array_shift($args));
                 if($height < 0.5){
                    $height = 0.5;
                 }
                 if($width < 0.5){
                    $width = 0.5;
                 }
                 $this->plugin->setSpawnerSize($id, $height, $width);
                 $sender->sendMessage(self::PREFIX . SpawnerNames::getName($id) ." hitbox set to height: ". $height ." width: ". $width .".");
                 return true;
              case 'give':
                 $id = array_shift($args);
                 if(!is_numeric($id)){
                    $sender->sendMessage(self::PREFIX ."Invalid id.");
                    return false;
                 }
                 $item = StringToItemParser::getInstance()->parse(BlockLegacyIds::MONSTER_SPAWNER .":". $id);
                 if($item === null){
                    $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                    return false;
                 }
                 $player = $sender->getServer()->getPlayerByPrefix(implode(" ", $args)) ?? $sender;
                 if($player !== $sender){
                    $player->sendMessage(self::PREFIX ."received.");
                 }
                 $player->getInventory()->addItem($item);
                 $sender->sendMessage(self::PREFIX ."item received successfully.");
                 return true;
              case 'list':
                 $list = SpawnerNames::getNames();
                 if(empty($list)){
                    $sender->sendMessage(self::PREFIX ."Invalid entities id.");
                    return false;
                 }
                 $message = self::PREFIX ."Entities: ";
                 foreach($list as $id => $name){
                    $message .= "[". $id ."] ". $name .", ";
                 }
                 $sender->sendMessage(substr($message, 0, -2));
                 return true;
              case 'killall':
                 $count = 0;
                 foreach($sender->getWorld()->getEntities() as $entity){
                    if(!$entity instanceof SpawnerEntity){
                       continue;
                    }
                    $entity->close();
                    $count++;
                 }
                 $sender->sendMessage(self::PREFIX . $count ." closed.");
                 return true;
              default:
                 $sender->sendMessage(
                    self::PREFIX ."/spawner name [format] Set spawner name\n".
                    self::PREFIX ."/spawner ename [format] Set entity name\n".
                    self::PREFIX ."/spawner drops [id] See entity drops\n".
                    self::PREFIX ."/spawner adddrop [id] Add entity drop\n".
                    self::PREFIX ."/spawner removedrop [id] Remove entity drop\n".
                    self::PREFIX ."/spawner xp [id] [amount] Set entity xp drop amount\n".
                    self::PREFIX ."/spawner size [id] [height] [width] Set entity hitbox size\n".
                    self::PREFIX ."/spawner give [entity id] (player) Give a spawner to a player\n".
                    self::PREFIX ."/spawner list See spawner list\n".
                    self::PREFIX ."/spawner killall Kill all spawner entities in your world"
                 );
                 return true;
           }
        }
        $sender->sendMessage(self::PREFIX ."In game only.");
        return false;
    }

}