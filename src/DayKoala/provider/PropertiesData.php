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

namespace DayKoala\provider;

use pocketmine\utils\Config;

use DayKoala\SakuraSpawners;

final class PropertiesData{

    private Config $config;

    private array $data = [];

    public function __construct(private SakuraSpawners $plugin){}

    public function create() : void{
        $this->config = new Config($this->plugin->getDataFolder() ."Properties.yml", Config::YAML);
        $this->data = $this->config->getAll();
    }

    public function getString(string $key) : string{
        return isset($this->data[$key]) ? (string) $this->data[$key] : "";
    }

    public function setString(string $key, string $value) : void{
        $this->data[$key] = $value;
    }

    public function getInt(string $key) : int{
        return isset($this->data[$key]) ? (int) $this->data[$key] : 0;
    }

    public function setInt(string $key, int $value) : void{
        $this->data[$key] = $value;
    }

    public function getFloat(string $key) : float{
        return isset($this->data[$key]) ? (float) $this->data[$key] : 0;
    }

    public function setFloat(string $key, float $value) : void{
        $this->data[$key] = $value;
    }
    public function save() : void{
        $this->config->setAll($this->data);
        $this->config->save();
    }

}