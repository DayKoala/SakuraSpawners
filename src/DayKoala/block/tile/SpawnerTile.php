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
 * @link https://github.com/DayKoala/SakuraSpawners
 * 
 * 
*/

namespace DayKoala\block\tile;

use pocketmine\world\World;
use pocketmine\world\format\Chunk;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\player\Player;

use pocketmine\block\BlockLegacyIds;

use pocketmine\entity\EntityDataHelper as Helper;
use pocketmine\entity\Entity;

use pocketmine\world\particle\MobSpawnParticle;

use DayKoala\entity\SpawnerEntity;

class SpawnerTile extends Spawner{

    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);

        $world->scheduleDelayedBlockUpdate($pos, 1);
    }

    public function canUpdate() : Bool{
        return ($this->entityTypeId !== ":" and $this->getPosition()->getWorld()->getNearestEntity($this->getPosition(), $this->requiredPlayerRange, Player::class) !== null);
    }

    public function onUpdate() : Bool{
        if($this->closed){
           return false;
        }
        $this->timings->startTiming();
        if($this->canUpdate()){
           if($this->spawnDelay <= 0){
              $this->spawnEntity();
              $this->setRandomSpawnDelay();
           }else $this->spawnDelay--;
        }
        $this->timings->stopTiming();
        return true;
    }

    protected function spawnEntity() : Void{
        $entity = $this->getNearestSameEntity();
        if($entity === null){
           $position = $this->getPosition();
           for($attempts = 0; $attempts < $this->spawnAttempts; $attempts++){
              $pos = $position->add(mt_rand(-$this->spawnRange, $this->spawnRange), mt_rand(-1, 1), mt_rand(-$this->spawnRange, $this->spawnRange));
              if(
                 $position->getWorld()->getBlock($pos)->getIdInfo()->getBlockId() !== BlockLegacyIds::AIR or
                 $position->getWorld()->getBlock($pos->subtract(0, 1, 0))->isSolid() == false
              ){
                 continue;
              }
              $entity = new SpawnerEntity(Helper::parseLocation($nbt = $this->readEntitySpawnData($pos), $position->getWorld()), $nbt);
              $entity->spawnToAll();
              $position->getWorld()->addParticle($pos, new MobSpawnParticle((int) $entity->getSize()->getWidth(), (int) $entity->getSize()->getHeight()));
              break;
           }
        }else $entity->addStackSize(1);
    }

    protected function getNearestSameEntity() : ?Entity{
        $pos = $this->getPosition();

        $minX = ((int) floor($pos->x - $this->spawnRange)) >> Chunk::COORD_BIT_SIZE;
        $maxX = ((int) floor($pos->x + $this->spawnRange)) >> Chunk::COORD_BIT_SIZE;
        $minZ = ((int) floor($pos->z - $this->spawnRange)) >> Chunk::COORD_BIT_SIZE;
        $maxZ = ((int) floor($pos->z + $this->spawnRange)) >> Chunk::COORD_BIT_SIZE;

        $target = null;
        for($x = $minX; $x <= $maxX; $x++){
           for($z = $minZ; $z <= $maxZ; $z++){
              if(!$pos->getWorld()->isChunkLoaded($x, $z)){
                 continue;
              }
              foreach($pos->getWorld()->getChunkEntities($x, $z) as $entity){
                 if(!$entity instanceof SpawnerEntity or !$entity->isAlive() or $entity->isFlaggedForDespawn()){
                    continue;
                 }
                 $maxY = (int) floor($pos->y - $entity->getPosition()->y);
                 if($this->entityTypeId !== $entity->getModifiedNetworkTypeId() or $this->spawnRange < $maxY){
                    continue;
                 }
                 $target = $entity;
                 break 3;
              }
           }
        }
        return $target;
    }

    protected function readEntitySpawnData(Vector3 $pos) : CompoundTag{
        return CompoundTag::create()
          ->setString("id", $this->entityTypeId)
          ->setTag("Pos", new ListTag([new DoubleTag($pos->x + 0.5), new DoubleTag($pos->y), new DoubleTag($pos->z + 0.5)]))
          ->setTag("Rotation", new ListTag([new FloatTag(lcg_value() * 360), new FloatTag(0.0)]));
    }

}