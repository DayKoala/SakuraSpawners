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

use DayKoala\SakuraSpawners;

use DayKoala\utils\SpawnerSettings;

use DayKoala\entity\SpawnerEntity;

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
        $this->setPermission('sakuraspawners.command.main');
    }

    public function getOwningPlugin() : SakuraSpawners{ return $this->plugin; }

    public function execute(CommandSender $sender, String $label, Array $args) : Bool{
        if($sender instanceof Player){
            if(!$this->testPermission($sender)){
                return false;
            }
            switch(strtolower((string) array_shift($args))){
                case 'spname':
                    if(!isset($args[0])){
                        $sender->sendMessage(self::PREFIX ."Invalid spawner name format.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->setDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_NAME, implode(" ", $args));
                    $sender->sendMessage(self::PREFIX ."Spawner name changed.");
                    return true;
                case 'egname':
                    if(!isset($args[0])){
                        $sender->sendMessage(self::PREFIX ."Invalid egg name format.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->setDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_EGG_NAME, implode(" ", $args));
                    $sender->sendMessage(self::PREFIX ."Egg name changed.");
                    return true;
                case 'ename':
                    if(!isset($args[0])){
                        $sender->sendMessage(self::PREFIX ."Invalid entity name format.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->setDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_NAME, implode(" ", $args));
                    $sender->sendMessage(self::PREFIX ."Entity name changed.");
                    return true;
                case 'sprange':
                    if(!isset($args[0]) or !is_numeric($args[0])){
                        $sender->sendMessage(self::PREFIX ."Invalid spawner spawn range.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->setDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_SPAWN_DISTANCE, (int) $args[0]);
                    $sender->sendMessage(self::PREFIX ."Spawner spawn range changed to ". $args[0] .".");
                    return true;
                case 'strange':
                    if(!isset($args[0]) or !is_numeric($args[0])){
                        $sender->sendMessage(self::PREFIX ."Invalid spawner stack range.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->setDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_STACK_DISTANCE, (int) $args[0]);
                    $sender->sendMessage(self::PREFIX ."Spawner stack range changed to ". $args[0] .".");
                    return true;
                case 'drops':
                    if(!isset($args[0]) or !is_numeric($args[0])){
                        $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                        return false;
                    }
                    $drops = SakuraSpawners::getSettings()->getEntityDrops((int) $args[0]);
                    if(empty($drops)){
                        $sender->sendMessage(self::PREFIX ."Invalid entity drops.");
                        return false;
                    }
                    $message = self::PREFIX . $args[0] ." Drops: ";
                    foreach($drops as $item){
                        $message .= $item->getName() .", ";
                    }
                    $sender->sendMessage(substr($message, 0, -2));
                    return true;
                case 'adrop':
                    if(!isset($args[0]) or !is_numeric($args[0])){
                        $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                        return false;
                    }
                    $item = $sender->getInventory()->getItemInHand();
                    if($item->isNull()){
                        $sender->sendMessage(self::PREFIX ."Invalid item in hand.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->addEntityDrop((int) $args[0], $item);
                    $sender->sendMessage(self::PREFIX . $args[0] ." drop ". $item->getName() ." added.");
                    return true;
                case 'rdrop':
                    if(!isset($args[0]) or !is_numeric($args[0])){
                        $sender->sendMessage(self::PREFIX ."Invalid entity id.");
                        return false;
                    }
                    $item = $sender->getInventory()->getItemInHand();
                    if(!SakuraSpawners::getSettings()->hasEntityDrop((int) $args[0], $item)){
                        $sender->sendMessage(self::PREFIX ."Invalid item in hand.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->removeEntityDrop((int) $args[0], $item);
                    $sender->sendMessage(self::PREFIX . $args[0] ." drop ". $item->getName() ." removed.");
                    return true;
                case 'size':
                    $id = array_shift($args);
                    $height = array_shift($args);
                    $width = array_shift($args);
                    if(
                        !is_numeric($id) or
                        !is_numeric($height) or
                        !is_numeric($width)
                    ){
                        $sender->sendMessage(self::PREFIX ."Invalid entity id, height or width.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->setEntitySize((int) $id, (float) $height, (float) $width);
                    $sender->sendMessage(self::PREFIX . $id ." hitbox set to height: ". $height ." width: ". $width .".");
                    return true;
                case 'xp':
                    $id = array_shift($args);
                    $xp = array_shift($args);
                    if(
                        !is_numeric($id) or
                        !is_numeric($xp)
                    ){
                        $sender->sendMessage(self::PREFIX ."Invalid entity id or xp amount.");
                        return false;
                    }
                    SakuraSpawners::getSettings()->setEntityXp((int) $id, (int) $xp);
                    $sender->sendMessage(self::PREFIX . $id ." xp drop amount set to ". $xp .".");
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
                       self::PREFIX ."/spawner spname [format] Set spawner name\n".
                       self::PREFIX ."/spawner egname [format] Set egg name\n".
                       self::PREFIX ."/spawner ename [format] Set entity name\n".
                       self::PREFIX ."/spawner sprange [range] Set spawner spawn range\n".
                       self::PREFIX ."/spawner strange [range] Set spawner stack range\n".
                       self::PREFIX ."/spawner drops [id] See entity drops\n".
                       self::PREFIX ."/spawner adrop [id] Add entity drop\n".
                       self::PREFIX ."/spawner rdrop [id] Remove entity drop\n".
                       self::PREFIX ."/spawner size [id] [height] [width] Set entity hitbox size\n".
                       self::PREFIX ."/spawner xp [id] [amount] Set entity xp drop amount\n".
                       self::PREFIX ."/spawner killall Kill all spawner entities in your world"
                    );
                    return true;
            }
        }
        $sender->sendMessage(self::PREFIX ."In game only.");
        return false;
    }

}