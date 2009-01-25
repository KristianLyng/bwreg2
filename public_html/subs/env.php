<?
/* BWReg2 enviromental setup 
 *  
 * Copyright (C) 2007 Kristian Lyngstol <kristian@bohemians.org>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

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
require_once("subs/content.php");
require_once("subs/compotemp.php");
require_once("Text/Wiki.php");
require_once("subs/news.php");
require_once("subs/ticket.php");
global $base;
$base = "/template.php";
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

function next_action($action, &$object)
{
	if(is_object($object))
		$object->actioncb($action);
}

/* Make sure we render and update action when the page is done */
	register_shutdown_function(down);
	if ($_REQUEST['action'] == "RssNews")
		header("Content-type: application/RSS+xml");

/* Set up the wiki-object */
	$wiki =& new Text_Wiki();
	$wiki->setRenderConf('xhtml', 'wikilink', 'view_url', 
						 $base . '/');
	$wiki->setRenderConf('xhtml', 'wikilink', 'new_url', 
						 $base . '/');
	$wiki->setRenderConf('xhtml', 'wikilink', 'pages',null);
	$sites = array(
		"news" => $base . '/News?action=ViewNews&news=%s', 
		"force" => $base . '/%s', 
		"version" => $base . '?action=ContentGetVersion&version=%s', 
		"diff" => $base . '?action=ContentDiff&version=%s', 
		"action" => $_SERVER['PHP_SELF'] . '?action=%s', 
		"file" => '', // FIXME: doesn't really work because / gets encoded.
		"user" => $base . '/Userinfo?action=UserGetInfo&user=%s');
	$wiki->setFormatConf('Xhtml',array('translate'=>HTML_SPECIALCHARS)); 
	$wiki->setRenderConf('xhtml', 'interwiki','sites', $sites);
	$wiki->setRenderConf('xhtml', 'interwiki','target', null);
	$wiki->setRenderConf('xhtml', 'url','target', null);

// Normal exceptions until $page is up.
try 
{
/* Set up the basic enviroment */
	$session = new session();
	$config = new config();
	$db = new database();
	$compo = new CompoTemp();

/* Possibly load plugins (Nonfunctional at themoment */
	$plugins = new plugins();

/* Create the default top page */
	$page = new page();
} catch (Exception $e)
{
    print "En fatal feil oppstod: " . $e->getMessage();
}

try
{
/* Create information about the logged in user */
	$me = new myuser();

/* Add login/logout information to the second control box */
	$page->ctrl2->add($me);
	
/* Create event-specific data (Re-populates part of $page) */
	$event = new event();
	$page->htmltitle = $event->title;
	$page->header = $event->title;
	$page->logo->add(img($event->logo,$event->title));
	$page->set_css($event->css);

/* Get the content of the currently selected page and add it to $page */
	$maincontent =& new content();
	if(!isset($maincontent->content))
	{
		$page->warn->add(new content("/Error/PageNotFound"));
		if(me_perm($maincontent->permission,"w"))
			$page->warn->add(new content("/Error/PageNotFoundAdmin"));
	} 
	$page->content->add($maincontent);

/* Ticket handeling */
	$ticket = new Ticket_System($event);

/* Populate the menu */
	$menu = new menuboks($event->title);
	$menu->add(new content("/" . $event->gname . "Menu"));
	$page->ctrl1->add(&$menu);
	$menucrew = new menuboks("/" . $event->title);
	$menucrew->add(new content("/" . $event->gname . "CrewMenu"));
	$page->ctrl1->add(&$menucrew);
	
	$act = $maincontent->get_keyword("ACTION");
	if ($act != false && !isset($session->action))
			$session->action = $act;

	$page->ctrl2->add($ticket);

/* Set up news handeling */
	global $news;
	$news = new news();
	$page->content->add(&$news);

/* Handle actions */
	if (isset($execaction[$session->action]))
		$execaction[$session->action]->actioncb($session->action);
	if (isset($_REQUEST['refreshme']))
		$page->setrefresh($page->url() . '?refreshme=' . $_REQUEST['refreshme'],$_REQUEST['refreshme']);
}

catch (Error $e)
{
    $page->warn->add($e);
}
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
