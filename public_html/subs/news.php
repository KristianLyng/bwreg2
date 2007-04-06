<?

/* BWReg2 News classes
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

/* Main news class, an instance of this is created from env.php and only
 * one should be necesarry, but we support multiple just for fun. You never
 * know. 
 */
class news
{
	var $display; // How many news 
	var $sname;
	var $permission;
	var $news;
	/* If no sname is given, we assume we want to find it in the content 
	 * using $KEYWORD:value$, our value can either be a plain sname, or
	 * contain a comma and a max number of items to display. 
	 * If no max is defined, we default to showing 10 elements.
	 */
	function news($sname = false, $count = 10)
	{
		if (!$sname)
		{
			global $maincontent;
			$sname = $maincontent->get_keyword("NEWS");
			if (!$sname)
				return;
			$ret = split(",",$sname);
			$sname = $ret[0];
			if (isset($ret[1]))
				$count = $ret[1];
		}
		$this->sname = $sname;
		$this->display = $count;
		if(!$this->get_category())
			return;
		$this->get_news();
	}
	
	function get_news()
	{
		global $event;
		global $db;
		$this->boss =false;
		if (me_perm($this->permission,"w",$event->gid))
			$this->boss = true;
		$query = "SELECT users.uname,users.firstname,users.lastname,users.mail,users.birthyear,users.adress,users.phone,users.extra,users.private,";
		$query .= "news.title,news.date,news.content FROM users,news WHERE news.uid = users.uid AND (eid = '0' OR eid = '";
		$query .= $db->escape($event->eid) . "') AND sname = '";
		$query .= $db->escape($this->sname) . "' LIMIT " . $this->display . ";";
		$db->query($query,&$this);
		
	}
	function sqlcb($row)
	{
		global $wiki;
		$user = new userinfo($row);
		$h1 = $row['title'];
		$h2 = new box();
		if ($this->boss) $h2->add(str( "EditETC!")); //fixme
		$h2->add(str($row['date']));
		$drop = new dropdown($user->get_name());
		$drop->add($user);
		$h2->add($drop);
		$news = new htmlnews(str($h1),$h2);
		$news->add(str($wiki->transform($row['content'])));
		$this->news[] = $news;
	}
	function get_category()
	{
		global $db;
		global $event;
		$query = "SELECT permission, title, description FROM news_categories WHERE gid = '";
		$query .= $db->escape($event->gid) . "' AND sname = '";
		$query .= $db->escape($this->sname) . "';";
		$ret = $db->query($query);
		$this->permission = $ret['permission'];
		$this->title = $ret['title'];
		$this->description = $ret['description'];
		return me_perm($this->permission,"r",$event->gid);
	}
	function get()
	{
		$foo = "";
		foreach ($this->news as $news)
			$foo .= $news->get();
		return $foo;
	}
	function getraw()
	{
		return $this->get();
	}
}

?>
