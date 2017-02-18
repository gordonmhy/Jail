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

namespace hoyinm14mc\jail\listeners;

use hoyinm14mc\jail\Jail;
use hoyinm14mc\jail\base\BaseListener;
use hoyinm14mc\jail\Mines;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;

class PlayerListener extends BaseListener
{

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $t = $this->getPlugin()->data->getAll();
        if ($this->getPlugin()->playerProfileExists(strtolower($event->getPlayer()->getName())) !== true) {
            $t[$event->getPlayer()->getName()]["jailed"] = false;
            $t[$event->getPlayer()->getName()]["gamemode"] = $this->getPlugin()->getServer()->getGamemode();
            $t[$event->getPlayer()->getName()]["voteForJail"]["votes"] = 0;
            $t[$event->getPlayer()->getName()]["voteForJail"]["votedBy"] = []; //Players who voted for him
            $t[$event->getPlayer()->getName()]["uuid"] = $event->getPlayer()->getUniqueId();
            $this->getPlugin()->data->setAll($t);
            $this->getPlugin()->data->save();
        }
        $t[strtolower($event->getPlayer()->getName())]["uuid"] = (string)$event->getPlayer()->getUniqueId();
        $this->getPlugin()->data->setAll($t);
        $this->getPlugin()->data->save();
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) && isset($t[strtolower($event->getPlayer()->getName())]["seconds"]) !== false && $t[strtolower($event->getPlayer()->getName())]["seconds"] < 0) {
            $this->getPlugin()->unjail(strtolower($event->getPlayer()->getName()));
        }
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) && $this->getPlugin()->jailExists($t[strtolower($event->getPlayer()->getName())]["jail"]) !== true) {
            $this->getPlugin()->unjail(strtolower($event->getPlayer()->getName()));
        }
    }

    public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
    {
        $cfg = $this->getPlugin()->getConfig();
        $msg = $event->getMessage();
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== false) {
            if ($cfg->get("allow-chat") == false && $cfg->get("allow-command") == true) {
                if ($msg{0} !== "/") {
                    $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                    $event->setCancelled(true);
                }
            } else if ($cfg->get("allow-chat") == false && $cfg->get("allow-command") == false) {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                $event->setCancelled(true);
            } else if ($cfg->get("allow-chat") == true && $cfg->get("allow-command") == false) {
                if ($msg{0} = "/") {
                    $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.player.command.cancelled"));
                    $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                    $event->setCancelled(true);
                }
            }
        }
    }

    /**
     * @priority HIGH
     */
    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        if ($this->getPlugin()->isSelectionMode($event->getPlayer())) {
            $event->setCancelled(true);
            if (array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->c1_tmp) !== true && array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->c2_tmp) !== true && array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->pos_tmp) !== true) {
                $this->getPlugin()->pos_tmp[strtolower($event->getPlayer()->getName())] = new Position($event->getBlock()->x, $event->getBlock()->y + 1, $event->getBlock()->z, $event->getBlock()->getLevel());
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("setjail.tap.corner.1"));
            } else if (array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->c1_tmp) !== true && array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->c2_tmp) !== true && array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->pos_tmp) !== false) {
                $this->getPlugin()->c1_tmp[strtolower($event->getPlayer()->getName())] = new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel());
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("setjail.tap.corner.2"));
            } else if (array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->c1_tmp) !== false && array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->c2_tmp) !== true && array_key_exists(strtolower($event->getPlayer()->getName()), $this->getPlugin()->pos_tmp) !== false) {
                $this->getPlugin()->c2_tmp[strtolower($event->getPlayer()->getName())] = new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel());
                //Selection end
                $this->getPlugin()->setJail(
                    $this->getPlugin()->jailName_tmp[strtolower($event->getPlayer()->getName())],
                    $this->getPlugin()->pos_tmp[strtolower($event->getPlayer()->getName())],
                    $this->getPlugin()->c1_tmp[strtolower($event->getPlayer()->getName())],
                    $this->getPlugin()->c2_tmp[strtolower($event->getPlayer()->getName())],
                    isset($this->getPlugin()->allowBail_tmp[$event->getPlayer()->getName()]) !== false ? $this->getPlugin()->allowBail_tmp[$event->getPlayer()->getName()] : $this->getPlugin()->getConfig()->get("allow-bail")
                );
                unset($this->getPlugin()->jailName_tmp[strtolower($event->getPlayer()->getName())]);
                unset($this->getPlugin()->pos_tmp[strtolower($event->getPlayer()->getName())]);
                unset($this->getPlugin()->c1_tmp[strtolower($event->getPlayer()->getName())]);
                unset($this->getPlugin()->c2_tmp[strtolower($event->getPlayer()->getName())]);
                unset($this->getPlugin()->selection_mode[array_search(strtolower($event->getPlayer()->getName()), $this->getPlugin()->selection_mode)]);
                if (isset($this->getPlugin()->allowBail_tmp[$event->getPlayer()->getName()]) !== false) {
                    unset($this->getPlugin()->allowBail_tmp[$event->getPlayer()->getName()]);
                }
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("setjail.success"));
            }
        }
        // Mine of jail
        $mines = new Mines($this->getPlugin());
        if (array_key_exists(strtolower($event->getPlayer()->getName()), $mines->mineName_tmp) !== false && array_key_exists(strtolower($event->getPlayer()->getName()), $mines->mine_c1) !== true && array_key_exists(strtolower($event->getPlayer()->getName()), $mines->mine_c2) !== true) {
            $mines->mine_c1[strtolower($event->getPlayer()->getName())] = new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel());
            $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("mine.set.tap.corner.2"));
        } else if (array_key_exists(strtolower($event->getPlayer()->getName()), $mines->mineName_tmp) !== false && array_key_exists(strtolower($event->getPlayer()->getName()), $mines->mine_c1) !== false && array_key_exists(strtolower($event->getPlayer()->getName()), $mines->mine_c2) !== true) {
            $mines->mine_c2[strtolower($event->getPlayer()->getName())] = new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel());
            //Selection end
            $mines->setMine($mines->mineName_tmp[strtolower($event->getPlayer()->getName())], $mines->mine_c1[strtolower($event->getPlayer()->getName())], $mines->mine_c2[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->mineName_tmp[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->mine_c1[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->mine_c2[strtolower($event->getPlayer()->getName())]);
            $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("mine.set.success"));
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event)
    {
        $t = $this->getPlugin()->data->getAll();
        $j = $this->getPlugin()->data1->getAll();
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) && $this->getPlugin()->jailExists($t[strtolower($event->getPlayer()->getName())]["jail"]) && $this->getPlugin()->insideJail($t[strtolower($event->getPlayer()->getName())]["jail"], $event->getPlayer()->getPosition()) !== true) {
            $event->getPlayer()->teleport(new Position($j[$t[strtolower($event->getPlayer()->getName())]["jail"]]["pos"]["x"], $j[$t[strtolower($event->getPlayer()->getName())]["jail"]]["pos"]["y"], $j[$t[strtolower($event->getPlayer()->getName())]["jail"]]["pos"]["z"], $this->getPlugin()->getServer()->getLevelByName($j[$t[strtolower($event->getPlayer()->getName())]["jail"]]["pos"]["level"])));
            $event->getPlayer()->sendPopup("\n" . $this->getPlugin()->getMessage("listener.player.not.allowed.leave"));
        }
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== false && $this->getPlugin()->getConfig()->get("allow-movement") !== true) {
            if ($event->getFrom()->x != $event->getPlayer()->x || $event->getFrom()->y != $event->getPlayer()->y || $event->getFrom()->z != $event->getPlayer()->z) {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                $event->setCancelled(true);
            }
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event)
    {
        if ($this->getPlugin()->isSelectionMode($event->getPlayer())) {
            unset($this->getPlugin()->jailName_tmp[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->pos_tmp[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->c1_tmp[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->c2_tmp[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->selection_mode[array_search(strtolower($event->getPlayer()->getName()), $this->getPlugin()->selection_mode)]);
        }
        $mines = new Mines($this->getPlugin());
        if (isset($mines->mineName_tmp[$event->getPlayer()->getName()])) {
            unset($this->getPlugin()->mineName_tmp[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->mine_c1[strtolower($event->getPlayer()->getName())]);
            unset($this->getPlugin()->mine_c2[strtolower($event->getPlayer()->getName())]);
        }
    }

    public function onItemConsume(PlayerItemConsumeEvent $event)
    {
        if ($this->getPlugin()->isJailed($event->getPlayer()->getName()) !== false && $event->getItem()->getId() == 274) {
            $event->setCancelled(true);
        }
    }

}
?>
