<?php
/*
 * This file is a part of Jail.
 * Copyright (C) 2017 hoyinm14mc
 *
 * Jail is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jail is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jail. If not, see <http://www.gnu.org/licenses/>.
 */

namespace hoyinm14mc\jail;

use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Mines
{

    private $plugin;

    /**
     * @var array:string
     */
    public $mineName_tmp = [];

    /**
     * @var array
     */
    public $mine_c1 = [];

    /**
     * @var array
     */
    public $mine_c2 = [];

    public function __construct(Jail $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param string $jail_name
     * @param Position $c1
     * @param Position $c2
     * @return bool
     */
    public function setMine(string $jail_name, Position $c1, Position $c2): bool
    {
        $j = $this->plugin->data1->getAll();
        if ($this->plugin->jailExists($jail_name) !== true) {
            return false;
        }
        if ($this->hasMineSet($jail_name) !== false) {
            $this->rmMine($jail_name);
        }
        $j[$jail_name]["mine"]["isSet"] = true;
        $j[$jail_name]["mine"]["c1"]["x"] = $c1->x;
        $j[$jail_name]["mine"]["c1"]["y"] = $c1->y;
        $j[$jail_name]["mine"]["c1"]["z"] = $c1->z;
        $j[$jail_name]["mine"]["c2"]["x"] = $c2->x;
        $j[$jail_name]["mine"]["c2"]["y"] = $c2->y;
        $j[$jail_name]["mine"]["c2"]["z"] = $c2->z;
        $this->plugin->data1->setAll($j);
        $this->plugin->data1->save();
        return true;
    }

    /**
     * @param string $jail_name
     * @return bool
     */
    public function rmMine(string $jail_name): bool
    {
        $j = $this->plugin->data1->getAll();
        if ($this->plugin->jailExists($jail_name) !== true || $this->hasMineSet($jail_name) !== true) {
            return false;
        }
        $j[$jail_name]["mine"]["isSet"] = false;
        unset($j[$jail_name]["c1"]);
        unset($j[$jail_name]["c2"]);
        $this->plugin->data1->setAll($j);
        $this->plugin->data1->save();
        return true;
    }

    /**
     * @param string $jail_name
     * @return bool
     */
    public function hasMineSet(string $jail_name): bool
    {
        $j = $this->plugin->data1->getAll();
        if ($this->plugin->jailExists($jail_name) !== true) {
            return false;
        }
        return $j[$jail_name]["mine"]["isSet"];
    }

    /**
     * @param string $jail_name
     * @param Position $pos
     * @return bool
     */
    public function insideMine(string $jail_name, Position $pos): bool
    {
        $j = $this->plugin->data1->getAll();
        if ($this->hasMineSet($jail_name) !== true) {
            return false;
        }
        if ($this->plugin->insideJail($jail_name, $pos) !== true) {
            return false;
        }
        return (min($j[$jail_name]["mine"]["c1"]["x"],
                    $j[$jail_name]["mine"]["c2"]["x"]) <= $pos->x) && (max($j[$jail_name]["c1"]["x"],
                    $j[$jail_name]["mine"]["c2"]["x"]) >= $pos->x) && (min($j[$jail_name]["c1"]["y"],
                    $j[$jail_name]["mine"]["c2"]["y"]) <= $pos->y) && (max($j[$jail_name]["c1"]["y"],
                    $j[$jail_name]["mine"]["c2"]["y"]) >= $pos->y) && (min($j[$jail_name]["c1"]["z"],
                    $j[$jail_name]["mine"]["c2"]["z"]) <= $pos->z) && (max($j[$jail_name]["c1"]["z"],
                    $j[$jail_name]["mine"]["c2"]["z"]) >= $pos->z) && ($j[$jail_name]["pos"]["level"] == $pos->getLevel()->getName());
    }

    /**
     * @param string $jail_name
     * @return bool
     */
    public function resetMine(string $jail_name): bool
    {
        $j = $this->plugin->data1->getAll();
        if ($this->hasMineSet($jail_name) !== true) {
            return false;
        }
        //Block Placer
        for ($x = min($j[$jail_name]["mine"]["c1"]["x"], $j[$jail_name]["mine"]["c2"]["x"]);
             $x <= max($j[$jail_name]["c1"]["x"], $j[$jail_name]["mine"]["c2"]["x"]);
             $x++) {
            for ($y = min($j[$jail_name]["mine"]["c1"]["y"], $j[$jail_name]["mine"]["c2"]["y"]);
                 $y <= max($j[$jail_name]["c1"]["y"], $j[$jail_name]["mine"]["c2"]["y"]);
                 $y++) {
                for ($z = min($j[$jail_name]["mine"]["c1"]["z"], $j[$jail_name]["mine"]["c2"]["z"]);
                     $z <= max($j[$jail_name]["c1"]["z"], $j[$jail_name]["mine"]["c2"]["z"]);
                     $z++) {
                    $this->plugin->getServer()->getLevelByName($j[$jail_name]["pos"]["level"])->setBlock(new Vector3($x, $y, $z), Block::get($this->plugin->getConfig()->get("block")));
                }
            }
        }
        return true;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function hasMineAreaSelected(Player $player): bool
    {
        return array_key_exists(strtolower($player->getName()), $this->mine_c1) &&
            array_key_exists(strtolower($player->getName()), $this->mine_c2);
    }

}


