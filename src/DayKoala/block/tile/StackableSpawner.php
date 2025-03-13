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

namespace DayKoala\block\tile;

use pocketmine\nbt\tag\CompoundTag;

use DayKoala\utils\Stackable;

abstract class StackableSpawner extends Spawner implements Stackable{

    protected int $maxStack = 64;
    protected int $currentStack = 1;

    public function hasMaxStackSize() : bool{
        return $this->maxStack <= $this->currentStack;
    }

    public function getMaxStackSize() : int{
        return $this->maxStack;
    }

    public function setMaxStackSize(int $size) : void{
        $this->maxStack = $size < 1 ? 1 : $size;
    }
    
    public function getStackSize() : int{
        return $this->currentStack;
    }

    public function setStackSize(int $size) : void{
        $this->currentStack = $size > $this->maxStack ? $this->maxStack : $size;
    }

    public function addStackSize(int $size) : void{
        $this->setStackSize($this->currentStack + $size);
    }

    public function reduceStackSize(int $size) : void{
        $this->setStackSize($this->currentStack - $size);
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->maxStack = $nbt->getInt("MaxStackSize", 64);
        $this->currentStack = $nbt->getInt("CurrentStackSize", 1);
        parent::readSaveData($nbt);
    }

    public function writeSaveData(CompoundTag $nbt) : void{
        $nbt->setInt("MaxStackSize", $this->maxStack)
            ->setInt("CurrentStackSize", $this->currentStack);
        parent::writeSaveData($nbt);
    }

}