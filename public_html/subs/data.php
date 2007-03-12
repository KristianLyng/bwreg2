<?

class content
{
	var $content;
	var $version;
	var $gid;
	var $title;
	function content($contentid = false)
	{
		global $db;
		global $event;
		$query = "SELECT content,version,title,gid FROM content WHERE gid='";
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
	}
	function sqlcb($row)
	{
		$this->content = $row['content'];
		$this->version = $row['version'];
		$this->gid = $row['gid'];
		$this->title = $row['title'];
	}

	function get()
	{
		global $wiki;
		return $wiki->transform($this->content);
	}
}
?>
