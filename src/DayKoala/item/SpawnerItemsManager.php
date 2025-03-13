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

namespace DayKoala\item;

use pocketmine\item\StringToItemParser;
use pocketmine\item\Item;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;

use DayKoala\SakuraSpawners;

use DayKoala\entity\GlobalEntitySelector;

final class SpawnerItemsManager{

    public static function registerAll() : void{
        $parser = StringToItemParser::getInstance();
        $data = SakuraSpawners::getGlobalEntityData();
        $format = SakuraSpawners::getPropertiesData()->getString("SPAWNER.ITEM.NAME.FORMAT");
        foreach(LegacyEntityIdToStringIdMap::getInstance()->getLegacyToStringMap() as $legacy => $current){
            $callback = function() use ($current, $data, $format) : Item{
                $item = self::writeEntityId(SpawnerItems::SPAWNER(), $current);
                return $item->setCustomName(str_replace("{name}", $data->getEntityName($current), $format));
            };
            $parser->override("52:". $legacy, $callback);
            $parser->override("52:". $current, $callback);
        }
    }

    public static function writeEntityId(Item $item, string $entityId) : Item{
        $namedtag = $item->getNamedTag();
        $namedtag->setString(GlobalEntitySelector::TAG_ENTITY_ID, $entityId);
        $item->setNamedTag($namedtag);
        return $item;
    }

    private function __construct(){}

}