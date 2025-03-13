<p align="center">
  <a href="https://github.com/DayKoala/SakuraSpawners/stargazers"><img src="https://i.ibb.co/yN6gcXR/Sakura-Spawners-Gif.gif"></img></a><br>
</p>
<p align="center">
  <img alt= "Last Commit" src= "https://img.shields.io/github/last-commit/DayKoala/SakuraSpawners?color=green">
  <img alt= "Poggit" src="https://poggit.pmmp.io/shield.dl.total/SakuraSpawners"></a>
</p>

# About
- **[SakuraSpawners](https://github.com/DayKoala/SakuraSpawners)** is a plugin for **[PocketMine-MP](https://github.com/pmmp/PocketMine-MP)** which adds the Monster Spawner and it's functionalities as in vanilla Minecraft with some extra tweaks and features.

### Command

| Command | Description | Permission |
| --- | --- | --- |
| `/spawner [subcommand] [entity-id]` | Main command | **sakuraspawners.command.main** |

- **Subcommands**
  - `name [name]`: **Changes entity Name.**
  - `size [height] [width]`: **Changes entity hitbox Height and Width.**
  - `scale [scale]`: **Changes entity Scale.**
  - `xp [amount]`: **Changes entity XP Drop Amount when killed.**
  - `drop`: **Adds or removes item from player's hand as a Drop.**
  - `get`: **Adds entity Monster Spawner in player's inventory.**

> Subcommands values are set after `[entity-id]`

- All changes are updated in the entity upon server restart or when a new one is created and their data saved in **[Entities.yml](https://github.com/DayKoala/SakuraSpawners/blob/main/resources/Entities.yml)** .

### Properties

- Some usage properties can be changed in **[Properties.yml](https://github.com/DayKoala/SakuraSpawners/blob/main/resources/Properties.yml)** .

> They won't change if the server is running

# Developers

- How can i add my own entity to a Monster Spawner ?

### Example

```php

use DayKoala\entity\GlobalEntitySelector;

GlobalEntitySelector::getInstance()->register("MyEntityId", MyEntity::class, function(Position $position, string $entityId) : MyEntity{
  return new MyEntity(...);
});

```

> You also can take a look at **[GlobalEntitySelector](https://github.com/DayKoala/SakuraSpawners/blob/main/DayKoala/entity/GlobalEntitySelector.php)** for guidance

- How can i get the Monster Spawner Block or Item ?

### Example

```php

use DayKoala\item\SpawnerItems;
use DayKoala\item\SpawnerItemsManager;

// Block
SpawnerItems::MONSTER_SPAWNER();

// Item
SpawnerItems::SPAWNER();

// Item with Entity Identifier
SpawnerItemsManager::writeEntityId(SpawnerItems::SPAWNER(), "entityId");

```

> Also take a loot at **[SpawnerItems](https://github.com/DayKoala/SakuraSpawners/blob/main/DayKoala/item/SpawnerItems.php)** and **[SpawnerItemsManager](https://github.com/DayKoala/SakuraSpawners/blob/main/DayKoala/item/SpawnerItemsManager.php)** in case of doubts