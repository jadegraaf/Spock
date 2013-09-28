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

use \ ModuleBase;
use \ Nuwani;
use \ Spock;
use \ UserStatus;


/**
 * Proccesses all commands send from IRC
 *
 * @author Joeri de Graaf <joeri@oostcoast.nl>
 */
class Commands {
  public static function processCommand(Bot $bot, $command, $parameters, $channel, $nickname, $userLevel) {
    switch ($command) {
      /*
        UserStatus::IsVoiced
        UserStatus::IsHalfOperator
        UserStatus::IsOperator
        UserStatus::IsProtected
      */
      
      /////////////////////////
      // Commands for anyone //
      /////////////////////////
      
      case 'msg':
        self::onMsgCommand($bot, $channel, $nickname, $parameters);
        return true;

      case 'players':
        self::onPlayersCommand($bot, $channel);
        return true;

      case 'version':
        self::onVersionCommand($bot, $channel);
        return true;
      
      /////////////////////
      // Voiced commands //
      /////////////////////

      ////////////////////////////
      // Half-Operator commands //
      ////////////////////////////

      ///////////////////////
      // Operator commands //
      ///////////////////////
      
      case 'kick':
        if ($userLevel >= UserStatus::IsOperator)
          self::onKickCommand($bot, $channel, $nickname, $parameters);
        return true;

      case 'ban':
        if ($userLevel >= UserStatus::IsOperator)
          self::onBanCommand($bot, $channel, $nickname, $parameters);
        return true;

      case 'unban':
        if ($userLevel >= UserStatus::IsOperator)
          self::onUnbanCommand($bot, $channel, $nickname, $parameters);
        return true;

      case 'isbanned':
        if ($userLevel >= UserStatus::IsOperator)
          self::onIsBannedCommand($bot, $channel, $parameters);
        return true;

      case 'why':
        if ($userLevel >= UserStatus::IsOperator)
          self::onWhyCommand($bot, $channel, $parameters);
        return true;

      case 'addnote':
        if ($userLevel >= UserStatus::IsOperator)
          self::onAddnoteCommand($bot, $channel, $nickname, $parameters);
        return true;

      case 'say':
        if ($userLevel >= UserStatus::IsOperator)
          self::onSayCommand($bot, $channel, $nickname, $parameters);
        return true;

      ////////////////////////
      // Protected commands //
      ////////////////////////
      
      case 'startserver':
        if ($userLevel >= UserStatus::IsProtected)
          self::onStartServerCommand($bot, $channel);
        return true;

      case 'stopserver':
        if ($userLevel >= UserStatus::IsProtected)
          self::onStopServerCommand($bot, $channel);
        return true;
    }

    return false;
  }

  // Handles !msg
  private static function onMsgCommand(Bot $bot, $channel, $nickname, $parameters) {
    if (!count($parameters))
      return CommandHelper::usageMessage($bot, $channel, '!msg [message]');

    if (!ServerController::getStarmadeServerState() && !Configuration::IsBeta)
      return CommandHelper::errorMessage($bot, $channel, 'Command not availible while the server is offline');

    $message = implode(' ', $parameters);

    ServerController::broadcastToServer('[IRC] ' . $nickname . ': ' . $message);
    return $bot->send('PRIVMSG ' . $channel . ' :06' . $nickname . '01: ' . $message);
  }

  // Handles !Players
  private static function onPlayersCommand(Bot $bot, $channel) {
    if (!ServerController::getStarmadeServerState() && !Configuration::IsBeta)
      return CommandHelper::errorMessage($bot, $channel, 'Command not availible while the server is offline');
    
    $playerList = PlayerTracker::getPlayerList();
    
    if (count($playerList) == 0)
      CommandHelper::longMessage($bot, $channel, ModuleBase::BOLD . ModuleBase::COLOUR_ORANGE . 'Online Players' . ModuleBase::CLEAR . ' (0): There are no players ingame');
    else {
      sort($playerList);
    CommandHelper::longMessage($bot, $channel, ModuleBase::BOLD . ModuleBase::COLOUR_ORANGE . 'Online Players ' . ModuleBase::CLEAR . '(' . 
      count($playerList) . '): ' . implode(', ', $playerList));
    }
  }

