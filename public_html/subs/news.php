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
		global $news; // PHP+$this in constructor == copy! Yes, we do hate PHP OO.
		$this->action['ViewNews'] =& add_action("ViewNews",&$news);
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
	function actioncb($action)
	{
		if ($action == "ViewNews")
		{
			if (isset($_REQUEST['news']))
				$this->content = new onenews($_REQUEST['news']);
			else if (isset($_REQUEST['sname']))
				$this->content = new newslist($_REQUEST['sname']);
			else
				$this->content = new newslist(false);
		}
		next_action($action,$this->action[$action]);
	}
	function get_news()
	{
		global $event;
		global $db;
		$this->boss =false;
		if (me_perm($this->permission,"w",$event->gid))
			$this->boss = true;
		$query = "SELECT users.uname,users.firstname,users.lastname,users.mail,users.birthyear,users.adress,users.phone,users.extra,users.private,";
		$query .= "news.title,news.date,news.content,news.identifier FROM users,news WHERE news.uid = users.uid AND (eid = '0' OR eid = '";
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
		if ($this->boss) $h2->add($this->edit_box($row['identifier'])); //fixme
		$h2->add(str($row['date']));
		$drop = new dropdown($user->get_name());
		$drop->add($user);
		$h2->add($drop);
		$news = new htmlnews(str($h1),$h2);
		$news->add(str($wiki->transform($row['content'])));
		$this->news[] = $news;
	}
	
	function edit_box($newsname)
	{
		global $page;
		$box = new box();
		$box->add(str("[ "));
		$box->add(htlink($page->url() . "?action=NewsEdit&amp;page=NewsEditor&news=" . $newsname,str("Editer")));
		$box->add(str(' | '));
		$box->add(htlink($page->url() . "?action=NewsDelete&amp;page=NewsEditor&amp;news=" . $newsname,str("Slett")));
		$box->add(str(' ] '));
		return $box;
	}
	
	function get_category()
	{
		global $db;
		global $event;
		$query = "SELECT permission, sname, heading, description FROM news_categories WHERE gid = '";
		$query .= $db->escape($event->gid) . "' AND sname = '";
		$query .= $db->escape($this->sname) . "';";
		$ret = $db->query($query);
		$this->category = new newscategory($ret);
		$this->permission = $ret['permission'];
		return me_perm($this->category->permission,"r",$event->gid);
	}
	
	function get()
	{
		if ($this->content != null)
			return $this->content->get();
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

class newscategory
{
	function newscategory($row = false)
	{
		if ($row)
		{
			$this->permission = $row['permission'];
			$this->sname = $row['sname'];
			$this->heading = $row['heading'];
			$this->description = $row['description'];
		}
	}
}
class newscategorylist
{
	var $list;
	function newscategorylist()
	{
		global $event;
		global $db;
		$query = "SELECT permission,sname,heading,description FROM news_categories WHERE ";
		$query .= "gid = '" . $db->escape($event->gid) . "';";
		$this->list = array();
		$db->query($query,&$this);
		return $this;
	}

	function sqlcb($row)
	{
		global $event;
		if(me_perm($row['permission'],"r",$event->gid))
			$this->list[] = new newscategory($row);
	}
}
class newslistitem 
{
	function newslistitem($row)
	{
		$this->permission = $row['permission'];
		$this->sname = $row['sname'];
		$this->heading = $row['heading'];
		$this->title = $row['title'];
		$this->date = $row['date'];
		$this->user = new userinfo($row);
		$this->identifier = $row['identifier'];
	}
}
class newslist extends news
{
	function newslist($sname = false)
	{
		global $event;
		global $db;
		$this->sname = $sname;
		if ($sname)
		{
			if(!$this->get_category())
				return ;
		} else {
			$this->get_our_category();
		}
		$query = "SELECT news_categories.permission,news_categories.sname,news_categories.heading,";
		$query .= "news.title,news.identifier,news.date,users.* FROM news,news_categories,users ";
		$query .= "WHERE news.sname = news_categories.sname AND users.uid = news.uid AND ";
		$query .= "news_categories.gid = '";
		$query .= $db->escape($event->gid) . "' AND ";
		if (!is_array($this->category))
			$query .= "news.sname = '" . $db->escape($this->category->sname) . "'";
		else
		{
			$query .= "(";
			$set = false;
			foreach ($this->category as $cat)
			{
				if ($set)
					$query .= " OR ";
				$query .= "news.sname = '" . $db->escape($cat->sname) . "'";
				$set = true;
			}
			if (!$set)
				return;
			$query .= ")";
		}
		$query .= ";";
		$db->query($query,&$this);
	}
	function sqlcb($row)
	{
		$this->list[] = new newslistitem($row);
	}
	function get_our_category()
	{
		$list = new newscategorylist();
		$this->category = $list->list;
	}
	function get()
	{
		global $page;
		$box = new table(4,"newslist");
		$box->add(h1("Overskrift"),false,"newstitle");
		$box->add(h1("Kategori"),false,"newscategory");
		$box->add(h1("Dato"),false,"newsdate");
		$box->add(h1("Forfatter"),false,"newsauthor");
		foreach ($this->list as $item)
		{
			$link = htlink($page->url() . "?action=ViewNews&amp;page=News&amp;news=" . $item->identifier, str($item->title));
			$box->add($link,false,"newstitle");
			$link = htlink($page->url() . "?action=ViewNews&amp;page=News&amp;sname=" . $item->sname, str($item->heading));
			$box->add($link,false,"newscategory");
			$box->add(str($item->date),false,"newsdate");
			$f = new dropdown($item->user->get_name());
			$f->add($item->user);
			$box->add($f,false,"newsauthor");
		}
		return $box->get();
	}
}

class onenews extends news
{
	function onenews($id)
	{
		global $event;
		if (!$this->check_perm($id));
			return;
	}
	function check_perm($id)
	{
		global $event;
		global $db;
		global $wiki;
		global $page;
		$query = "SELECT news_categories.permission,news_categories.sname,news_categories.heading,";
		$query .= "news.title,news.content,news.date,users.* FROM news,news_categories,users ";
		$query .= "WHERE news.sname = news_categories.sname AND users.uid = news.uid AND ";
		$query .= "news_categories.gid = '";
		$query .= $db->escape($event->gid) . "' AND news.identifier = '";
		$query .= $db->escape($id) . "' LIMIT 1;";
		
		$row = $db->query($query);
		if (!$row)
			return; //fixme: Error message.
		if (!me_perm($row['permission'],"r",$event->gid))
			return; // fixme: ditto
		$user = new userinfo($row);
		$h1 = $row['title'];
		$h2 = new box();
		if(me_perm($row['permission'],"w",$event->gid))
			$h2->add($this->edit_box($row['identifier']));
		$h2->add(str($row['date']));
		$drop = new dropdown($user->get_name());
		$drop->add($user);
		$h2->add($drop);
		$h2->add(htlink($page->url() . "?action=ViewNews&amp;sname=" . $row['sname'],str($row['heading'])));
		$this->news = new htmlnews($h1,$h2);
		$this->news->add(str($wiki->transform($row['content'])));
	}
	function get()
	{
		if(is_object($this->news))
		return $this->news->get();
	}
}
?>
