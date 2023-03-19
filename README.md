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

## Getting a Spawner

- You can get the desired Monster Spawner in the following ways:

> You can use the command `/give [player] 52` using the id of the entity as a meta, example:
> - `/give [player] 52:10` will result in the `chicken spawner`.
> - `/give [player] 52:11` will result in the `cow spawner`.
> - `/give [player] 52:12` will result in the `pig spawner`.
>
> You can use the command `/spawner give [entity id] (player)` to give the desired spawner to a player or mine the Monster Spawner with a `pickaxe with the SilkTouch enchantment`, to obtain the same.
>
> If you want to have access to all entities ids, use the `/spawner list` command or check the `Names.yml` file, in the plugin folder.

# Command

- `/spawner`: Main command, permission: `sakuraspawners.command.spawner`.

> # SubCommands
>
> - `/spawner name [format]` Change spawners name format, the name of the spawners will be modified on the next server startup (does not change the names of items already obtained), you can use the `{name}` key to replace it with the name of the spawner.
>
> - `/spawner ename [format]` Change spawner entity names format, entity names will only be updated on their next spawn, you can use the following keys: `{health} {max-health} {stack} {max-stack} {name}` they will be changed according to their value.
>
> - `/spawner adddrop [entity id]` Add the item in your hand to the droplist according to the entity id.
> 
> - `/spawner removedrop [entity id]` Remove the item in your hand of the droplist according to the entity id.
> 
> - `/spawner drops [entity id]` Remove the item in your hand to the drop list.
>
> - `/spawner xp [entity id] [amount]` Set the amount of experience the entity will give on its death.
> 
> - `/spawner size [entity id] [height] [width]` Set the hitbox size of the entity.
>
> - `/spawner give [entity id] (player)` Give a spawner to a player.
>
> - `/spawner list` See all entities ids.
>
> - `/spawner killall` Kill all spawner entities in your current world.
