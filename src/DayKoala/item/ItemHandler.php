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

use pocketmine\world\format\io\GlobalItemDataHandlers as Handler;

use pocketmine\item\{StringToItemParser, Item, LegacyStringToItemParser, VanillaItems};

use pocketmine\data\bedrock\item\SavedItemData;

final class ItemHandler{

    public static function registerItem(String $id, Item $item, Array $names) : Void{
        Handler::getDeserializer()->map($id, fn() => clone $item);
		Handler::getSerializer()->map($item, fn() => new SavedItemData($id));

		foreach($names as $name) StringToItemParser::getInstance()->override($name, fn() => clone $item);
    }

    public static function fromItem(Item $item) : String{
        // ...
        return str_replace(' ', '_', $item->getVanillaName()) .':'. $item->getCount();
    }

    public static function fromString(String $item) : Item{
        // ...
        $args = explode(':', $item);

        $id = $args[0] ?? '0';
        $count = $args[1] ?? '1';

        $item = StringToItemParser::getInstance()->parse($id) ?? LegacyStringToItemParser::getInstance()->parse($id);
        return $item !== null ? $item->setCount($count) : VanillaItems::AIR();
    }

    private function __construct(){}

}