  // Handles !startserver
  private static function onStartServerCommand(Bot $bot, $channel) {
    if (!ServerController::getStarmadeServerState()) {
      CommandHelper::infoMessage($bot, $channel, 'Server Initializing');
      ServerController::startStarmadeServer();
    }
    else
      CommandHelper::errorMessage($bot, $channel, 'The server is already running');
  }

  // Handles !startserver
  private static function onStopServerCommand(Bot $bot, $channel) {
    if (ServerController::getStarmadeServerState()) {
      ServerController::broadcastToServer('The server will shut down in 10 seconds');
      CommandHelper::infoMessage($bot, $channel, 'Server shutting down in 10 seconds');
      ServerController::stopStarmadeServer();
    }
    else
      CommandHelper::errorMessage($bot, $channel, 'The server is not running');
  }

  // Handles !version
  private static function onVersionCommand(Bot $bot, $channel) {
    $version = ServerController::getStarmadeServerVersionNumber();

    if ($version[0] == '')
      return CommandHelper::errorMessage($bot, $channel, 'Unknown version, the server must be restarted');
    else
      return CommandHelper::longMessage($bot, $channel, ModuleBase::COLOUR_ORANGE . 'Starmade Version: ' . ModuleBase::CLEAR . $version[0] . ModuleBase::COLOUR_ORANGE . ' Build: ' . ModuleBase::CLEAR . $version[1]);
  }

  // Handles !kick
  private static function onKickCommand(Bot $bot, $channel, $nickname, $parameters) {
    if (!count($parameters))
      return CommandHelper::usageMessage($bot, $channel, '!kick Name reason');

    if (!ServerController::getStarmadeServerState() && !Configuration::IsBeta)
      return CommandHelper::errorMessage($bot, $channel, 'Command not availible while the server is offline');

    $target = $parameters[0];
    array_shift($parameters);
    $reason = implode(' ', $parameters);

    if (!isset($reason) || strlen($reason) <= 3)
      return CommandHelper::errorMessage($bot, $channel, 'A reason must be given and needs to be longer then 3 characters');
    if (!PlayerTracker::isPlayerConnected($target))
      return CommandHelper::errorMessage($bot, $channel, $target . ' is not on the server');
    else
      Punish::kickPlayer($target, $reason, $nickname);

    CommandHelper::successMessage($bot, $channel, $target . ' has been kicked by ' . $nickname);
  }

  // Handles !ban
  private static function onBanCommand(Bot $bot, $channel, $nickname, $parameters) {
    if (!count($parameters))
      return CommandHelper::usageMessage($bot, $channel, '!ban [Name/Ip] reason');

    if (!ServerController::getStarmadeServerState() && !Configuration::IsBeta)
      return CommandHelper::errorMessage($bot, $channel, 'Command not availible while the server is offline');

    $target = $parameters[0];
    array_shift($parameters);
    $reason = implode(' ', $parameters);

    if (!isset($reason) || strlen($reason) <= 3)
      return CommandHelper::errorMessage($bot, $channel, 'A reason must be given and needs to be longer then 3 characters');
 
    if (PunishmentManager::isBanned($target))
      return CommandHelper::infoMessage($bot, $channel, $target . ' is already banned');

    // Lets see if we want to ban an Ip
    if (filter_var($target, FILTER_VALIDATE_IP) != false)
      Punish::banIp($target, $reason, $nickname);
    elseif (self::isValidNickname($target))
      Punish::banPlayer($target, $reason, $nickname);
    else
      return CommandHelper::errorMessage($bot, $channel, 'No valid nickname or Ip entered');

    CommandHelper::successMessage($bot, $channel, $target . ' has been banned by ' . $nickname);
  }

