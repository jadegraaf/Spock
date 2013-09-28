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
 * Description
 *
 * @author Joeri de Graaf <joeri@oostcoast.nl>
 */
class PunishmentManager {
  private static $m_Punishments;

  /**
   * Loads the punishments file into memory
   */
  public static function initialize() {
    if (file_exists(Configuration::PunishmentsFile))
      self::$m_Punishments = json_decode(file_get_contents(Configuration::PunishmentsFile), true);
  }

  /**
   * Saves the punisments to disk
   */
  public static function savePunishments() {
    if(file_put_contents(Configuration::PunishmentsFile, json_encode(self::$m_Punishments)) == false)
      exit("\r\n!!!ERROR: Punishment file could not be saved.\r\n!!!Verify the configuration in " . getcwd() . '/Modules/Spock/Configuration.php');
  }

  /**
   * Adds a notice to the punishments log and saves it.
   *
   * @param string $type   What type of notice to add (ban, unban, kick, note)
   * @param string $target PlayerName or Ip to pin to the notice
   * @param string $reason The reason or note for the notice
   */
  public static function addPunishmentNotice($type, $target, $reason, $crewMemberName) {
    switch ($type) {
      case 'ban':
        self::$m_Punishments[] = array(
          'type'    => 'ban',
          'date'    => time(),
          'target'  => $target,
          'reason'  => $reason,
          'actor'   => $crewMemberName,
          'active'  => true);
        break;
      case 'unban':
        self::$m_Punishments[] = array(
          'type'    => 'unban',
          'date'    => time(),
          'target'  => $target,
          'reason'  => $reason,
          'actor'   => $crewMemberName);
        break;
      case 'kick':
        self::$m_Punishments[] = array(
          'type'    => 'kick',
          'date'    => time(),
          'target'  => $target,
          'reason'  => $reason,
          'actor'   => $crewMemberName);
        break;
      case 'note':
        self::$m_Punishments[] = array(
          'type'    => 'note',
          'date'    => time(),
          'target'  => $target,
          'reason'  => $reason,
          'actor'   => $crewMemberName);
        break;
    }

    self::savePunishments();
  }

  /**
   * Is the target currently banned?
   *
   * @param  string  $target PlayerName or Ip to lookup
   *
   * @return mixed         the reason if the PlayerName or Ip is banned or FALSE if it isn't
   */
  public static function isBanned($target) {
    foreach (self::$m_Punishments as $notice) {
      if ($notice['target'] == $target && $notice['active'] == true)
        return $notice['reason'] . ' by ' . $notice['actor'];
    }
    return false;
  }

  /**
   * Returns all noticesl kicks, bans & notes for a given playername
   *
   * @param  string $target Name of the player to lookup
   *
   * @return mixed         array containing all notices found for the player or FALSE if none were found
   */
  public static function wasBanned($target) {
    $notices = array();

    foreach (self::$m_Punishments as $notice) {
      if ($notice['target'] == $target)
        $notices[] = $notice;
    }
    
    if (isset($notices[0]))
      return $notices;
    else
      return false;
  }

  /**
   * Flags a ban as inactive
   *
   * @param  string $target Name or Ip of the ban entry to deactivate
   */
  public static function deactivateBanEntry($target) {
    foreach (self::$m_Punishments as $noticeId => $notice) {
      if ($notice['target'] == $target)
        self::$m_Punishments[$noticeId]['active'] = false;
    }
  }
};

class Punish {
  public static function BanPlayer($playerName, $reason, $crewMemberName) {
    ServerController::sendCommandToServer('/ban_name ' . $playerName);
    PunishmentManager::addPunishmentNotice('ban', $playerName, $reason, $crewMemberName);
  }

  public static function BanIp($ipAdress, $reason, $crewMemberName) {
    ServerController::sendCommandToServer('/ban_ip ' . $ipAdress);
    PunishmentManager::addPunishmentNotice('ban', $ipAdress, $reason, $crewMemberName);
  }

  public static function KickPlayer($playerName, $reason, $crewMemberName) {
    ServerController::sendCommandToServer('/kick' . $playerName);
    PunishmentManager::addPunishmentNotice('kick', $playerName, $reason, $crewMemberName);
  }

  public static function UnbanPlayer($playerName, $reason, $crewMemberName) {
    ServerController::sendCommandToServer('/unban_name ' . $playerName);
    PunishmentManager::deactivateBanEntry($playerName);
    PunishmentManager::addPunishmentNotice('unban', $playerName, $reason, $crewMemberName);
  }

  public static function UnbanIp($ipAdress, $reason, $crewMemberName) {
    ServerController::sendCommandToServer('/unban_ip ' . $ipAdress);
    PunishmentManager::deactivateBanEntry($ipAdress);
    PunishmentManager::addPunishmentNotice('unban', $ipAdress, $reason, $crewMemberName);
    //TODO find ban and active => false
  }
};
?>