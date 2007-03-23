<?

/* BWReg2 Data classes
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
/* This file deals with content. 
 * That includes news and general content. 
 * It will be one of the main work horses of bwreg2, and is critically
 * important.
 */

require_once("subs/html.php");
require_once("Text/Diff.php");
require_once("Text/Diff/Renderer.php");
class contenthistory 
{
	var $data;
	function contenthistory($title)
	{
		global $event;
		global $db;
		$query = "SELECT title, gid, version, modified,users.uname, users.firstname, users.lastname FROM content,users WHERE content.uid = users.uid AND ";
		$query .= "title = '" . $db->escape($title) . "' AND gid = '" . $event->gid . "' ORDER BY version DESC;";
		$db->query($query, &$this);
	}
	function sqlcb($row)
	{
		$myarray['version'] = $row['version'];
		$myarray['timestamp'] = $row['modified'];
		$myarray['gid'] = $row['gid'];
		$myarray['title'] = $row['title'];
		$myarray['author'] = $row['firstname'] . " " . $row['lastname'];
		$myarray['uname'] = $row['uname'];
		$this->data[] = $myarray;
	}
}

class content
{
	var $content;
	var $version;
	var $gid;
	var $title;
	var $contentid;
	var $permission;
	var $main;
	function content($contentid = false, $version = -1)
	{
		global $db;
		global $event;
		global $me;
		global $page;
		global $session;
		global $execaction;
		$this->renderme = true;
		$this->permission = $event->gname . "ContentCreators";
		$query = "SELECT content,version,title,gid,permission,contentid FROM content WHERE gid='";
		$query .= $db->escape($event->gid);
		if (!$contentid)
		{
			$query .= "' AND title = '";
			if($_REQUEST['page'])
			{
				$pg = $_REQUEST['page'];
				$_SESSION['page'] = $_REQUEST['page'];
			}
			else if ($_SESSION['page'])
				$pg = $_SESSION['page'];
				
			if(isset($pg) && $pg != "FrontPage")
			{
				$query .= $db->escape($pg);
				$this->title = $pg;
			}
			else
			{
				$query .= $db->escape($event->title);
				$this->title = $event->title;
			}
			$this->main = true;
		} else {
			$query .= "' AND title = '";
			$query .= $db->escape($contentid);
			$this->title = $contentid;
			$this->main = false;
		}
		$query .= "'";
		if($version > 0)
			$query .= " AND version = '" . $db->escape("$version") . "'";
		$query .= " ORDER BY version DESC LIMIT 1;";
		$db->query($query,&$this);
		if (!strstr($me->permission($this->permission),"r"))
		{
			$this->content = null;
		}
		$this->lastedit =& add_action("EditContent",$this);
		$this->lastsave =& add_action("EditContentSave", $this);
		$this->lastdiff =& add_action("ContentDiff", $this);
		$this->lasthist =& add_action("ContentHistory", $this);
		$this->lastgetversion =& add_action("ContentGetVersion", $this);
		if($this->main)
		{
			if(strstr($me->permission($this->permission),"w"))
			{
				$page->ctrl2->add($this->editlink());
			}
		}
	}
	function actioncb($action)
	{
		global $page;
		global $me;
		global $session;
		global $db;
		global $event;
		global $maincontent;
		if ($action == "EditContent" && $this->main)
		{
			$this->content = $this->editbox();
			$this->renderme = false;
		}
		else if ($action == "EditContentSave" && $_REQUEST['title'] == $this->title && $this->main == true)
		{
			$version = $_REQUEST['version'];
			$content = html_entity_decode($_REQUEST['content'], ENT_NOQUOTES);
			$title = $_REQUEST['title'];
			if ($version != $this->version)
			{
				return "Error!";
			}
			if (!strstr($me->permission($this->permission), "w"))
				return "Error perm";

			$myversion = $db->escape($version);
			$myversion++;
			$query = "INSERT INTO content (gid,version,title,content,contentid,permission,uid) VALUES('";
			$query .= $db->escape($event->gid);
			$query .= "','";
			$query .= $myversion . "','";
			$query .= $db->escape($title) . "','";
			$query .= $db->escape($content) . "','";
			$query .= $db->escape($this->contentid) . "','";
			$query .= $db->escape($_REQUEST['permission']) . "','";
			$query .= $db->escape($me->uid) . "'";
			$query .= ");";
			
			$db->insert($query);
			$this->content = $content;
			$session->action = "";
			$page->setrefresh();
		}
		if($action == "EditContent") {
			next_action($action,$this->lastedit);
		} else if ($action == "EditContentSave") {
			next_action($action,$this->lastsave);
		} else if ($action == "ContentDiff") {
			global $maincontent;
			if ($this->title == $maincontent->title && str($me->permission($this->permission),"w"))
			{
				$oldcontent = new content($this->title, $_REQUEST['version']);
				$new = split("\n", $this->content);
				$old = split("\n", $oldcontent->content);
				$diff= new Text_Diff($old, $new);
				$opt = array("trailing_context_lines" => 0, "leading_context_lines" => 0);
				$renderer = new Text_Diff_Renderer($opt);
				$this->content = "<pre>" . $renderer->render($diff) . "</pre>";
				$this->renderme = false;

				
			}
			next_action($action,$this->lastdiff);
		} else if ($action == "ContentHistory") {
			if ($this->title == $maincontent->title && str($me->permission($this->permission),"w") && $me->permission($this->permission) != "" && $this->main)
				$this->content = $this->gethistory();
			next_action($action,$this->lasthist);
		} else if ($action == "ContentGetVersion") {
			global $maincontent;
			if ($this->main && $this->title == $maincontent->title && str($me->permission($this->permission),"w"))
			{
				$newcontent = new content($this->title, $_REQUEST['version']);
				if (is_object($newcontent)) 
				{
					$this->content = $newcontent->content;
				}
			}
			next_action($action,$this->lastgetversion);
		}
	}

