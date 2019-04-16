# Roblox-Auto-PHP
Automatically generate a PHP-Based Roblox Web API Wrapper. I doubt this is very safe so it is **highly** recommended you **don't use this in production** without verifying each function first.

## How this works
This simple program scrapes Roblox's Swagger.io docs for their info, and then generates a file that allows you to communicate with the endpoints easily. It takes seconds to generate the file.

## Installation Instructions
If you don't want to run the script yourself, you can just download roblox.php and require it into your project. If you want to install the program yourself, however, continue with the installation instructions.

1. Download the `install` folder
2. Upload it to your server and run `php install.php` (or visit it from the web)
3. Once it's done loading, a file will be generated called `roblox_[timestamp].txt`. It's recommended you check for syntax errors, and then change the file extension to `.php`.
4. `require` the script into wherever you need it. When calling the new class, you can optionally enter a .ROBLOSECURITY for authentication. Without it, all requests you make won't be authenticated. View some examples below for more info.

## Examples

### With Authentication
```php
require('roblox.php');
$roblox = new roblox("_|WARNING:-DO-NOT-SHARE-THIS.--Sharing-this-will-allow-someone-to-log-in-as-you-and-to-steal-your-ROBUX-and-items.|_...");
var_dump($roblox->postv1groupspayouts(1, "FixedAmount", 1, "User", 25));
// Example Output:
/*
array{ ["success"]=>bool(true),["data"]=> string(2)"{}", ["statuscode"]=>int(200) }
*/
```

### Without Authentication
```php
require('roblox.php');
$roblox = new roblox;
var_dump($roblox->getv1groupmembership(1));
// Example Output:
/*
array(2) { ["data"]=> string(755) "{"groupId":1,"isPrimary":false,"isPendingJoin":false,"userRole":{"user":null,"role":{"id":231,"name":"Guest","rank":0,"memberCount":0}},"maxGroups":0,"permissions":{"groupPostsPermissions":{"viewWall":true,"postToWall":false,"deleteFromWall":false,"viewStatus":false,"postToStatus":false},"groupMembershipPermissions":{"changeRank":false,"inviteMembers":false,"removeMembers":false},"groupManagementPermissions":{"manageRelationships":false,"manageClan":false,"viewAuditLogs":false},"groupEconomyPermissions":{"spendGroupFunds":false,"advertiseGroup":false,"createItems":false,"manageItems":false,"addGroupPlaces":false,"manageGroupGames":false,"viewGroupPayouts":false}},"areGroupGamesVisible":false,"areGroupFundsVisible":false,"areEnemiesAllowed":true}" ["statuscode"]=> int(200) }
*/
```

## Todo:
* Hopefully add camelCasing or at least underscores to function names to make them less confusing
* Add support for undocumented endpoints (manually, of course)
* Add support for `application/x-www-form-urlencoded` (only supports url parameters, json arrays, and path at the moment)
