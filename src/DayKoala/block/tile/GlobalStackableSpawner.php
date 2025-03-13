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

use pocketmine\entity\Location;

use pocketmine\player\Player;

use pocketmine\world\Position;

use pocketmine\world\format\Chunk;

use pocketmine\world\particle\MobSpawnParticle;

use DayKoala\entity\object\SpawnerBlockHolder;

use DayKoala\entity\GlobalEntitySelector;
use DayKoala\entity\StackableEntity;
use DayKoala\entity\GlobalStackableEntity;

class GlobalStackableSpawner extends StackableSpawner{

    protected function getOwningHolder() : SpawnerBlockHolder{
        return new SpawnerBlockHolder(new Location($this->position->x + 0.5, $this->position->y + 1, $this->position->z + 0.5, $this->position->world, lcg_value() * 360, 0), $this);
    }
    
    public function canUpdate() : bool{
        $world = $this->position->world;
        return $world !== null and $world->isLoaded() and $world->getNearestEntity($this->position, $this->requiredPlayerRange, Player::class) !== null;
    }

    public function onUpdate() : bool{
        if($this->closed){
            return false;
        }
        if($this->canUpdate()){
            $this->timings->startTiming();
            if($this->spawnDelay <= 0){
                $this->spawnEntity();
                $this->setRandomSpawnDelay();
            }else{
                $this->spawnDelay--;
            }
        }
        return true;
    }

    protected function spawnEntity() : void{
        $world = $this->position->getWorld();
        for($attempts = 0; $attempts < $this->spawnAttempts; $attempts++){
            $pos = new Position(
                $this->position->x + mt_rand(-$this->spawnRange, $this->spawnRange),
                $this->position->y + mt_rand(0, 1),
                $this->position->z + mt_rand(-$this->spawnRange, $this->spawnRange),
                $world
            );
            if(!$world->isChunkLoaded($pos->x >> Chunk::COORD_BIT_SIZE, $pos->z >> Chunk::COORD_BIT_SIZE)){
                continue;
            }
            $entityClass = GlobalEntitySelector::getInstance()->getName($this->entityId);
            if(is_subclass_of($entityClass, StackableEntity::class)){
                $minX = ($this->position->x - $this->spawnRange) >> Chunk::COORD_BIT_SIZE;
                $maxX = ($this->position->x + $this->spawnRange) >> Chunk::COORD_BIT_SIZE;
                $minZ = ($this->position->z - $this->spawnRange) >> Chunk::COORD_BIT_SIZE;
                $maxZ = ($this->position->z + $this->spawnRange) >> Chunk::COORD_BIT_SIZE;
                for($x = $minX; $x <= $maxX; $x++){
                    for($z = $minZ; $z <= $maxZ; $z++){
                        if(!$world->isChunkLoaded($x, $z)){
                            continue;
                        }
                        foreach($world->getChunkEntities($x, $z) as $target){
                            if(
                                !$target instanceof StackableEntity or
                                !$target->isAlive() or 
                                $target->isFlaggedForDespawn() or 
                                $target instanceof GlobalStackableEntity and
                                $target->getEntityId() !== $this->entityId
                            ){
                                continue;
                            }
                            if(
                                $target::class !== $entityClass or
                                $target->hasMaxStackSize()
                            ){
                                return;
                            }
                            if($target->getLocation()->distance($this->position) < ($this->spawnRange * 3)){
                               $target->addStackSize($this->currentStack);
                               return;
                            }
                        }
                    }
                }
            }
            $entity = GlobalEntitySelector::getInstance()->get($this->entityId, $pos);
            if($entity === null){
                return;
            }
            if($entity instanceof StackableEntity){
                $entity->addStackSize($this->currentStack - 1);
            }
            $entity->spawnToAll();
            $world->addParticle($pos, new MobSpawnParticle((int) $entity->getSize()->getWidth(), (int) $entity->getSize()->getHeight()));
            break;
        }
    }

}