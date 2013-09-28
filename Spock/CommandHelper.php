<?php
/**
 * Copyright (c) 2006-2013 Las Venturas Playground
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

class CommandHelper {

  public static function infoMessage(Bot $bot, $channel, $infoMessage) {
    self::channelMessage($bot, $channel, '10* Info: ' . $infoMessage);
  }

  public static function usageMessage(Bot $bot, $channel, $usageMessage) {
    self::channelMessage($bot, $channel, '7* Usage: ' . $usageMessage);
  }

  public static function errorMessage(Bot $bot, $channel, $errorMessage) {
    self::channelMessage($bot, $channel, '4* Error: ' . $errorMessage);
  }
  
  public static function successMessage(Bot $bot, $channel, $errorMessage) {
    self::channelMessage($bot, $channel, '3* Success: ' . $errorMessage);
  }

  public static function channelMessage(Bot $bot, $channel, $message) {
    $bot->send('PRIVMSG ' . $channel . ' :' . $message);
  }

  // Splits up an long string into messages of 450 characters
  public static function longMessage(Bot $bot, $channel, $message) {
    $message = wordwrap($message, 450, PHP_EOL);
    $message = explode(PHP_EOL, $message);

    foreach ($message as $line) {
        $bot->send('PRIVMSG ' . $channel . ' :' . $line);
    }
  }

}