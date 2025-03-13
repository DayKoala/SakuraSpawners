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

use pocketmine\block\tile\Spawnable;

use pocketmine\world\World;

use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

use pocketmine\nbt\tag\CompoundTag;

use DayKoala\entity\SpawnerHolder;

abstract class Spawner extends Spawnable{

    protected const TAG_ENTITY_ID = "EntityIdentifier";
    
    protected const TAG_SPAWN_DELAY = "Delay";
    protected const TAG_MIN_SPAWN_DELAY = "MinSpawnDelay";
    protected const TAG_MAX_SPAWN_DELAY = "MaxSpawnDelay";
    
    protected const TAG_SPAWN_RANGE = "SpawnRange";
    protected const TAG_REQUIRED_PLAYER_RANGE = "RequiredPlayerRange";
    
    protected const TAG_SPAWN_ATTEMPTS = "SpawnAttempts";

    protected ?SpawnerHolder $holder = null;

    protected string $entityId = ":";

    protected int $spawnDelay = 200;
    protected int $minSpawnDelay = 200;
    protected int $maxSpawnDelay = 400;

    protected int $spawnRange = 4;
    protected int $spawnAttempts = 5;

    protected int $requiredPlayerRange = 16;

    abstract protected function getOwningHolder() : SpawnerHolder;

    abstract public function onUpdate() : bool;

    abstract public function canUpdate() : bool;

    abstract protected function spawnEntity() : void;

    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);

        $this->holder = $this->getOwningHolder();
        if($this->holder instanceof Entity) $this->holder->spawnToAll();
    }

    public function getHolder() : ?SpawnerHolder{
        return $this->holder;
    }

    public function getEntityId() : string{
        return $this->entityId;
    }

    public function setEntityId(String $id) : void{
        $this->entityId = $id;
    }

    public function getSpawnDelay() : int{
        return $this->spawnDelay;
    }

    public function setSpawnDelay(int $delay) : void{
        $this->spawnDelay = $delay;
    }

    public function setRandomSpawnDelay() : void{
        $this->spawnDelay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
    }

    public function getMaxSpawnDelay() : int{
        return $this->maxSpawnDelay;
    }

    public function setMaxSpawnDelay(int $delay) : void{
        $this->maxSpawnDelay = $delay;
    }

    public function getMinSpawnDelay() : int{
        return $this->minSpawnDelay;
    }

    public function setMinSpawnDelay(int $delay) : void{
        $this->minSpawnDelay = $delay;
    }

    public function getSpawnRange() : int{
        return $this->spawnRange;
    }

    public function setSpawnRange(int $range) : void{
        $this->spawnRange = $range;
    }

    public function getSpawnAttempts() : int{
        return $this->spawnAttempts;
    }

    public function setSpawnAttempts(int $attempts) : void{
        $this->spawnAttempts = $attempts;
    }

    public function getRequiredPlayerRange() : int{
        return $this->requiredPlayerRange;
    }

    public function setRequiredPlayerRange(int $range) : void{
        $this->requiredPlayerRange = $range;
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->entityId = $nbt->getString(self::TAG_ENTITY_ID, ":");
        $this->spawnDelay = $nbt->getShort(self::TAG_SPAWN_DELAY, 200);
        $this->minSpawnDelay = $nbt->getShort(self::TAG_MIN_SPAWN_DELAY, 200);
        $this->maxSpawnDelay = $nbt->getShort(self::TAG_MAX_SPAWN_DELAY, 400);
        $this->spawnRange = $nbt->getShort(self::TAG_SPAWN_RANGE, 4);
        $this->spawnAttempts = $nbt->getShort(self::TAG_SPAWN_ATTEMPTS, 5);
        $this->requiredPlayerRange = $nbt->getShort(self::TAG_REQUIRED_PLAYER_RANGE, 16);
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $nbt->setString(self::TAG_ENTITY_ID, $this->entityId)
            ->setShort(self::TAG_SPAWN_DELAY, $this->spawnDelay)
            ->setShort(self::TAG_MIN_SPAWN_DELAY, $this->minSpawnDelay)
            ->setShort(self::TAG_MAX_SPAWN_DELAY, $this->maxSpawnDelay)
            ->setShort(self::TAG_SPAWN_RANGE,  $this->spawnRange)
            ->setShort(self::TAG_SPAWN_ATTEMPTS, $this->spawnAttempts)
            ->setShort(self::TAG_REQUIRED_PLAYER_RANGE, $this->requiredPlayerRange);
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        $nbt->setString(self::TAG_ENTITY_ID, $this->entityId);
    }

}