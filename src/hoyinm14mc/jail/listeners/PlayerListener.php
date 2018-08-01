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

use hoyinm14mc\jail\base\BaseListener;
use hoyinm14mc\jail\Mines;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;

class PlayerListener extends BaseListener
{

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $t = $this->getPlugin()->data->getAll();
        if ($this->getPlugin()->playerProfileExists(strtolower($event->getPlayer()->getName())) !== true) {
            $t[strtolower($event->getPlayer()->getName())]["jailed"] = false;
            $t[strtolower($event->getPlayer()->getName())]["gamemode"] = $this->getPlugin()->getServer()->getGamemode();
            $t[strtolower($event->getPlayer()->getName())]["VoteForJail"]["votes"] = 0;
            $t[strtolower($event->getPlayer()->getName())]["VoteForJail"]["votedBy"] = []; //Players who voted for him
            $t[strtolower($event->getPlayer()->getName())]["uuid"] = $event->getPlayer()->getUniqueId();
            $this->getPlugin()->data->setAll($t);
            $this->getPlugin()->data->save();
        }
        if ($this->getPlugin()->getConfig()->get("check-UUID") !== false && $event->getPlayer()->hasPermission("jail.uuidcheck.bypass") !== true) {
            foreach ($t as $name => $value) {
                if ($t[$name]["jailed"] !== false
                    && $name !== strtolower($event->getPlayer()->getName())
                    && (string)$event->getPlayer()->getUniqueId() === $t[$name]["uuid"]
                ) {
                    $event->getPlayer()->kick($this->getPlugin()->getMessage("join.uuid.rejected.kickmsg"));
                }
            }
        }
        $t[strtolower($event->getPlayer()->getName())]["uuid"] = (string)$event->getPlayer()->getUniqueId();
        $this->getPlugin()->data->setAll($t);
        $this->getPlugin()->data->save();
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) && isset($this->getPlugin()->prisoner_time[strtolower($event->getPlayer()->getName())]) !== false && $this->getPlugin()->prisoner_time[strtolower($event->getPlayer()->getName())] < 0) {
            $this->getPlugin()->unjail(strtolower($event->getPlayer()->getName()));
        }
        if (isset($t[strtolower($event->getPlayer()->getName())]["unjailedSettings"]) !== false
        ) {
            $event->getPlayer()->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSpawnLocation());
            $event->getPlayer()->getInventory()->clearAll();
            $event->getPlayer()->getInventory()->setContents($t[strtolower($event->getPlayer()->getName())]["unjailedSettings"]["inv"]);
            $event->getPlayer()->setGamemode($t[strtolower($event->getPlayer()->getName())]["unjailedSettings"]["gm"]);
            unset($t[strtolower($event->getPlayer()->getName())]["unjailedSettings"]);
            $this->getPlugin()->data->setAll($t);
            $this->getPlugin()->data->save();
        }
    }

    public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
    {
        $cfg = $this->getPlugin()->getConfig();
        $msg = $event->getMessage();
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== false) {
            if ($cfg->get("allow-chat") !== true && $cfg->get("allow-command") !== false) {
                if ($msg{0} !== "/") {
                    $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                    $event->setCancelled(true);
                }
            } elseif ($cfg->get("allow-chat") !== true && $cfg->get("allow-command") !== true) {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                $event->setCancelled(true);
            } elseif ($cfg->get("allow-chat") !== false && $cfg->get("allow-command") !== true) {
                if ($msg{0} == "/") {
                    $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.player.command.cancelled"));
                    $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                    $event->setCancelled(true);
                }
            }
        }
    }

    /**
     * @priority HIGH
     * @param PlayerInteractEvent $event
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
                    isset($this->getPlugin()->allowBail_tmp[strtolower($event->getPlayer()->getName())]) !== false ? $this->getPlugin()->allowBail_tmp[strtolower($event->getPlayer()->getName())] : $this->getPlugin()->getConfig()->get("allow-bail"),
                    isset($this->getPlugin()->allowVisit_tmp[strtolower($event->getPlayer()->getName())]) !== false ? $this->getPlugin()->allowVisit_tmp[strtolower($event->getPlayer()->getName())] : $this->getPlugin()->getConfig()->get("allow-visit")
                );
                unset($this->getPlugin()->jailName_tmp[strtolower($event->getPlayer()->getName())]);
                unset($this->getPlugin()->pos_tmp[strtolower($event->getPlayer()->getName())]);
                unset($this->getPlugin()->c1_tmp[strtolower($event->getPlayer()->getName())]);
                unset($this->getPlugin()->c2_tmp[strtolower($event->getPlayer()->getName())]);
                unset($this->getPlugin()->selection_mode[array_search(strtolower($event->getPlayer()->getName()), $this->getPlugin()->selection_mode)]);
                if (isset($this->getPlugin()->allowBail_tmp[strtolower($event->getPlayer()->getName())]) !== false) {
                    unset($this->getPlugin()->allowBail_tmp[strtolower($event->getPlayer()->getName())]);
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
            unset($mines->mineName_tmp[strtolower($event->getPlayer()->getName())]);
            unset($mines->mine_c1[strtolower($event->getPlayer()->getName())]);
            unset($mines->mine_c2[strtolower($event->getPlayer()->getName())]);
            $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("mine.set.success"));
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event)
    {
        $t = $this->getPlugin()->data->getAll();
        $j = $this->getPlugin()->data1->getAll();
        if ($event->getPlayer()->hasPermission("jail.showInOutMessage") !== false && $this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== true) {
            foreach (array_keys($j) as $jail) {
                if ($this->getPlugin()->insideJail($jail, $event->getPlayer()->getPosition()) !== false
                    && $this->getPlugin()->insideJail($jail, new Position($event->getFrom()->x, $event->getFrom()->y, $event->getFrom()->z, $event->getFrom()->getLevel())) !== true
                ) {
                    $event->getPlayer()->sendMessage(str_replace("%jail%", $jail, $this->getPlugin()->getMessage("enterJail.msg")));
                } else if ($this->getPlugin()->insideJail($jail, $event->getPlayer()->getPosition()) !== true
                    && $this->getPlugin()->insideJail($jail, new Position($event->getFrom()->x, $event->getFrom()->y, $event->getFrom()->z, $event->getFrom()->getLevel())) !== false
                ) {
                    $event->getPlayer()->sendMessage(str_replace("%jail%", $jail, $this->getPlugin()->getMessage("leaveJail.msg")));
                }
            }
        }
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName()))
            && $this->getPlugin()->jailExists($t[strtolower($event->getPlayer()->getName())]["jail"])
            && $this->getPlugin()->insideJail($t[strtolower($event->getPlayer()->getName())]["jail"], $event->getPlayer()->getPosition()) !== true
            && ($event->getFrom()->x != $event->getPlayer()->x || $event->getFrom()->y != $event->getPlayer()->y || $event->getFrom()->z != $event->getPlayer()->z)
        ) {
            $event->getPlayer()->teleport(
                new Position(
                    $j[$t[strtolower($event->getPlayer()->getName())]["jail"]]["pos"]["x"],
                    $j[$t[strtolower($event->getPlayer()->getName())]["jail"]]["pos"]["y"],
                    $j[$t[strtolower($event->getPlayer()->getName())]["jail"]]["pos"]["z"],
                    $this->getPlugin()->getServer()->getLevelByName($j[$t[strtolower($event->getPlayer()->getName())]["jail"]]["pos"]["level"])));
            $event->getPlayer()->sendPopup($this->getPlugin()->getMessage("listener.player.not.allowed.leave") . "\n\n\n\n");
        }
        if ($event->getPlayer()->hasPermission("jail.visit.bypass") !== true
            && $this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== true) {
            foreach (array_keys($j) as $jail) {
                if ($this->getPlugin()->jailExists($jail)
                    && ($this->getPlugin()->getConfig()->get("allow-visit") !== true && filter_var($j[$jail]["allow-visit"], FILTER_VALIDATE_BOOLEAN) !== true)
                    && $this->getPlugin()->insideJail($jail, $event->getPlayer()->getPosition()) !== false
                    && ($event->getFrom()->x != $event->getPlayer()->x || $event->getFrom()->y != $event->getPlayer()->y || $event->getFrom()->z != $event->getPlayer()->z)
                ) {
                    $event->getPlayer()->teleport(
                        new Position(
                            $event->getFrom()->x,
                            $event->getFrom()->y,
                            $event->getFrom()->z,
                            $this->getPlugin()->getServer()->getLevelByName($j[$jail]["pos"]["level"])
                        )
                    );
                    $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.player.not.allowed.enter"));
                }
            }
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
        if (isset($mines->mineName_tmp[strtolower($event->getPlayer()->getName())])) {
            unset($mines->mineName_tmp[strtolower($event->getPlayer()->getName())]);
            unset($mines->mine_c1[strtolower($event->getPlayer()->getName())]);
            unset($mines->mine_c2[strtolower($event->getPlayer()->getName())]);
        }
    }

    public function onItemConsume(PlayerItemConsumeEvent $event)
    {
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== false && $event->getItem()->getId() == 274) {
            $event->setCancelled(true);
        }
    }

    public function onItemDrop(PlayerDropItemEvent $event)
    {
        if ($this->getPlugin()->getConfig()->get("allow-item-drop") !== true
            && $this->getPlugin()->isJailed($event->getPlayer()->getName()) !== false
        ) {
            $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
            $event->setCancelled(true);
        }
    }

}


