*******************************************************
*  Ragnarok Online Control Panel Coded by Azndragon   *
*                     Skin by zack                    *
*                    Version 4.x.x                    *
*******************************************************

Home Page: http://rocp.ragonline.net
Latest versions of CP can be located at http://azndragon.ragonline.net/forums
IRC Channel:

irc.deltaanime.net
#rocp

Information about this script can also be located in the RO Emulator Section-->Tools & Programs section on Aegis Support Boards.

Note: Cookies MUST be enabled to login properly!

Please support ACP development by reporting bugs!
Please read the readme (this file) before asking about installation/config issues.

*********************
* What is the ROCP? *
*********************

This is a PHP powered script designed for users running Aegis or the SQL versions of Athena. It provides user management for the players, as well as advanced server management for the host and GMs.

***************************
* What's new in Version 4?*
***************************

The biggest change will be the addition of Aegis compatibility, so Aegis users can experience what Athena users have used for a long time. A more subtle change it the increase in security features in the pages, such as increased difficulty to SQL inject, as well as encrypted ID Numbers, thereby reducing the chances of SQL injection. Registration has also been improved, with Email & Admin validation options. Consult versioninfo.txt for more detailed information.

The multiple language support has been delayed, so this is not a new feature yet.

Note: The roster queries have been changed a bit, so you should either manually move them, or run the upgrade query in the upgrade folder.

****************************
* Before Installing the CP *
****************************

For version 4, if you had a previous installation of the CP, please uninstall all the tables.

In order to properly install the Control Panel, you must have:

A working server database, the CP relies on existing databases for usage.

PHP & MySQL-enabled Webhost with access to:
	- PHP 4.3.8 or later. (5.0.0 takes slightly more steps to install MySQL) Make sure you use the extract version of PHP, not the installer.
	- MySQL 4.0 for Athena, MSSQL for Aegis
Highly recommended to use those 2 versions, or problems may arise, depending on how old your version is.

