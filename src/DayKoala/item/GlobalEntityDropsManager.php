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

use DayKoala\SakuraSpawners;

final class GlobalEntityDropsManager{

    private static array $drops = [];

    public static function isEntityDropsWrited(string $entityId) : bool{
        return isset(self::$drops[$entityId]);
    }

    public static function readEntityDrops(string $entityId) : array{
        return self::$drops[$entityId] ?? [];
    }

    public static function writeEntityDrops(string $entityId) : void{
        self::$drops[$entityId] = [];

        $inputs = SakuraSpawners::getGlobalEntityData()->getEntityDrops($entityId);
        if(count($inputs) < 1){
            return;
        }
        $parser = StringToItemParser::getInstance();
        foreach($inputs as $input => $amount){
            $item = $parser->parse($input);
            if($item instanceof Item) self::$drops[$entityId][] = $item->setCount($amount);
        }
    }

    private function __construct(){}

}