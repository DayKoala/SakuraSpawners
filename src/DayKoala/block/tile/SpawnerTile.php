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

use pocketmine\world\World;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;

use pocketmine\math\Vector3;

use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\ClosureTask;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\player\Player;

use pocketmine\entity\EntityDataHelper as Helper;

use pocketmine\world\particle\MobSpawnParticle;

use DayKoala\SakuraSpawners;

use DayKoala\utils\SpawnerSettings;

use DayKoala\entity\SpawnerEntity;

class SpawnerTile extends Spawner{

    protected int $stackRange = 0;

    private ?TaskHandler $handler = null;

    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);

        $this->spawnRange = ($settings = SakuraSpawners::getSettings())->getDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_SPAWN_DISTANCE);
        $this->stackRange = $settings->getDefault(SpawnerSettings::TAG_DEFAULT_SPAWNER_ENTITY_STACK_DISTANCE);

        #$world->scheduleDelayedBlockUpdate($pos, 1);

        $tile = $this;
        $this->handler = SakuraSpawners::getInstance()->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(
                function() use ($tile){
                    if($tile->canUpdate()) $tile->onUpdate();
                }
            ), 1
        );

    }

    public function canUpdate() : Bool{
        return (
            $this->entityTypeId !== ":" and
            $this->getPosition()->getWorld()->getNearestEntity($this->getPosition(), $this->requiredPlayerRange, Player::class) !== null
        );
    }

    public function onUpdate() : Bool{
        if($this->closed){
            $this->handler->cancel();
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
        if(
            $entity == null or
            !$entity->canStack()
        ){
            $world = ($position = $this->getPosition())->getWorld();
            for($attempts = 0; $attempts < $this->spawnAttempts; $attempts++){
                $pos = $position->add(mt_rand(-$this->spawnRange, $this->spawnRange), mt_rand(-1, 1), mt_rand(-$this->spawnRange, $this->spawnRange));
                if(
                    $world->getBlock($pos)->isSolid() and
                    !$world->getBlock($pos)->canBeFlowedInto() or
                    !$world->getBlock($pos->subtract(0, 1, 0))->isSolid()
                ){
                    continue;
                }
                $entity = new SpawnerEntity(Helper::parseLocation($nbt = $this->readEntitySpawnData($pos), $world), $nbt);
                $entity->spawnToAll();
                $world->addParticle($pos, new MobSpawnParticle((int) $entity->getSize()->getWidth(), (int) $entity->getSize()->getHeight()));
                break;
            }
        }else $entity->addStackSize(1);
    }

    protected function getNearestSameEntity(?Position $pos = null) : ?SpawnerEntity{
        if($pos === null){
            $pos = $this->getPosition();
        }

        $world = $pos->getWorld();

        $minX = ((int) floor($pos->x - $this->stackRange)) >> Chunk::COORD_BIT_SIZE;
        $maxX = ((int) floor($pos->x + $this->stackRange)) >> Chunk::COORD_BIT_SIZE;
        $minZ = ((int) floor($pos->z - $this->stackRange)) >> Chunk::COORD_BIT_SIZE;
        $maxZ = ((int) floor($pos->z + $this->stackRange)) >> Chunk::COORD_BIT_SIZE;

        $target = null;

        for($x = $minX; $x <= $maxX; $x++){
            for($z = $minZ; $z <= $maxZ; $z++){
                if(!$world->isChunkLoaded($x, $z)){
                    continue;
                }
                foreach($world->getChunkEntities($x, $z) as $entity){
                    if(
                        !$entity instanceof SpawnerEntity or
                        !$entity->isAlive() or
                        $entity->isFlaggedForDespawn()
                    ){
                        continue;
                    }
                    $maxY = (int) floor($pos->y - $entity->getPosition()->y);
                    if(
                        $this->legacyEntityTypeId !== $entity->getLegacyNetworkId() or
                        $this->stackRange < $maxY
                    ){
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
        ->setTag("Pos", new ListTag([new DoubleTag($pos->x + 0.5), new DoubleTag($pos->y + 0.2), new DoubleTag($pos->z + 0.5)]))
        ->setTag("Rotation", new ListTag([new FloatTag(lcg_value() * 360), new FloatTag(0.0)]));
    }

}