  // Handles !unban
  private static function onUnbanCommand(Bot $bot, $channel, $nickname, $parameters) {
    if (!count($parameters))
      return CommandHelper::usageMessage($bot, $channel, '!unban [Name/Ip] reason');

    if (!ServerController::getStarmadeServerState() && !Configuration::IsBeta)
      return CommandHelper::errorMessage($bot, $channel, 'Command not availible while the server is offline');

    $target = $parameters[0];
    array_shift($parameters);
    $reason = implode(' ', $parameters);

    if (!isset($reason) || strlen($reason) <= 3)
      return CommandHelper::errorMessage($bot, $channel, 'A reason must be given and needs to be longer then 3 characters');

    if (!PunishmentManager::isBanned($target))
      return CommandHelper::infoMessage($bot, $channel, $target . ' is not banned');

    // Lets see if we want to ban an Ip
    if (filter_var($target, FILTER_VALIDATE_IP) !== false)
      Punish::unbanIp($target, $reason, $nickname);
    elseif (self::isValidNickname($target))
      Punish::unbanPlayer($target, $reason, $nickname);
    else
      return CommandHelper::errorMessage($bot, $channel, 'No valid nickname or Ip entered');

    CommandHelper::infoMessage($bot, $channel, $target . ' has been unbanned');

  }

  // Handles !isbanned
  private static function onIsBannedCommand(Bot $bot, $channel, $parameters) {
    if (count($parameters) != 1)
      return CommandHelper::usageMessage($bot, $channel, '!isbanned [Name/Ip');
    $reason = PunishmentManager::isbanned($parameters[0]);

    if (!$reason)
      return CommandHelper::infoMessage($bot, $channel, $parameters[0] . ' is not banned');
    else
      return CommandHelper::infoMessage($bot, $channel, $parameters[0] . ' has been banned for: ' . $reason);
  }

  // Handles !why
  private static function onWhyCommand(Bot $bot, $channel, $parameters) {
    if (count($parameters) != 1)
      return CommandHelper::usageMessage($bot, $channel, '!why Name [page]');

    $entries = PunishmentManager::wasbanned($parameters[0]);

    if(!$entries)
      return CommandHelper::infoMessage($bot, $channel, 'no entries found for ' . $parameters[0]);

    $entries = array_slice($entries, 0, 10);

    $bot->send('PRIVMSG ' . Configuration::EchoChannel . ' :' . ModuleBase::COLOUR_RED . '*** player log for ' . $parameters[0] . ' (' . count($entries) . 
      ' entries)');   
    
    foreach ($entries as $entry) {
      $bot->send('PRIVMSG ' . Configuration::EchoChannel . ' :' . ModuleBase::COLOUR_RED . date('[j-n-Y G:i:h] ',$entry['date']) . ModuleBase::COLOUR_DARKGREEN . 
        $entry['type']  . ' by ' . $entry['actor'] . ModuleBase::CLEAR . ': ' . $entry['reason']);
    }
  }

  // Handles !addnote
  private static function onAddnoteCommand(Bot $bot, $channel, $nickname, $parameters) {
    if (count($parameters) < 2)
      return CommandHelper::usageMessage($bot, $channel, '!addnote Name Note');

    $target = $parameters[0];
    array_shift($parameters);
    $note = implode(' ', $parameters);

    if (strlen($note) <= 3)
      return CommandHelper::errorMessage($bot, $channel, 'A note must be longer then 3 characters');
    if (!self::isValidNickname($target))
      return CommandHelper::errorMessage($bot, $channel, 'Invalid player name');

    PunishmentManager::addPunishmentNotice('note', $target, $note, $nickname);
    CommandHelper::successMessage($bot, $channel, 'Note added');
  }

  // Handles !say
  private static function onSayCommand(Bot $bot, $channel, $nickname, $parameters) {
    if (!count($parameters))
      return CommandHelper::usageMessage($bot, $channel, '!say [message]');

    if (!ServerController::getStarmadeServerState() && !Configuration::IsBeta)
      return CommandHelper::errorMessage($bot, $channel, 'Command not availible while the server is offline');

    $message = implode(' ', $parameters);

    ServerController::broadcastToServer('[ADMIN]' . $nickname . ': ' . $message);
    return $bot->send('PRIVMSG ' . $channel . ' :06 [ADMIN]' . $nickname . '01: ' . $message);
  }

  // Utility function to validate a nickname.
  private static function isValidNickname($nickname) {
    return preg_match('/^[A-Za-z0-9\[\]\.\$\=\@\(\)_]{3,23}$/', $nickname);
  }

  // Utility function to sort an array by colum
  private static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
  }
};