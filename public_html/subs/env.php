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
$execaction = array();
function down()
{
	global $page;
	$page->output();
	$_SESSION['action'] = $session->action;
}
function &add_action($action, &$object)
{
	global $execaction;
	$tmp =& $execaction[$action];
	$execaction[$action] =& $object;
	return $tmp;
}

/* Make sure we render and update action when the page is done */
	register_shutdown_function(down);

/* Set up the wiki-object */
	$wiki =& new Text_Wiki();
	$wiki->setRenderConf('xhtml', 'wikilink', 'view_url', 
						 $_SERVER['PHP_SELF'] . '?page=');
	$wiki->setRenderConf('xhtml', 'wikilink', 'new_url', 
						 $_SERVER['PHP_SELF'] . '?page=');
	$wiki->setRenderConf('xhtml', 'wikilink', 'pages',null);
	$sites = array(
		"news" => $_SERVER['PHP_SELF'] . '?page=News&news=%s', 
		"force" => $_SERVER['PHP_SELF'] . '?page=%s', 
		"version" => $_SERVER['PHP_SELF'] . '?action=ContentGetVersion&version=%s', 
		"diff" => $_SERVER['PHP_SELF'] . '?action=ContentDiff&version=%s', 
		"user" => $_SERVER['PHP_SELF'] . '?page=Userinfo&action=UserGetInfo&user=%s');
	$wiki->setRenderConf('xhtml', 'interwiki','sites', $sites);
	$wiki->setRenderConf('xhtml', 'interwiki','target', null);
	$wiki->setRenderConf('xhtml', 'url','target', null);

/* Set up the basic enviroment */
	$session = new session();
	$config = new config();
	$db = new database();

/* Possibly load plugins (Nonfunctional at themoment */
	$plugins = new plugins();

/* Create the default top page */
	$page = new page();

/* Create event-specific data (Re-populates part of $page) */
	$event = new event();
	if ($event->gid == 0)
		print "No such genre/event";
	$page->htmltitle = $event->title;
	$page->header = $event->title;
	$page->logo->add(img($event->logo,$event->title));
	$page->set_css($event->css);

/* Create information about the logged in user */
	$me = new myuser();

/* Add login/logout information to the second control box */
	$page->ctrl2->add($me);

/* Get the content of the currently selected page and add it to $page */
	$maincontent =& new content();
	if(!isset($maincontent->content))
	{
		$page->warn->add(new content("ErrorPageNotFound"));
		if(strstr($me->permission($maincontent->permission),"w"))
			$page->warn->add(new content("ErrorPageNotFoundAdmin"));
	} 
	$page->content->add($maincontent);


/* Populate the menu */
	$menu = new menuboks($event->title);
	$menu->add(new content($event->gname . "Menu"));
	$page->ctrl1->add(&$menu);
	$menucrew = new menuboks($event->title);
	$menucrew->add(new content($event->gname . "CrewMenu"));
	$page->ctrl1->add(&$menucrew);

/* Handle actions */
	if (isset($execaction[$session->action]))
		$execaction[$session->action]->actioncb($session->action);

/* Enviromental classes too small for their own file
 */
class session
{
	var $action;
	var $page;
	function session()
	{
		session_start();
		header("Cache-control: private");
		$this->action = $_REQUEST['action'] ? $_REQUEST['action'] : $_SESSION['action'];
		$this->page = $_REQUEST['page'] ? $_REQUEST['page'] : $_SESSION['page'];
	}
}
?>
