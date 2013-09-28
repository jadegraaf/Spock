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
 * This class is used for configuration of the different functions in this module
 * See the desciption of each to see what does what
 *
 * @author Joeri de Graaf <joeri@oostcoast.nl>
 */
class Configuration {

  //////
  // File Configuration
  /////
  
  // The following path is your starmade server directory
  const StarmadeServerDirectory = '/home/starmade/Server';

  // Where would you like the punishment log to be saved. Make sure the directory exists.
  // This file contains all current and previous bans, kicks and notes
  const PunishmentsFile = '/home/starmade/Server/banlog.json';
  
  //////
  // Channel Configuration
  //////

  // These are the channels used to output various messages to
  // The main echo channel is where all chat, join, part and ingame ban/kicks are broadcasted to
  const EchoChannel = '#MyChannelName';
  // The permissions channel is the one used to determine is a user is allowed to use a command. See command restrictions in Commands.php
  // Crewmembers are given '@' operator status and are allowed to use crew commands like !ban and certain messages are only send to everyone with '%' and above.
  const PermissionsChannel = '#MyChannelName';
  

  
  /////
  // Under the hood
  // CAUTION: only make modifications to the following if you know what you're doing/breaking
  /////
  
  // ServerLog fetch interval (miliseconds)
  const UpdateInterval = 100;
  // Which prefix is used for channel commands
  const CommandPrefix = '!';
  // Development switch
  const IsBeta = false;

  // Currently not in use:
  #const CrewChannel = '#LVP.Starmade.Crew';
  #const DevelopmentChannel = '#LVP.Starmade.Dev';  
  
};
?>