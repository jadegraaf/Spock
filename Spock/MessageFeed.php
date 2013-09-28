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

use \ Spock;

use Nuwani \ BotManager;
use Nuwani \ Timer;

/**
 * Handles the IRC echo
 *
 * @author Joeri de Graaf <joeri@oostcoast.nl>
 */
class MessageFeed {
  private static $m_LogFileSize = 0;
  private $m_MessageFeedTimer;
  
  public function __construct() {
    self::$m_LogFileSize = filesize(Configuration::StarmadeServerDirectory . '/logs/serverlog.txt.0');
    $this->m_MessageFeedTimer = new Timer;
    $this->m_MessageFeedTimer->create(array($this, 'ProccessLogMessages'), Configuration::UpdateInterval, 1);

  }

  /**
   * processes the freshly imported log lines
   */
  public static function ProccessLogMessages() {
    $messages = self::LoadNewLogLines();

    if(!$messages)
      return false;

    foreach ($messages as $message) {
      $message = str_replace("\r\n", '', $message);

      if ($message == "")
        continue;

      $prefix = '';

      if (Configuration::IsBeta)
        var_dump($message);

      //Lets see what type of message it is an do what needs to be done
      if (preg_match('/^.* \[CHAT\] (\w+)\: (.*)$/', $message)) {
        $message = preg_replace('/^.* \[CHAT\] (\w+)\: (.*)$/', '06\101: \2', $message);
      }
      elseif (preg_match('/^.* \[LOGIN\] logged in \w+: (\w+) \((\d+)\) .*$/', $message, $matches)) {
        $message = preg_replace('/^.* \[LOGIN\] logged in \w+: (\w+) \(\d+\) .*\/(\d+\.\d+\.\d+\.\d+):\d+\)$/', '03\1 has joined Las Venturas Starground', $message);
        PlayerTracker::addPlayerToList($matches[1], $matches[2]);
      }
      elseif (preg_match('/^.* \[LOGOUT\] logging out \w+: (\w+) \((\d+)\) .*$/', $message, $matches)) {
        $message = preg_replace('/^.* \[LOGOUT\] logging out \w+: (\w+) \(\d+\) .*$/', '03\1 has left Las Venturas Starground', $message);
        PlayerTracker::removePlayerFromList($matches[1]);
      }
      elseif (preg_match('/^.* FINE: \[SERVERSOCKET\] Incoming connection\: \d+, \/(\d+\.\d+\.\d+\.\d+) ->.*$/', $message, $matches)) {
        $prefix = '%';
        $message = '04Incomming Connection from ' . $matches[1];
      }
      else if (preg_match('/^.* FINE: \[ADMINCOMMAND\] (\w+) used: \'(\w+)\' with args \[(.*)\]$/', $message, $matches)) {
        $prefix = '%';

        switch ($matches[2]) {
          case 'GIVEID':
            preg_match('/(\w+), (\d+), (\d+)/', $matches[3], $parameters);

            $itemName = ItemUtilities::getItemName($parameters[2]);

            if (!$itemName)
              $itemName = 'ID:' . $parameters[2];

            if ($parameters)
            $message = $matches[1] . ' has given ' . $parameters[3] . ' of "' . $itemName . '" to ' . $parameters[1];
            break;

          case 'BAN_IP':
            PunishmentManager::addPunishmentNotice('ban', $matches[3], '[Ingame ban]', $matches[1]);
            $message = $matches[3] . ' has been banned by ' . $matches[1];
          break;

          case 'BAN_NAME':
            PunishmentManager::addPunishmentNotice('ban', $matches[3], '[Ingame ban]', $matches[1]);
            $message = $matches[3] . ' has been banned by ' . $matches[1];
          break;

          case 'KICK':
            if (PlayerTracker::isPlayerConnected($matches[3])) {
              PunishmentManager::addPunishmentNotice('kick', $matches[3], '[Ingame kick]', $matches[1]);
              $message = $matches[3] . ' has been kicked by ' . $matches[1];
            }
            else
              return false;
          break;

          default:
            $message = preg_replace('/^.* FINE: \[ADMINCOMMAND\] (\w+) used: \'(\w+)\' with args \[(.*)\].*$/', '\1 used \2 \3', $message);
            break;
        }

      }
      else if (preg_match('/^.* FINE: STARMADE SERVER STARTED:.*$/', $message)) {
        $message = '04The server has been started';
        PlayerTracker::clearPlayerList();
      }
      elseif (preg_match('/^.* FINE: \[SHUTDOWN\] Shutting down server$/', $message))
        $message = '04The server has been shut down';
      else if (preg_match('/^.*FINE: STARMADE SERVER VERSION: (.*); Build\((\w+)\)$/', $message, $matches))
        return ServerController::setStarmadeServerVersionNumber($matches[1], $matches[2]);
      else
        continue;

      $bot = BotManager::getInstance()->offsetGet('channel:' . Configuration::EchoChannel);
      if ($bot === false)
        continue;
       
      if ($bot instanceof \ Nuwani \ BotGroup)
          $bot = $bot->current();
      $bot->send('PRIVMSG ' . $prefix . Configuration::EchoChannel . ' :' . $message);
    }
  }



  /**
   * Imports the fresh log lines
   */
  private static function LoadNewLogLines() {
    $newLogLines = array();

    clearstatcache();
    
    $currentSize = filesize(Configuration::StarmadeServerDirectory . '/logs/serverlog.txt.0');
    if (self::$m_LogFileSize == $currentSize) {
      return false;
    }

    if (!file_exists(Configuration::StarmadeServerDirectory . '/logs/serverlog.txt.0'))
      return false;

    $logFileHandle = fopen(Configuration::StarmadeServerDirectory . '/logs/serverlog.txt.0', "r");
    fseek($logFileHandle, self::$m_LogFileSize);

    while ($logLine = fgets($logFileHandle)) {
      if ($logLine == "")
        continue;
      $newLogLines[] = $logLine;
    }

    fclose($logFileHandle);
    self::$m_LogFileSize = $currentSize;

    return $newLogLines;
  }
};
?>