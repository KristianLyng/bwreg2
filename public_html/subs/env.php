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
require_once("subs/events.php");
require_once("Text/Wiki.php");
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
$wiki = new Text_Wiki();
$config = new config();
$db = new database();
$session = new session();

$plugins = new plugins();

$page = new page();
$me = new myuser();
$event = new event();
if ($event->gid == 0)
	print "No such genre/event";

$page->htmltitle = $event->title;
$page->header = $event->title;
$page->logo->add(img($event->logo,$event->title));

$page->ctrl2->add($me);
?>
