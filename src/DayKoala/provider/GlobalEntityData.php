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

final class GlobalEntityData{

    private Config $config;

    private array $data = [];

    public function __construct(private SakuraSpawners $plugin){}

    public function create() : void{
        $this->config = new Config($this->plugin->getDataFolder() .'Entities.yml', Config::YAML);
        $this->data = $this->config->getAll();
    }

    public function existsEntity(string $entityId) : string{
        return isset($this->data[$entityId]);
    }

    public function getEntityName(string $entityId) : string{
        return isset($this->data[$entityId]["entity.name"]) ? $this->data[$entityId]["entity.name"] : "Unknown Global Entity";
    }

    public function setEntityName(string $entityId, string $name) : void{
        if(isset($this->data[$entityId])) $this->data[$entityId]["entity.name"] = $name;
    }

    public function getEntitySize(string $entityId) : array{
        return isset($this->data[$entityId]["entity.size"]) ? $this->data[$entityId]["entity.size"] : [1.0, 1.0];
    }

    public function setEntitySize(string $entityId, float $height, float $width) : void{
        if(isset($this->data[$entityId])) $this->data[$entityId]["entity.size"] = [$height, $width];
    }

    public function getEntityScale(string $entityId) : float{
        return isset($this->data[$entityId]["entity.scale"]) ? $this->data[$entityId]["entity.scale"] : 1.0;
    }

    public function setEntityScale(string $entityId, float $scale) : void{
        if(isset($this->data[$entityId])) $this->data[$entityId]["entity.scale"] = $scale;
    }

    public function getEntityXPAmount(string $entityId) : int{
        return isset($this->data[$entityId]["entity.xp"]) ? $this->data[$entityId]["entity.xp"] : 0;
    }

    public function setEntityXPAmount(string $entityId, int $xp) : void{
        if(isset($this->data[$entityId])) $this->data[$entityId]["entity.xp"] = $xp;
    }

    public function getEntityDrops(string $entityId) : array{
        return isset($this->data[$entityId]["entity.drops"]) ? $this->data[$entityId]["entity.drops"] : [];
    }

    public function hasEntityDrop(string $entityId, string $item) : bool{
        return isset($this->data[$entityId]["entity.drops"][$item]);
    }

    public function addEntityDrop(string $entityId, string $item, int $amount) : void{
        if(isset($this->data[$entityId])) $this->data[$entityId]["entity.drops"][$item] = $amount;
    }

    public function removeEntityDrop(string $entityId, string $item) : void{
        if(isset($this->data[$entityId]["entity.drops"][$item])) unset($this->data[$entityId]["entity.drops"][$item]);
    }

    public function save() : void{
        $this->config->setAll($this->data);
        $this->config->save();
    }

}