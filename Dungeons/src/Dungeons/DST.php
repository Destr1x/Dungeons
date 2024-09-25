<?php

namespace Dungeons;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\nbt\tag\{CompoundTag, IntTag, StringTag};
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\MobSpawner;

class DST extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onChunk(ChunkPopulateEvent $event): void {
        $world = $event->getWorld();
        $chunk = $event->getChunk();
        $chance = mt_rand(0, 30);

        if($chance == 1) {
            $y = $world->getHighestBlockAt($chunk->getX() << 4, $chunk->getZ() << 4) - mt_rand(20, 70);
            if($y < 1) {
                $y = 10;
            }

            $xmin = $chunk->getX() << 4;
            $xmax = ($chunk->getX() << 4) + mt_rand(7, 9);
            $ymin = $y;
            $ymax = $y + mt_rand(4, 5);
            $zmin = $chunk->getZ() << 4;
            $zmax = ($chunk->getZ() << 4) + mt_rand(7, 9);

            for($x = $xmin; $x <= $xmax; $x++) {
                for($y = $ymin; $y <= $ymax; $y++) {
                    for($z = $zmin; $z <= $zmax; $z++) {
                        $block = mt_rand(0, 100) > 25 ? VanillaBlocks::STONE() : VanillaBlocks::MOSSY_COBBLESTONE();
                        $world->setBlockAt($x, $y, $z, $block);
                    }
                }
            }

            for($x = $xmin + 1; $x <= $xmax - 1; $x++) {
                for($y = $ymin + 1; $y <= $ymax - 1; $y++) {
                    for($z = $zmin + 1; $z <= $zmax - 1; $z++) {
                        $world->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
                    }
                }
            }

            // Спавнер мобов
            $x = $xmin + intdiv(($xmax - $xmin), 2);
            $y = $ymin + 1;
            $z = $zmin + intdiv(($zmax - $zmin), 2);
            $pos = new Vector3($x, $y, $z);

            $mobs = [34, 35, 32];
            $mob = array_rand($mobs);

            $world->setBlockAt($x, $y, $z, VanillaBlocks::MONSTER_SPAWNER());

            $spawnerTag = new CompoundTag();
            $spawnerTag->setString("id", Tile::MOB_SPAWNER);
            $spawnerTag->setInt("x", $x);
            $spawnerTag->setInt("y", $y);
            $spawnerTag->setInt("z", $z);
            $spawnerTag->setInt("EntityId", $mobs[$mob]);

            $spawnerTile = new MobSpawner($world, $spawnerTag);
            $world->addTile($spawnerTile);

            $this->getLogger()->notice("New dungeon generated at $x, $y, $z");

            // Генерация сундука
            $x = $xmin + intdiv(($xmax - $xmin), 2);
            $z = $zmin + intdiv(($zmax - $zmin), 2);
            $pos = new Vector3($x, $y, $z);
            $world->setBlockAt($x, $y, $z, VanillaBlocks::CHEST());

            $chestTag = new CompoundTag();
            $chestTag->setString("id", Tile::CHEST);
            $chestTag->setInt("x", $x);
            $chestTag->setInt("y", $y);
            $chestTag->setInt("z", $z);

            $chestTile = Tile::createTile(Tile::CHEST, $world, $chestTag);
            $world->addTile($chestTile);

            // Добавляем предметы в сундук
            $items = [
                VanillaItems::DIAMOND(), VanillaItems::IRON_INGOT(),
                VanillaItems::GOLD_INGOT(), VanillaItems::BREAD(),
                VanillaItems::ENCHANTED_GOLDEN_APPLE(), VanillaItems::NETHER_STAR(),
                VanillaItems::LEATHER(), VanillaItems::NAME_TAG(),
                VanillaItems::SULPHUR(), VanillaItems::BUCKET(), VanillaItems::WHEAT(),
                VanillaItems::BONE(), VanillaItems::ROTTEN_FLESH(), VanillaItems::COAL()
            ];

            for($i = 0; $i < 10; $i++) {
                $item = $items[array_rand($items)];
                $chestTile->getInventory()->setItem(mt_rand(0, 14), $item);
            }
        }
    }
}
