<?
require_once("subs/html.php");
class content
{
	var $content;
	var $version;
	var $gid;
	var $title;
	var $permission;
	var $read_permission;
	function content($contentid = false)
	{
		global $db;
		global $event;
		global $me;
		global $page;
		$query = "SELECT content,version,title,gid,permission,read_permission FROM content WHERE gid='";
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
			}
			else
				$query .= $db->escape($event->title);

		} else {
			$query .= "' AND contentid = '";
			$query .= $db->escape($contentid);
		}
		$query .= "' ORDER BY version DESC LIMIT 1;";
		$db->query($query,&$this);
		if ($this->read_permission != null && !strstr($me->permission($this->permission),"r"))
		{
			$this->content = "+ Permission denied";
		}
	}
	function sqlcb($row)
	{
		$this->content = $row['content'];
		$this->version = $row['version'];
		$this->gid = $row['gid'];
		$this->title = $row['title'];
		$this->permission = $row['permission'];
		$this->read_permission = $row['read_permission'];
	}

	function get()
	{
		global $wiki;
		global $page;
		global $me;
		return $wiki->transform($this->content);
	}
}
?>
