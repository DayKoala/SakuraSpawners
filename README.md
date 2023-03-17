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

- You can get the ``Monster Spawner block`` using the **/give command** and using the ``entity id as a meta`` or by mining it with a **pickaxe with the SilkTouch enchantment**.

# Command

- `/spawner`: Main command, permission: `sakuraspawners.command.spawner`.

# Subcommand

> Change spawners name format, the name of the spawners will be modified on the next server startup (does not change the names of items already obtained).
> - `/spawner name [format]`

> Change spawner entity names format, entity names will only be updated on their next spawn.
> - `/spawner ename [format]`

> Add the item in your hand to the droplist according to the entity id.
> - `/spawner adddrop [entity id]`

> Remove the item in your hand of the droplist according to the entity id.
> - `/spawner removedrop [entity id]`

> Remove the item in your hand to the drop list.
> - `/spawner drops [entity id]`

> Set the amount of experience the entity will give on its death.
> - `/spawner xp [entity id] [amount]`

> Set the hitbox size of the entity.
> - `/spawner size [entity id] [height] [width]`

> Kill all spawner entities in your current world.
> - `/spawner killall`
