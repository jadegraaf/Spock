<?php
/**
 * Copyright (c) 2006-2013 Las Venturas Starground
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 */

use Nuwani \ BotManager;
use Nuwani \ ModuleManager;

require_once __DIR__ . '/CommandHelper.php';
require_once __DIR__ . '/Commands.php';
require_once __DIR__ . '/Configuration.php';
require_once __DIR__ . '/ItemUtilities.php';
require_once __DIR__ . '/MessageFeed.php';
require_once __DIR__ . '/PlayerTracker.php';
require_once __DIR__ . '/PunishmentManager.php';
require_once __DIR__ . '/ServerController.php';

use Spock \ Commands;
use Spock \ CommandHelper;
use Spock \ Configuration;
use Spock \ ItemUtilities;
use Spock \ MessageFeed;
use Spock \ PlayerTracker;
use Spock \ Punish;
use Spock \ PunishmentManager;
use Spock \ ServerController;

class Spock extends ModuleBase {

  private $m_MessageFeed;

  public function __construct() {
    if(!file_exists(Configuration::StarmadeServerDirectory . '/logs/serverlog.txt.0'))
      exit("\r\n!!!ERROR: Starmade server directory not found.\r\n!!!Verify the configuration in " . getcwd() . '/Modules/Spock/Configuration.php');

    $this->m_MessageFeed = new MessageFeed();
    PunishmentManager::initialize();
  }

  // Invoked when someone types something in a public channel.
  public function onChannelPrivmsg(Bot $bot, $channel, $nickname, $message) {
    if (substr($message, 0, 1) != Configuration::CommandPrefix)
      return;

    $channelTracker = ModuleManager::getInstance()->offsetGet('ChannelTracker');
    if ($channelTracker === false) {
      echo '[Mineground] Disregarding command as the Channel Tracker is not available.' . PHP_EOL;
      return;
    }

    $userLevel = $channelTracker->highestUserLevelForChannel($nickname, "#LVP.Starmade");
    $parameters = preg_split('/\s+/', $message);
    $command = substr(array_shift($parameters), 1);

    return Commands::processCommand($bot, $command, $parameters, $channel, $nickname, $userLevel);
  }
};