	function sqlcb($row)
	{
		$this->content = $row['content'];
		$this->version = $row['version'];
		$this->gid = $row['gid'];
		$this->title = $row['title'];
		$this->permission = $row['permission'];
		$this->contentid = $row['contentid'];
	}
	function gethistory()
	{
		$history = new contenthistory($this->title);
		$box = new box();
		global $page;
		$box->addst("<table><tr><td><b>Version</b></td><td><b>Timestamp</b></td><td><b>Author</b></td></tr>\n");
		foreach ($history->data as $data)
		{
			$box->addst("<tr><td>");
			$box->add(htlink($page->url() . "?action=ContentGetVersion&amp;version=" . $data['version'], str($data['version'])));
			$box->add(htlink($page->url() . "?action=ContentDiff&amp;version= "  . $data['version'], str("diff")));
			$box->addst("</td><td>");
			$box->add(str($data['timestamp']));
			$box->addst("</td><td>");
			$user = new user($data['uname']);
			$dropdown = new dropdown($user->get());
			$dropdown->add($user->userinfo);
			$box->addst($dropdown->get());
			$box->addst("</td></tr>");
		}
		$box->addst("</table>");
		$this->renderme = false;
		return $box->get();
	}
	function &editlink() {
		global $page;
		$box = new infoboks();
		$meny = new menu();
		$meny->add(htlink($page->url() . "?action=EditContent&amp;page=" . $this->title,
			str("Editer denne siden")));
		$meny->add(htlink($page->url() . "?action=ContentHistory&amp;page=" . $this->title, 
			str("Sidehistorie")));
		$box->add($meny);
		return $box;
	}

	function editbox()
	{
		global $page;
		global $me;
		if (!strstr($me->permission($this->permission),"w"))
			return ;
		$box = new form();
		$box->add(textarea("content",htmlentities($this->content, ENT_NOQUOTES, 'UTF-8')));
		$permlist .= "<br /> Resource (ACL): <select name=\"permission\">";
		$permlist .= $me->list_perms($this->permission);
		$permlist .= "</select>";
		
		$box->add(str($permlist));
		$box->add(fhidden($this->version, "version"));
		$box->add(fhidden("EditContentSave"));
		$box->add(fhidden($this->title, "title"));
		$box->add(fsubmit("Save changes"));
		return $box->get();
	}
	function get()
	{
		global $wiki;
		if($this->renderme)
		{
			return $wiki->transform(utf8_decode($this->content));
		}
			return $this->content;
	}
}
?>
