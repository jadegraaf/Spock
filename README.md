#Spock
A PHP based IRC echo bot for the Starmade server

###Introduction
Spock intergrates a [Starmade](http://star-made.org/) server with IRC (Internet Relay Chat). It broadcasts various events to an channel and allows crewmembers to administrate the server without having to be ingame.

###Features
- Broadcasting of ingame chat, join and part messages
- A punishment system that keeps track of all previous offences
- Various commands to make your life easier. The full list is in the wiki

### Instalation

#### Requirements
- A Linux based operating system
- The ability to start php scripts from shell
- FTP/SFTP access to the server
- A copy of the [Nuwani IRC Platform](https://code.google.com/p/nuwani/). Version 2.3-rc1 is recommended.
- A Starmade server being run inside a [screen](http://www.rackaid.com/resources/linux-screen-tutorial-and-how-to/)

#### Bringing Spock alive, Step by step
1. Login using the user that runs the Starmade server
2. Shutdown the Starmade server if it is running
3. Make a directory to house the bot in. I suggest you keep this isolated from the Starmade server e.g. /home/starmade/Server for starmade and /home/starmade/Nuwani for the bot
3. Start a screen with name 'starmade' and de-attach yourself using Control+A D:
```
$ screen -S starmade
```
4. Download a copy of [Nuwani](https://code.google.com/p/nuwani/downloads/list). 2.3-rc1 is recommended.
5. Upload all file from the scr directory to the directory you've created for the bot.
6. Edit example_config.php to your needs and rename it to config.php
  - Modify the 'Networks' array to where you want to deploy the bot
  - Modify the 'Bots' array to your liking
  - List all channels you want the bot to join on line 83, separating them with a comma eg '#Myserver', '#Myserver.crew channelpass'
7. Download a copy of [Spock](https://github.com/oostcoast/Spock/archive/master.zip)
8. Upload the contents to the Modules directory
9. Edit Modules/Spock/Configuration.php to your situation
10. Start the bot:

```
$ cd /home/starmade/Nuwani
$ nohup php run.php&
```
The bot should be joining your channel within a few seconds. If nothing happens, check nohop.out for any error messages.










