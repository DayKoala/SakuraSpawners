<p align="center">
  <a href="https://github.com/DayKoala/SakuraSpawners/stargazers"><img src="https://i.ibb.co/yN6gcXR/Sakura-Spawners-Gif.gif"></img></a><br>
</p>
<p align="center">
  <img alt= "Last Commit" src= "https://img.shields.io/github/last-commit/DayKoala/SakuraSpawners?color=green">
  <img alt= "Poggit" src="https://poggit.pmmp.io/shield.dl.total/SakuraSpawners"></a>
</p>

# About

- **[SakuraSpawners](https://github.com/DayKoala/SakuraSpawners)** is a plugin which adds the functionalities of the Monster Spawner block with some different and useful functions, for
**[PocketMine-MP](https://github.com/pmmp/PocketMine-MP)**.

### Commands

| Command | Description | Permission |
| --- | --- | --- |
| `/spawner` | SakuraSpawners main command | **sakuraspawners.command.main** |
| `/hitkill` | Kill all entities together in one hit | **sakuraspawners.command.hitkill** |

### Permissions

| Permission | Description |
| --- | --- |
| **sakuraspawners.stack** | Be able to stack entities with their own egg |
| **sakuraspawners.change** | Be able to change the entity in a spawner with an egg |

## How can I get a Spawner?

At the moment there is only one way in which you can get a spawer, using the `/give` command.

### Example

| Command | Item ID:META | Result |
| --- | --- | --- |
| `/give` | 52:10 | **Chicken Spawner** |
| `/give` | 52:11 | **Cow Spawner** |
| `/give` | 52:12 | **Pig Spawner** |

Remembering that the `id of the spawner is 52` and the `id of the desired entity is the meta`.

- If you want to see all available `entity ids` you can see them **[HERE](https://github.com/DayKoala/SakuraSpawners/blob/main/resources/Names.yml)** !

## How can I get a Spawn Egg?

In the same way as the spawner, use:

### Example

| Command | Item ID:META | Result |
| --- | --- | --- |
| `/give` | 383:10 | **Chicken Egg** |
| `/give` | 383:11 | **Cow Egg** |
| `/give` | 383:12 | **Pig Egg** |

Remembering that the `id of the spawn egg is 383` and the `id of the desired entity is the meta`.

# For Developers

**1.** How can I add my own entity to a spawner?
 It is currently not possible to do this with minor changes. You would need to create a `Spawner or SpawnerTile` extension and register it using the `TileFactory`

 **2.** How can I get a Monster Spawner or Spawn Egg? Using SakuraSpawnersItems as in the example below:

 ```php

use DayKoala\item\SakuraSpawnersItems;

SakuraSpawnersItems::MONSTER_SPAWNER(); # Spawner
SakuraSpawnersItems::SPAWN_EGG(); # Spawn Egg

```

- Remembering that the Monster Spawner is returned as a Block if you want it as an item use `Block->asItem();`
