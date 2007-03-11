<?

/* This file is responsbile for keeping the enviromental variables up.
 * This file will create objects when included, and should be
 * the only file included in actual pages. It will pull in others when needed.
 */
require_once("subs/base.php");
require_once("subs/html.php");
require_once("subs/user.php");
require_once("subs/config.php");
require_once("subs/db.php");
require_once("subs/session.php");
require_once("subs/plugins.php");
global $page;
global $session;
global $user;
global $config;
global $plugins;

$config = new config();
$db = new database();
$session = new session();
$plugins = new plugins();
$page = new page();

?>
