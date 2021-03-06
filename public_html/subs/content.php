<?

/* BWReg2 Data classes
 * Copyright 2007-2009 Kristian Lyngstol <kristian@bohemians.org>
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
		$query = "SELECT title, version, modified,users.uname, users.firstname, users.lastname FROM content,users WHERE content.uid = users.uid AND ";
		$query .= "title = '" . $db->escape($title) . "' ORDER BY version DESC;";
		$db->query($query, &$this);
	}
	function sqlcb($row)
	{
		$myarray['version'] = $row['version'];
		$myarray['timestamp'] = $row['modified'];
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
	var $title;
	var $main;
	function content($title = false, $version = -1)
	{
		global $db;
		global $event;
		global $me;
		global $page;
		global $session;
		global $execaction;
		$this->renderme = true;
		$query = "SELECT content,version,title,permission FROM content WHERE title='";
		if (!$title)
		{
			if($_SERVER['PATH_INFO'])
			{
				$pg = $_SERVER['PATH_INFO'];
				$_SESSION['page'] = $pg;
			}
				
			if(isset($pg) && $pg != "/FrontPage" && $pg != "/")
			{
				$query .= $db->escape($pg);
				$this->title = $pg;
			}
			else
			{
				$query .= "/";
				$query .= $db->escape($event->title);
				$this->title = $event->title;
			}
			$this->main = true;
		} else {
			$query .= $db->escape($title);
			$this->title = $title;
			$this->main = false;
		}
		$query .= "'";
		if($version > 0)
			$query .= " AND version = '" . $db->escape("$version") . "'";
		$query .= " ORDER BY version DESC LIMIT 1;";
		$db->query($query,&$this);
		$this->lastedit =& add_action("EditContent",$this);
		$this->lastsave =& add_action("EditContentSave", $this);
		$this->lastdiff =& add_action("ContentDiff", $this);
		$this->lasthist =& add_action("ContentHistory", $this);
		$this->lastgetversion =& add_action("ContentGetVersion", $this);
		if($this->main)
		{
			if (perm_path($this->title,"w"))
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
			if (!perm_path($this->title,"w"))
				return "Error perm";

			$myversion = $db->escape($version);
			$myversion++;
			$query = "INSERT INTO content (version,title,content,permission,uid) VALUES('";
			$query .= $myversion . "','";
			$query .= $db->escape($title) . "','";
			$query .= $db->escape($content) . "','";
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
			if ($this->main && $this->title == $maincontent->title && me_perm($this->permission,"w"))
			{
				$oldcontent = new content($this->title, $_REQUEST['version']);
				$new = split("\n", $this->content);
				$old = split("\n", $oldcontent->content);
				$diff= new Text_Diff($old, $new);
				$opt = array("trailing_context_lines" => 0, "leading_context_lines" => 0);
				$renderer = new Text_Diff_Renderer($opt);
				$this->content = "<pre>" . htmlentities($renderer->render($diff)) . "</pre>";
				$this->renderme = false;
			}
			next_action($action,$this->lastdiff);
		} else if ($action == "ContentHistory") {
			if ($this->title == $maincontent->title && me_perm($this->permission,"w") && $this->main)
				$this->content = $this->gethistory();
			next_action($action,$this->lasthist);
		} else if ($action == "ContentGetVersion") {
			global $maincontent;
			if ($this->main && $this->title == $maincontent->title && me_perm($this->permission,"w"))
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
		$this->title = $row['title'];
		$this->permission = $row['permission'];
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
		$meny = new menu();
		$meny->add(htlink($page->url() . "?action=EditContent",
			str("Editer denne siden")));
		$meny->add(htlink($page->url() . "?action=ContentHistoryp", 
			str("Sidehistorie")));
		$b = new box();
		$b->add(str("<hr />"));
		$b->add($meny);
		return $b;
	}

	function editbox()
	{
		global $page;
		global $me;
		if (!me_perm($this->permission,"w"))
			return ;
		$box = new form();
		$box->add(str("<fieldset>"));
		$box->add(flegend("Innholdsredigering"));
		$box->add(textarea("content",htmlentities($this->content, ENT_NOQUOTES, 'UTF-8')));
		$permlist .= "<p> Resource (ACL): <select name=\"permission\">";
		$permlist .= $me->list_perms($this->permission,"w");
		$permlist .= "</select></p>";
		
		$box->add(str($permlist));
		$box->add(fhidden($this->version, "version"));
		$box->add(fhidden("EditContentSave"));
		$box->add(fhidden($this->title, "title"));
		$box->add(fsubmit("Save changes"));
		$box->add(str("</fieldset>"));
		return $box->get();
	}

	function get_keyword($keyword)
	{
		if (!$this->renderme || strstr($_REQUEST['action'],'Content'))
			return false;
		$this->content = preg_replace_callback(
			'|(.{0,2})\$' . $keyword . ':(.*)\$(.{0,2})|',
			create_function('$match', '
				global $maincontent;
				if ($match[1] == $match[3] && $match[1] == "``")
					return $match[0];
				$maincontent->replaceword = $match[2]; 
				return $match[1] . $match[3];
			'),
			$this->content);
		if (!isset($this->replaceword))
			return false;
		return $this->replaceword;
	}
	function get()
	{
		global $wiki;
		if($this->renderme)
		{
			return str_replace("%2F", "/", $wiki->transform($this->content));
		}
			return $this->content;
	}
}
?>
