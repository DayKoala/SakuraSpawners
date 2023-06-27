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

use DayKoala\SakuraSpawners;

final class SpawnerNames{

    private array $names;

    public function __construct(
        private SakuraSpawners $plugin
    ){
        $plugin->saveResource('Names.yml');
        $this->loadNames();
    }

    public function getNames() : Array{
        return (array) $this->names;
    }

    public function hasName(Int $id) : Bool{
        return isset($this->names[$id]);
    }

    public function getName(Int $id) : String{
        return $this->names[$id] ?? 'Unknown';
    }

    public function setName(Int $id, String $name) : Void{
        $this->names[$id] = $name;
    }

    public function saveNames() : Void{
        if(empty($this->names)){
            return;
        }
        $names = new Config($this->plugin->getDataFolder() .'Names.yml', Config::YAML);
        $names->setAll($this->names);
        $names->save();
    }

    public function loadNames() : Void{
        $this->names = (new Config($this->plugin->getDataFolder() .'Names.yml', Config::YAML))->getAll();
    }

}