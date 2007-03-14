<?
require_once("subs/html.php");
class content
{
	var $content;
	var $version;
	var $gid;
	var $title;
	var $contentid;
	var $permission;
	var $read_permission;
	var $main;
	function content($contentid = false)
	{
		global $db;
		global $event;
		global $me;
		global $page;
		global $session;
		$this->permission = $event->gname . "ContentCreators";
		$query = "SELECT content,version,title,gid,permission,read_permission,contentid FROM content WHERE gid='";
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
		$query .= "' ORDER BY version DESC LIMIT 1;";
		$db->query($query,&$this);
		if ($this->read_permission != null && !strstr($me->permission($this->read_permission),"r"))
		{
			$this->content = null;
		}
		if ($session->action == "EditContentSave" && $_REQUEST['title'] == $this->title && $this->main == true)
			$page->setrefresh();
	}
	function sqlcb($row)
	{
		$this->content = $row['content'];
		$this->version = $row['version'];
		$this->gid = $row['gid'];
		$this->title = $row['title'];
		$this->permission = $row['permission'];
		$this->read_permission = $row['read_permission'];
		$this->contentid = $row['contentid'];
	}
	function &editlink() {
		global $page;
		$box = new infoboks();
		$box->add(htlink($page->url() ."?action=EditContent&page=" . $this->title,
			str("Editer denne siden")));
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
		$box->add(fhidden($this->version, "version"));
		$box->add(fhidden("EditContentSave"));
		$box->add(fhidden($this->title, "title"));
		$box->add(fsubmit("Save changes"));
		return $box->get();
	}
	function get()
	{
		global $wiki;
		global $page;
		global $me;
		global $session;
		global $db;
		global $event;
		global $maincontent;
		if ($session->action == "EditContent" && $session->page == $this->title && $this->main)
			return $this->editbox();
		else if ($session->action == "EditContentSave" && $_REQUEST['title'] == $this->title && $this->main == true)
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
			$query = "INSERT INTO content (gid,version,title,content,contentid,permission,read_permission,uid) VALUES('";
			$query .= $db->escape($event->gid);
			$query .= "','";
			$query .= $myversion . "','";
			$query .= $db->escape($title) . "','";
			$query .= $db->escape($content) . "','";
			$query .= $db->escape($this->contentid) . "','";
			if ($this->content)
				$query .= $db->escape($this->permission) . "',";
			else
				$query .= "2', "; // FIXME
			if ($this->read_permission)
				$query .= "'" . $db->escape($this->read_permission) . "'," . $me->uid . ");";
			else
				$query .= "NULL," . $me->uid . ");";
			$db->insert($query);
			$this->content = $content;
			$session->action = "";
		}
		return $wiki->transform(utf8_decode($this->content));
	}
}
?>
