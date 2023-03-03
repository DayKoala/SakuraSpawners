<?php

namespace DayKoala\block\tile;

use pocketmine\block\tile\Spawnable;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

abstract class Spawner extends Spawnable{

    protected const TAG_LEGACY_ENTITY_TYPE_ID = "EntityId";
    protected const TAG_ENTITY_TYPE_ID = "EntityIdentifier";

    protected const TAG_SPAWN_DELAY = "Delay";
    protected const TAG_MIN_SPAWN_DELAY = "MinSpawnDelay";
    protected const TAG_MAX_SPAWN_DELAY = "MaxSpawnDelay";

    protected const TAG_SPAWN_RANGE = "SpawnRange";
    protected const TAG_REQUIRED_PLAYER_RANGE = "RequiredPlayerRange";

    protected const TAG_SPAWN_ATTEMPTS = "SpawnAttempts";

    protected const DEFAULT_MIN_SPAWN_DELAY = 10;
    protected const DEFAULT_MAX_SPAWN_DELAY = 100;

    protected const DEFAULT_SPAWN_RANGE = 4;
    protected const DEFAULT_REQUIRED_PLAYER_RANGE = 16;
    
    protected const DEFAULT_SPAWN_ATTEMPTS = 5;

    protected int $legacyEntityTypeId = 0;
    protected string $entityTypeId = ":";

    protected int $spawnDelay = self::DEFAULT_MIN_SPAWN_DELAY;
    protected int $minSpawnDelay = self::DEFAULT_MIN_SPAWN_DELAY;
    protected int $maxSpawnDelay = self::DEFAULT_MAX_SPAWN_DELAY;

    protected int $spawnRange = self::DEFAULT_SPAWN_RANGE;
    protected int $requiredPlayerRange = self::DEFAULT_REQUIRED_PLAYER_RANGE;

    protected int $spawnAttempts = self::DEFAULT_SPAWN_ATTEMPTS;

    abstract public function canUpdate() : Bool;

    abstract public function onUpdate() : Bool;

    abstract protected function readEntitySpawnData(Vector3 $pos) : CompoundTag;

    public function getLegacyEntityId() : Int{
        return $this->legacyEntityTypeId;
    }

    public function getEntityId() : String{
        return $this->entityTypeId;
    }

    public function setEntityId(String $id) : Void{
        $this->legacyEntityTypeId = LegacyEntityIdToStringIdMap::getInstance()->stringToLegacy($this->entityTypeId = $id) ?? 0;

        if($id !== ":") $this->clearSpawnCompoundCache();
    }

    public function getSpawnDelay() : Int{
        return $this->spawnDelay;
    }

    public function setSpawnDelay(Int $delay) : Void{
        $this->spawnDelay = $delay < 0 ? 0 : $delay;
    }

    public function setRandomSpawnDelay() : Void{
        $this->spawnDelay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
    }

    public function getMinSpawnDelay() : Int{
        return $this->minSpawnDelay;
    }

    public function setMinSpawnDelay(Int $delay) : Void{
        $this->minSpawnDelay = $delay < self::DEFAULT_MIN_SPAWN_DELAY ? self::DEFAULT_MIN_SPAWN_DELAY : $delay;
    }

    public function getMaxSpawnDelay() : Int{
        return $this->maxSpawnDelay;
    }

    public function setMaxSpawnDelay(Int $delay) : Void{
        $this->maxSpawnDelay = $delay < self::DEFAULT_MAX_SPAWN_DELAY ? self::DEFAULT_MAX_SPAWN_DELAY : $delay;
    }

    public function getSpawnRange() : Int{
        return $this->spawnRange;
    }

    public function setSpawnRange(Int $range) : Void{
        $this->spawnRange = $range < self::DEFAULT_SPAWN_RANGE ? self::DEFAULT_SPAWN_RANGE : $range;
    }

    public function getSpawnAttempts() : Int{
        return $this->spawnAttempts;
    }

    public function setSpawnAttempts(Int $attempts) : Void{
        $this->spawnAttempts = $attempts < self::DEFAULT_SPAWN_ATTEMPTS ? self::DEFAULT_SPAWN_ATTEMPTS : $attempts;
    }

    public function getRequiredPlayerRange() : Int{
        return $this->requiredPlayerRange;
    }

    public function setRequiredPlayerRange(Int $range) : Void{
        $this->requiredPlayerRange = $range < self::DEFAULT_REQUIRED_PLAYER_RANGE ? self::DEFAULT_REQUIRED_PLAYER_RANGE : $range;
    }

    public function readSaveData(CompoundTag $nbt) : Void{
        $legacyIdTag = $nbt->getTag(self::TAG_LEGACY_ENTITY_TYPE_ID);
        if($legacyIdTag instanceof IntTag){
           $this->entityTypeId = LegacyEntityIdToStringIdMap::getInstance()->legacyToString($this->legacyEntityTypeId = $legacyIdTag->getValue()) ?? ":";
        }else{
           $this->legacyEntityTypeId = LegacyEntityIdToStringIdMap::getInstance()->stringToLegacy($this->entityTypeId = $nbt->getString(self::TAG_ENTITY_TYPE_ID, 0)) ?? 0;
        }
        $this->spawnDelay = $nbt->getShort(self::TAG_SPAWN_DELAY, self::DEFAULT_MIN_SPAWN_DELAY);
        $this->minSpawnDelay = $nbt->getShort(self::TAG_MIN_SPAWN_DELAY, self::DEFAULT_MIN_SPAWN_DELAY);
        $this->maxSpawnDelay = $nbt->getShort(self::TAG_MAX_SPAWN_DELAY, self::DEFAULT_MAX_SPAWN_DELAY);
        $this->spawnRange = $nbt->getShort(self::TAG_SPAWN_RANGE, self::DEFAULT_SPAWN_RANGE);
        $this->spawnAttempts = $nbt->getShort(self::TAG_SPAWN_ATTEMPTS, self::DEFAULT_SPAWN_ATTEMPTS);
        $this->requiredPlayerRange = $nbt->getShort(self::TAG_REQUIRED_PLAYER_RANGE, self::DEFAULT_REQUIRED_PLAYER_RANGE);
    }

    protected function writeSaveData(CompoundTag $nbt) : Void{
        $nbt->setString(self::TAG_ENTITY_TYPE_ID, $this->entityTypeId);
        $nbt->setShort(self::TAG_SPAWN_DELAY, $this->spawnDelay);
        $nbt->setShort(self::TAG_MIN_SPAWN_DELAY, $this->minSpawnDelay);
        $nbt->setShort(self::TAG_MAX_SPAWN_DELAY, $this->maxSpawnDelay);
        $nbt->setShort(self::TAG_SPAWN_RANGE, $this->spawnRange);
        $nbt->setShort(self::TAG_SPAWN_ATTEMPTS, $this->spawnAttempts);
        $nbt->getShort(self::TAG_REQUIRED_PLAYER_RANGE, $this->requiredPlayerRange);
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : Void{
        $nbt->setString(self::TAG_ENTITY_TYPE_ID, $this->entityTypeId);
    }

}