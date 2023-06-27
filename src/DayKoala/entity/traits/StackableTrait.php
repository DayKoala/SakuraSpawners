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

namespace DayKoala\entity\traits;

trait StackableTrait{

    protected int $maxStack = 300;
    protected int $stack = 1;

    public function canStack() : Bool{
        return $this->stack < $this->maxStack;
    }

    public function getMaxStackSize() : Int{
        return $this->maxStack;
    }

    public function setMaxStackSize(Int $stack) : Void{
        $this->maxStack = $stack < 1 ? 1 : $stack;
    }

    public function getStackSize() : Int{
        return $this->stack;
    }

    public function setStackSize(Int $stack) : Void{
        if($stack > $this->maxStack){
           $stack = $this->maxStack;
        }
        $this->stack = $stack < 0 ? 0 : $stack;
    }

    public function addStackSize(Int $stack) : Void{
        $this->setStackSize($this->stack + $stack);
    }

    public function reduceStackSize(Int $stack) : Void{
        $this->setStackSize($this->stack - $stack);
    }

}