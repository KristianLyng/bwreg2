<?
/* This file deals with getting event-specific information.
 * That means it's used a lot, for things like the header of
 * the page, and as a reference for content
 */

class genrectrl
{
	function genrectrl()
	{
		$this->lastshow = add_action("BWReg2ShowGenreAdmin",&$this);
		
	}
	function actioncb($action)
	{
		global $page;
		if($action == "BWReg2ShowGenreAdmin")
		{
			$page->content->addst("This is under construction");
			if (is_object($this->lastshow))
				$this->lastshow->actioncb($action);
		}
	}
	function get()
	{
		$link = htlink($page->url . "?page=BWReg2GenreAdmin&action=BWReg2ShowGenreAdmin", str("Genre Admin"));
		
		return "<br />" . $link->get();
	}
	function getraw()
	{

	}
}
class event
{
	var $gid;
	var $eid;
	var $title;
	var $description;
	var $logo;
	var $location;
	var $gname;
	var $price;
	var $payment;
	var $start;
	var $end;
	function event($gid = 0, $event = 0)
	{
		global $db;
		if($gid == null)
		{
			if ($_REQUEST['gid'])
			{
				$gid = $_REQUEST['gid'];
				$_SESSION['gid'] = $gid;
			}
			else if ($_SESSION['gid'])
				$gid = $_SESSION['gid'];
			else
				$gid = 1;
		}
		$query = "SELECT " . 
			"gid," . 
			"eid," .
			"gname," .
			"title," .
			"description," .
			"logo," . 
			"events.location," . 
			"name," . 
			"address, " .
			"directions," .
			"maplink," .
			"rows," . 
			"cols," .
			"seats," .
			"north," .
			"south," .
			"east," .
			"west, " .
			"css " . 
			"FROM events left join location on " . 
			" events.location = location.location WHERE gid = '";
		$query .= $db->escape($gid);
		$query .= "'";
		if($event != 0)
		{
			$query .= " and eid='";
			$query .= $db->escape($event);
			$query .= "'";
		}

		$query .= " ORDER BY eid DESC LIMIT 1;";
		if(!$db->query($query,&$this))
			return false;
		global $page;
		global $me;

		if(strstr($me->permission("BWReg2",0,0),"rwm"))
			$page->ctrl2->add(new genrectrl());
	}

	function sqlcb($row)
	{
		$this->gid = $row['gid'];
		$this->eid = $row['eid'];
		$this->title = $row['title'];
		$this->description = $row['description'];
		$this->logo = $row['logo'];
		$this->gname = $row['gname'];
		$this->location = new location($row);
		$this->css = $row['css'];
	}
}

/* Quite obviously, this is describes a location.
 * It is meant mainly for reading, after the event class has
 * created it.
 */
class location
{
	var $id;
	var $name;
	var $address;
	var $directions;
	var $maplink;
	var $rows;
	var $cols;
	var $seats;
	var $north;
	var $south;
	var $east;
	var $west;
	function location($row)
	{
		$this->id = $row['location'];
		if (!$this->id || $this->id == "")
		{	
			$this->id = false;
			return false;
		}
		$this->name = $row['name'];
		$this->address = $row['address'];
		$this->directions = $row['directions'];
		$this->maplink = $row['maplink'];
		$this->rows = $row['rows'];
		$this->cols = $row['cols'];
		$this->seats = $row['seats'];
		$this->north = $row['north'];
		$this->south = $row['south'];
		$this->east = $row['east'];
		$this->west = $row['west'];
	}
}

?>
