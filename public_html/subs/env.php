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
global $me;
class down extends box
{
		function endit()
		{
			global $page;
			for($tmp = 0; $tmp < $this->nItems; $tmp++)
				$this->items[$tmp]->endit();
			$page->output();
		}
}
function down()
{
	global $down;
	$down->endit();
}

register_shutdown_function(down);

$down = new down();
$config = new config();
$db = new database();
$session = new session();
$plugins = new plugins();
$page = new page();
$me = new myuser();
$page->ctrl2->add($me);
?>
