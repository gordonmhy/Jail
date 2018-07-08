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
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\level\Position;

class BlockListener extends BaseListener
{

    public function onBlockBreak(BlockBreakEvent $event)
    {
        $t = $this->getPlugin()->data->getAll();
        $j = $this->getPlugin()->data1->getAll();
        $cfg = $this->getPlugin()->getConfig();
        $mines = new Mines($this->getPlugin());
        foreach ($j as $jail => $value) {
            if ($this->getPlugin()->insideJail($jail, new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel())) && $this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== true && $event->getPlayer()->hasPermission("jail.modify.bypass") !== true) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.block.is.restricted"));
            }
        }
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== false && $cfg->get("allow-block-break") !== true) {
            if ($mines->hasMineSet($t[strtolower($event->getPlayer()->getName())]["jail"]) !== true) {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                $event->setCancelled(true);
                if ($cfg->get("enable-penalty") !== false && isset($this->getPlugin()->prisoner_time[strtolower($event->getPlayer()->getName())])) {
                    $this->getPlugin()->applyPenalty(strtolower($event->getPlayer()->getName()), (int)$cfg->get("penalty-time"));
                    $event->getPlayer()->sendMessage(str_replace("%time%", $cfg->get("penalty-time"), $this->getPlugin()->getMessage("penalty.added.prisoner")));
                }
            } else if ($mines->hasMineSet($t[strtolower($event->getPlayer()->getName())]["jail"]) !== false && $mines->insideMine($t[strtolower($event->getPlayer()->getName())]["jail"], new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel())) !== true) {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                $event->setCancelled(true);
                if ($cfg->get("enable-penalty") !== false && isset($this->getPlugin()->prisoner_time[strtolower($event->getPlayer()->getName())])) {
                    $this->getPlugin()->applyPenalty(strtolower($event->getPlayer()->getName()), (int)$cfg->get("penalty-time"));
                    $event->getPlayer()->sendMessage(str_replace("%time%", $cfg->get("penalty-time"), $this->getPlugin()->getMessage("penalty.added.prisoner")));
                }
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event)
    {
        $t = $this->getPlugin()->data->getAll();
        $j = $this->getPlugin()->data1->getAll();
        $cfg = $this->getPlugin()->getConfig();
        foreach ($j as $jail => $value) {
            if ($this->getPlugin()->insideJail($jail, new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel())) && $this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== true && $event->getPlayer()->hasPermission("jail.modify.bypass") !== true) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.block.is.restricted"));

            }
        }
        if ($this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== false && $cfg->get("allow-block-place") !== true) {
            $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
            $event->setCancelled(true);
            if ($cfg->get("enable-penalty") !== false && isset($this->getPlugin()->prisoner_time[strtolower($event->getPlayer()->getName())])) {
                $this->getPlugin()->applyPenalty(strtolower($event->getPlayer()->getName()), (int)$cfg->get("penalty-time"));
                $event->getPlayer()->sendMessage(str_replace("%time%", $cfg->get("penalty-time"), $this->getPlugin()->getMessage("penalty.added.prisoner")));
            }
        }
    }

}

