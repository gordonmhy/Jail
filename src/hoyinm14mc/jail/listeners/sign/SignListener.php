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

namespace hoyinm14mc\jail\listeners\sign;

use hoyinm14mc\jail\base\BaseListener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;

class SignListener extends BaseListener
{

    public function onBlockBreak(BlockBreakEvent $event)
    {
        if ($event->getBlock()->getID() != 323 && $event->getBlock()->getID() != 63 && $event->getBlock()->getID() != 68) {
            return false;
        }
        $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
        if ($sign instanceof Sign !== true) {
            return false;
        }
        $sign = $sign->getText();
        if ($sign[0] == $this->getPlugin()->colorMessage("&7[" . $this->getPlugin()->getMessage("timer.broadcast.bail") . "&7]")) {
            if ($event->getPlayer()->hasPermission("jail.sign.destroy") !== false && $this->getPlugin()->isJailed(strtolower($event->getPlayer()->getName())) !== true) {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("sign.destroy.success"));
                return true;
            } else {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("no.permission"));
                $event->setCancelled(true);
            }
        }
    }

    public function onSignChange(SignChangeEvent $event)
    {
        if ($event->getBlock()->getID() != 323 && $event->getBlock()->getID() != 63 && $event->getBlock()->getID() != 68) {
            return false;
        }
        $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
        if ($sign instanceof Sign !== true) {
            return false;
        }
        $sign = $event->getLines();
        if ($sign[0] != "[Bail]") {
            return false;
        }
        if ($event->getPlayer()->hasPermission("jail.sign.create") !== true) {
            $event->setLine(0, null);
            $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("no.permission"));
            return false;
        }
        $event->setLine(0, $this->getPlugin()->colorMessage("&7[" . $this->getPlugin()->getMessage("timer.broadcast.bail") . "&7]"));
        $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("sign.create.success"));
        return true;
    }

    public function onPlayerInteract(PlayerInteractEvent $event)
    {
        if ($event->getBlock()->getID() != 323 && $event->getBlock()->getID() != 63 && $event->getBlock()->getID() != 68) {
            return false;
        }
        $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
        if ($sign instanceof Sign !== true) {
            return false;
        }
        $sign = $sign->getText();
        if ($sign[0] == $this->getPlugin()->colorMessage("&7[" . $this->getPlugin()->getMessage("timer.broadcast.bail") . "&7]")) {
            if ($this->getPlugin()->isJailed($event->getPlayer()->getName()) !== true) {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("sign.not.jailed"));
                return false;
            }
            if ($event->getPlayer()->hasPermission("jail.sign.use") !== true) {
                $event->getPlayer()->sendMessage($this->getPlugin()->getMessage("no.permission"));
                return false;
            }
            $this->getPlugin()->getServer()->dispatchCommand($event->getPlayer(), "bail");
            return true;
        }
    }

}

?>