<?php
/**
 * Copyright (c) 2006-2013 Las Venturas Mineground
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

use Spock \ CommandHelper;

/**
 * Handles starting and stopping the Minecraft server and can send commands to it
 *
 * @author Joeri de Graaf <joeri@oostcoast.nl>
 */
class ServerController {
  private static $m_versionNumber = '';
  private static $m_buildNumber = '';

  /**
   * Starts the starmadeserver through the init script if it isn't running yet
   *
   * @return boolean          If the start was successfull
   */
  public static function startStarmadeServer() {
    shell_exec('screen -S starmade -p 0 -X stuff "`printf "java -Xms128m -Xmx1024m -jar StarMade.jar -server\r"`";');
  }

  /**
   * Stops or terminates the starmade server
   *
   * @return boolean            If the action was successfull
   */
  public static function stopStarmadeServer() {
    shell_exec('screen -S starmade -p 0 -X stuff "`printf "/shutdown 10\r"`";');
  }

  /**
   * Sends a command to the server
   * 
   * @param  string $command The command which should be send
   */
  public static function sendCommandToServer($command) {
    shell_exec('screen -S starmade -p 0 -X stuff "`printf "' . $command . '\r"`";');
    return true;
  }

  /**
   * Broadcasts an message to the server
   * 
   * @param  string $command The command which should be send
   */
  public static function broadcastToServer($command) {
   shell_exec('screen -S starmade -p 0 -X stuff "`printf "/chat ' . $command . '\r"`";');
  }

  // Sets the server verion number
  public static function setStarmadeServerVersionNumber($versionNumber, $buildNumber) {
    self::$m_versionNumber = $versionNumber;
    self::$m_buildNumber = $buildNumber;

    return true;
  } 

  // Gets the server verion number
  public static function getStarmadeServerVersionNumber($versionNumber) {
    return array(self::$m_versionNumber, self::$m_buildNumber);
  }

  // Gets the server status. true if it is running, false otherwise
  public static function getStarmadeServerState() {
    if (shell_exec('ps -fu starmade |grep -v grep | grep java | awk \'{print $2}\''))
      return true;
    else
      false;

  }
};
?>