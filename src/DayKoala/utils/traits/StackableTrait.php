<?php

namespace DayKoala\utils\traits;

trait StackableTrait{

    protected int $maxStack = 300;
    protected int $stack = 1;

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
        $this->stack = $stack < 1 ? 1 : $stack;
    }

    public function addStackSize(Int $stack) : Void{
        $this->setStackSize($this->stack + $stack);
    }

    public function reduceStackSize(Int $stack) : Void{
        $this->setStackSize($this->stack - $stack);
    }

}