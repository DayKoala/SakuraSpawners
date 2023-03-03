<?php

namespace DayKoala\utils;

use pocketmine\utils\Config;

final class SpawnerNames{

    private static $names = [];

    private static $defaultNames = [
        10 => 'Chicken',
        11 => 'Cow',
        12 => 'Pig',
        13 => 'Sheep',
        14 => 'Wolf',
        15 => 'Villager', // V1
        16 => 'Mooshroom',
        17 => 'Squid',
        18 => 'Rabbit',
        19 => 'Bat',
        20 => 'Iron Golem',
        21 => 'Snow Golem',
        22 => 'Ocelot',
        23 => 'Horse',
        24 => 'Donkey',
        25 => 'Mule',
        26 => 'Skeleton Horse',
        27 => 'Zombie Horse',
        28 => 'Polar Bear',
        29 => 'Llama',
        30 => 'Parrot',
        31 => 'Dolphin',
        32 => 'Zombie',
        33 => 'Creeper',
        34 => 'Skeleton',
        35 => 'Spider',
        36 => 'Zombie Pigman',
        37 => 'Slime',
        38 => 'Enderman',
        39 => 'Silverfish',
        40 => 'Cave Spider',
        41 => 'Ghast',
        42 => 'Magma Cube',
        43 => 'Blaze',
        44 => 'Zombie Villager', // V1
        45 => 'Witch',
        46 => 'Stray',
        47 => 'Husk',
        48 => 'Wither Skeleton',
        49 => 'Guardian',
        50 => 'Elder Guardian',
        51 => 'NPC',
        52 => 'Wither',
        53 => 'Ender Dragon',
        54 => 'Shulker',
        55 => 'Endermite',
        56 => 'Agent',
        57 => 'Vindicator',
        58 => 'Phantom',
        59 => 'Ravanger', 
        62 => 'Tripod Camera',
        68 => 'XP Bottle',
        71 => 'Ender Crystal',
        72 => 'Fireworks',
        74 => 'Turtle',
        75 => 'Cat',
        78 => 'Chalkboard',
        85 => 'Fireball',
        93 => 'Lightning Bolt',
        94 => 'Small Fireball',
        103 => 'Evocation Fang',
        104 => 'Evocation Illager',
        105 => 'Vex',
        109 => 'Salmon',
        110 => 'Drowned',
        111 => 'Tropicalfish',
        112 => 'Cod',
        113 => 'Panda',
        114 => 'Pillager',
        115 => 'Villager', // V2
        118 => 'Wandering Trader',
        116 => 'Zombie Villager', // V2
        121 => 'Fox',
        122 => 'Bee',
        123 => 'Piglin',
        124 => 'Hoglin',
        125 => 'Strider',
        126 => 'Zoglin',
        127 => 'Piglin Brute',
        128 => 'Goat',
        131 => 'Warden',
        132 => 'Frog',
        133 => 'Tadpole',
        134 => 'Allay'
    ];

    public static function init(String $folder) : Void{
        self::$names = (new Config($folder .'Names.yml', Config::YAML))->getAll();
    }

    public static function hasName(Int $id) : Bool{
        return isset(self::$names[$id]);
    }

    public static function getName(Int $id) : String{
        return self::$names[$id] ?? self::getDefaultName($id);
    }

    public static function hasDefaultName(Int $id) : Bool{
        return isset(self::$defaultNames[$id]);
    }

    public static function getDefaultName(Int $id) : String{
        return self::$defaultNames[$id] ?? 'Unknown';
    }

    private function __construct(){}

} 