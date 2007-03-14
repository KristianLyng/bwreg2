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
require_once("subs/data.php");
require_once("Text/Wiki.php");
global $page;
global $session;
global $user;
global $config;
global $plugins;
global $me;
global $maincontent;
class down extends box
{
		function endit()
		{
			global $page;
			for($tmp = 0; $tmp < $this->nItems; $tmp++)
				$this->items[$tmp]->endit();
			$page->output();
			$_SESSION['action'] = $session->action;
		}
}
function down()
{
	global $down;
	$down->endit();
}

register_shutdown_function(down);

$down = new down();
$wiki =& new Text_Wiki();
$wiki->setRenderConf('xhtml', 'wikilink', 'view_url', $_SERVER['PHP_SELF'] . '?page=');
$wiki->setRenderConf('xhtml', 'wikilink', 'new_url', $_SERVER['PHP_SELF'] . '?page=');
$wiki->setRenderConf('xhtml', 'wikilink', 'pages',null);
$sites = array(
"news" => $_SERVER['PHP_SELF'] . '?page=News&news=%s', 
"user" => $_SERVER['PHP_SELF'] . '?page=Userinfo&user=%s');
	

$wiki->setRenderConf('xhtml', 'interwiki','sites', $sites);
$wiki->setRenderConf('xhtml', 'interwiki','target', null);
$wiki->setRenderConf('xhtml', 'url','target', null);
$session = new session();
$config = new config();
$db = new database();

$plugins = new plugins();

$page = new page();
$event = new event();
$me = new myuser();
$maincontent = new content();
if(!isset($maincontent->content))
{
	$page->info4->add(new content("ErrorPageNotFound"));
	if(strstr($me->permission($maincontent->permission),"w"))
		$page->info4->add(new content("ErrorPageNotFoundAdmin"));
} 
$page->content->add(new content());
if ($event->gid == 0)
	print "No such genre/event";

if(strstr($me->permission($maincontent->permission),"w"))
	$page->info2->add($maincontent->editlink());

$page->htmltitle = $event->title;
$page->header = $event->title;
$page->logo->add(img($event->logo,$event->title));
$page->set_css($event->css);

$page->ctrl2->add($me);
?>
