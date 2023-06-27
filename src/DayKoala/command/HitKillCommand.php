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
use DayKoala\SakuraSpawnersListener as Handler;

final class HitKillCommand extends Command implements PluginOwned{

    private const PREFIX = "§l§dHITKILL §r§d";

    public function __construct(
        private SakuraSpawners $plugin
    ){
        parent::__construct(
            'hitkill',
            'hitkill command for spawner entities of SakuraSpawners',
            '/hitkill'
        );
        $this->setPermission('sakuraspawners.command.hitkill');
    }

    public function getOwningPlugin() : SakuraSpawners{ return $this->plugin; }

    public function execute(CommandSender $sender, String $label, Array $args) : Bool{
        if($sender instanceof Player){
            if(!$this->testPermission($sender)){
                return false;
            }
            if(Handler::hasKiller($sender)){
                Handler::removeKiller($sender);
                $message = self::PREFIX ."Hitkill disabled.";
            }else{
                Handler::addKiller($sender);
                $message = self::PREFIX ."Hitkill enabled.";
            }
            $sender->sendMessage($message);
            return true;
        }
        $sender->sendMessage(self::PREFIX ."In game only.");
        return false;
    }

}