Aegis: Query Analyzer (Enterprise Manager won't create tables through SQL queries)
Athena: PhpMyAdmin, MySQL CC, or any other program you will use to execute your installer queries.

Not only do you need software, but it is strongly recommended that you:

Have good knowledge of SQL and PHP (Syntax, how it works)
For Aegis Users: Knowing how to install stored procedures and user-defined functions.
Know how to execute SQL queries (Can't install tables without knowing how to execute queries!)
Know proper SQL practice and procedures (table permissions, changing hosts/passwords)
Have general knowledge of how the tables for SQL Athena work (So you know what table does what)
Know how to setup and configure your webserver correctly and securely. (So .php files can be read properly, and secures your webserver from hackers)
Know how to read at a Grade 4 level, at least. (Seriously, some people need to know how to read, or they will not be able to install & config properly)

******************************************************************
Note: This guide also not guide you through the following things:

- Installing Aegis/Athena
- Installing & configuring your webhost
- Installing & configuring PHP
- Installing & configuring MySQL
- Installing a MySQL GUI (a common choice)
- Executing Queries

You can search the internet for help on these areas.

Recommended Links for reading:

http://rorussia.ru.zara.mtw.ru/asb/index.php (Aegis Forums)
http://www.eathena.deltaanime.net/forum/index.php (eAthena Forums)
http://agelessanime.com/aaforums/index.php (OmniAthena Forums)
http://apache.mirror.cygnal.ca/httpd/binaries/win32/apache_1.3.31-win32-x86-no_src.exe (Apache Download)
http://dev.mysql.com/downloads/mysql/4.0.html (MySQL Download)
http://dev.mysql.com/downloads/other/mysqlcc.html (MySQL CC Download)
http://dev.mysql.com/downloads/query-browser/ (MySQL Query Browser Download)
http://www.php.net/downloads.php (PHP Download)
http://ca3.php.net/manual/en/ref.mssql.php (Enabling MSSQL for PHP)
http://ca3.php.net/manual/en/ref.mysql.php (Enabling MySQL for PHP)
http://ca3.php.net/manual/en/faq.databases.php#faq.databases.mysql.php5 (Enabling MySQL for PHP5)
http://www.phpmyadmin.net (phpMyAdmin for Athena users)
******************************************************************

If you are not proficient in the above requirements, the Ragnarok Online Control Panel may not be right to you, due to security issues, as well as potential database damage if you do not know what you are doing. Otherwise, carry on.

*********************
* Extracting the CP *
*********************

First, you must extract the contents of the .rar file into your webhost folder, where users can access it through index.php.

For those extracting the CP to a webhost, ensure that edit_config.php have read/write access.

**************************************************
* Quick and Easy Installation for Advanced Users *
*        (Know Aegis/Athena/CP/SQL well)         *
**************************************************

Edit config.php using your basic editor, the most important ones being db_host, db_username, db_password, db_name (for athena), passphrase, and server_type (0 = Aegis, 1 = OmniAthena, 2 = eAthena).
Note for Athena Users: db_name IS NOT cp! It is the database where your server information is stored (ragnarok by default).

Create Database cp, ensure proper permissions to the cp, and ragnarok server databases. If you are using OmniAthena, and wish to use logs, give it access to log database.

For Aegis Users, install the stored procedures and functions from install_functions.txt
For Athena Users, install mob_db and item_db if you are not using the SQL item_db/mob_db.

Run the following query to make an admin account:

Aegis Users:
INSERT INTO cp.dbo.privilege SELECT AID, 4 FROM nLogin.dbo.login WHERE ID = 'your account name';
	
Athena Users:
INSERT INTO cp.privilege SELECT account_id, 4 FROM ragnarok.login WHERE userid = 'your account name';

Log into the control panel, and add admins/GMs using the GM Menu Option 'Privileges'.

****************************
* Setting Up the Databases *
****************************

Aegis Users:

Create a new database, called 'cp'.
Follow instructions in install_functions.sql
Run the queries in install/Aegis/install.sql
Create a new login 'cp', password, your choice, default database cp with system administration privileges, and give it access to the following databases:

	cp, character, itemLog, nLogin, script, user

Athena Users:

Open install\install.sql, copy the entire query, and execute it with your chosen SQL program. It should create a database named 'cp', and a login, with proper permissions.
If you are using the item_db and mob_db tables for your server, skip the next step.
However, if you are not, you are advised to install item_mob_db.sql so that item and mob conversions can be performed.

*********************************
* Configuring the Control Panel *
*********************************

Edit config.php to your liking.
Register yourself an admin account on your server, if you havn't already.
Log into the Control Panel with the admin account that created using the query in previous section.
You can add more admins in the Privileges section of the drop down menu.

******************************
* How do I uninstall the CP? *
******************************

Aegis: Drop the cp database, or just right click the cp database in Enterprise Manager, and click delete. (Query: DROP DATABASE cp)
Athena: Simply run the queries in uninstall.sql, which will delete the tables that are for the CP only.

************************************
* How do I Change the CP Settings? *
************************************

There are a few files that you can change to your liking.

config.php - Most of the configuration options
style.css - The style of the text, and tables
images\acp.jpg - The banner at the top of the page.
class.def & class_advanced.def - The different class names. class_advanced.def is not used for Aegis.
guild_castles.def - The names of the different castles.
conf.def - A brief description of each of the configuration variables, used for edit_config.php.
Do NOT delete the emblem folder, even though it's blank! You will need it for saving guild emblems.

********************************
* What can the ROCP do for me? *
********************************

Note: Some options are restricted to only certain server installations.

General:
Server Management System for Aegis & SQL Athena Ragnarok Online Servers
Efficient SQL queries, to reduce load time.
GZIP Compatible, for faster load times.
Easily skinnable, with the use of changing the CSS through CP.

Normal Users:
Announcements for all users
Server Information (Forums, IRC, rules, # of accounts/players, # of each class)
Who's Online (Athena Only)
Full Character Ladder
Guild Ladder (Guilds and Castles)
Changing Account Options (password, email, gender)
Changing Character Hairstyles
Money transfer between characters on the same account.
Uploading Guild Emblems by Guild Master
Item & Monster database

GMs:
Announcements that can only be viewed by GMs and above.
Account/Character/Guild Management
Add accounts manually
Single Accounts/Characters can be banned/unbanned.
Remove accounts/players based on login frequency, or illegal ASCII characters in name.
Clear all information associated with currently banned accounts.
Remove the temporary bans caused by failed password attempts. (Athena Only)
Edit/Delete the current announcements.
Item Management, which allows for the searching of items, by item name, refine level, by account, or by character.
View the list of all accounts currently ignored from the ladder.
View Characters/Account associated with a given Character/Account ID/Name.
View the list of currently banned accounts.
View the logs of accounts banned, money transfer, and user actions.
Who's Online (Aegis Only)

Admins:
Announcements that can only be viewed by Admins.
All passwords will be visible in Account Management.
Search for illegal character names.
Delete inactive accounts (Athena Only)
Manage GM/Admin Privileges, and view page privileges.
Full SQL dump of the server's current information. (Athena Only)
Full editing of the configuration.

View the logs of:
Access
Admin Actions
Bans
Login Logs (Athena Only)
Possible Exploit Attempts
Server Logs (OmniAthena Only)
Trade Logs (Aegis Only)

Security:
Access levels are handled server-side, not browser-side.
Minimal cookies required.
All Major queries & actions are logged.
GM/Admin accounts are logged when logging in.
Passwords are encrypted before being sent.
All ID Numbers are encrypted before being sent to the user.
Strong filters rejecting special characters in inputs.
Various Registration methods, equipped with anti-bot measures.

************
* Security *
************

- HIGHLY recommended to use a long passphrase in config.php, as this is what is used to encrypt the cookies.
- Enforce strong passwords for GM, and especially Admin Accounts, because account names WILL be displayed publicly to all users in announcements.
- Be wary of who you give Admin access to, because they have tremendous power over the server.
- GMs/Admins should read logs once in a while, to view possible exploits. Example: Access Log allows you to view all IPs that have logged into GM/Admin accounts, and you will be able to see if someone has accessed your login. Exploit log can possibly show if a user is trying to cheat the script, whether by SQL injection, or clever/illegal inputs.
- Athena: Backup regularly! You never know when an Admin will suddenly turn on you, a backup would be nice.
  Aegis: The CP does not allow for backups, however, you should schedule backups using Enterprise Manager.

**************
* Privileges *
**************

Guest - No need to log in, sees minimum pages.
User - Allows for basic page viewing, as well as editing some basic account/character options.
Game GM - Same as user, but is flagged as a GM in some of the more advanced pages.
GM - Access to some mid-level pages, such as editing user accounts/characters/guilds, as well as banning, and some logs.
Admin - Full access to the most powerful pages, as well as full log-viewing.

*************************
* Browser Compatibility *
*************************

This script has been tested with the latest version of IE, Mozilla, and Opera, and should work fine. It looks ugly with IE, however. Not that you should be using IE anyways. There may be a few layout issues with some browsers, so report any weird activity to me.

********
* FAQs *
********

Q: Will this be made for text servers (Athena)?
A: No, never will. I despise text-based servers.

Q: I found _____ bug. What do I do?
A: Report it to the forums. Please be thorough in your explanation.

Q: I have a really good idea for the CP! What do I do?
A: Post it in the forums. If I like it, I will add it, and give credit.

Q: Fatal error: Call to undefined function mysql_connect() in [htdocs]\adodb\drivers\adodb-mysql.inc.php on line 326
A: You are running PHP 5, which does not have MySQL enabled by default. Check out the following links:
http://ca3.php.net/manual/en/ref.mysql.php (Enabling MySQL for PHP)
http://ca3.php.net/manual/en/faq.databases.php#faq.databases.mysql.php5 (Enabling MySQL for PHP5)

Q: Fatal error: Call to undefined function: mssql_get_last_message() in [htdocs]\adodb\drivers\adodb-mssql.inc.php on line 419
A: You do not have MSSQL enabled. Check out http://ca3.php.net/manual/en/ref.mssql.php (Enabling MSSQL for PHP)

Q: Query (SELECT ID, Name, LV, HP, EXP, JEXP, ATK1, ATK2, DEF, MDEF, STR, AGI, VIT, `INT`, DEX, LUK, Scale, Race, Element, Drop1id, Drop1per, Drop2id, Drop2per, Drop3id, Drop3per, Drop4id, Drop4per, Drop5id, Drop5per, Drop6id, Drop6per, Drop7id, Drop7per, Drop8id, Drop8per FROM mob_db ORDER BY ID) failed: Unknown column 'Drop7id' in 'field list'
A: eAthena made a typo, using Drop7d instead of Drop7id. Rename the column yourself. It will not affect server performance in any way.
Query: ALTER TABLE `mob_db` CHANGE `Drop7d` `Drop7id` MEDIUMINT( 9 ) DEFAULT '0' NOT NULL 

******************
* Stuff About Me *
******************

I am azndragon, coder of the ROCP. Not only have I coded the Control Panel, I have also coded a character roster system for Aegis servers as well. I have also made a droplist program in VB, as well as PHP, and have made various other programs in VB (Upgrade Simulator, OBB/OVB/OCA/GB Simulator). You can find me in the AgelessAnime forums, eAthena Forums, Aegis Support Boards, or at my own forums.

*********
* Notes *
*********

- Make sure you read config.php all the way through, and understand what each option does.
- PLEASE leave credit to me at the bottom of the script! I have worked hard on this, and I deserve the credit.
- Please do not ask me about server issues! 99.99% of the time, I am not the owner/GM of the server.

***********
* Credits *
***********

Thanks to the following people:

Nucleo - For helping me with some layout, as well as a few coding sections.
Development Team of AthenaAdvanced/OmniAthena - Adding features that the CP can use, and just for general support :)
Nexus - For some bug fixes, along with the GZIP feature added to the CP.
zack - For lots of help with layouts and design, especially version 3 update.
Invision Power Board - For the anti-bot code.
joeh - Lots of bug fixes, as well as support.
maldiablo - For the inspiration of the roster system.
DysfunktinaL for clearing up some issues with Stored Procedures.
serra for her email code.
GamemasterX for determining what each value for effectstate, healthstate, bodyeffect meant.

Thank you to the following servers for helping me bug test:

Sandhawk of OmegaRO
kAkEAsHi of seraphRO
Mass Zero & Realrena of NovaRO
DysfunktinaL & zack of EvilRO

************
* Donators *
************

berserker703
JiGGaWHO
PoW

Please donate if you feel that this project has helped you greatly, and would like to support me on future versions. Donation link can be found at: http://rocp.ragonline.net/donate.php.