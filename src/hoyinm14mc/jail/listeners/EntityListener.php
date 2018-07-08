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
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

class EntityListener extends BaseListener
{

    public function onEntityDamage(EntityDamageEvent $event)
    {
        if ($event->getCause() instanceof EntityDamageByEntityEvent !== true) {
            if ($event->getEntity() instanceof Player && $this->getPlugin()->isJailed(strtolower($event->getEntity()->getName())) !== false) {
                if ($this->getPlugin()->getConfig()->get("disable-damage") !== false) {
                    $event->setCancelled(true);
                }
            }
        } else {
            if ($event->getDamager() instanceof Player && $this->getPlugin()->isJailed(strtolower($event->getDamager()->getName())) !== false) {
                if ($this->getPlugin()->getConfig()->get("allow-attack") !== false) {
                    $event->getDamager()->sendMessage($this->getPlugin()->getMessage("listener.not.allowed.do.this"));
                    $event->setCancelled(true);
                }
            }
        }
    }

}

