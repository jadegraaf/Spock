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

namespace Spock;

/**
 * Keeps track of all ingame players
 *
 * @author Joeri de Graaf <joeri@oostcoast.nl>
 */
class PlayerTracker {
  private static $m_CurrentPlayerList = array();

  public static function addPlayerToList($playerName, $playerIp) {
    if (!in_array($playerName, self::$m_CurrentPlayerList))
      self::$m_CurrentPlayerList[] = array($playerName => $playerIp);
  }

  public static function removePlayerFromList($playerName) {
    if (($key = array_search($playerName, self::$m_CurrentPlayerList)) !== false) {
      unset(self::$m_CurrentPlayerList[$key]);
    }
  }

  public static function isPlayerConnected($playerName) {
    if (array_key_exists($playerName, self::$m_CurrentPlayerList))
      return true;
    else
      return false;
  }

  public static function getPlayerList() {
    return array_keys(self::$m_CurrentPlayerList);
  }

  public static function clearPlayerList() {
    self::$m_CurrentPlayerList = array();
  }
};
?>