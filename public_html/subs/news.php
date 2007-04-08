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
		$this->action['RssNews'] =& add_action("RssNews",&$news);
		$this->action['EditNews'] =& add_action("EditNews",&$news);
		$this->action['EditNewsSave'] =& add_action("EditNewsSave",&$news);
		$this->action['NewsDelete'] =& add_action("NewsDelete",&$news);
		$this->action['NewsDeleteVerified'] =& add_action("NewsDeleteVerified",&$news);
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
		global $page;
		if ($action == "ViewNews")
		{
			if (isset($_REQUEST['news']))
				$this->content = new onenews($_REQUEST['news']);
			else if (isset($_REQUEST['sname']))
				$this->content = new newslist($_REQUEST['sname']);
			else
				$this->content = new newslist(false);
		} else if ($action == "EditNews") {
			$this->content = new newsedit();
		} else if ($action == "EditNewsSave") {
			$this->content = new newsedit(true);
		} else if ($action == "NewsDelete")  {
			$this->content = new newsdelete();
		} else if ($action == "NewsDeleteVerified")  {
			$this->content = new newsdelete(true);
		} else if ($action == "RssNews") {
			if ($_REQUEST['sname'])
				$this->content = new newslist($_REQUEST['sname'], true);
			else
				$this->content = new newslist(false,true);
			$page->rss = $this->content;
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
		$query .= $db->escape($this->sname) . "' ORDER BY news.date DESC LIMIT " . $this->display . ";";
		$db->query($query,&$this);
	}
	
	function sqlcb($row)
	{
		global $wiki;
		global $page;
		$user = new userinfo($row);
		$h1 = $row['title'];
		$h2 = new box();
		if ($this->boss) $h2->add($this->edit_box($row['identifier'])); //fixme
		$h2->add(htlink($page->url() . "?action=RssNews",str("RSS"),"rsslink"));
		$h2->add(str(" " . $row['date']));
		$drop = new dropdown("Skrevet av: " . $user->get_name());
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
		$box->add(htlink($page->url() . "?action=EditNews&amp;page=NewsEditor&news=" . $newsname,str("Editer")));
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
	function find($sname)
	{
		global $db;
		global $event;
		$query = "SELECT permission,sname,heading,description FROM news_categories WHERE gid = '";
		$query .= $db->escape($event->gid) . "' AND sname = '";
		$query .= $db->escape($sname) . "';";
		$row = $db->query($query);
		if (!$row) 
			return false;
		else 
			$this->newscategory($row);
		return true;
	}
	function get()
	{
		global $page;
		if (!isset($this->sname))
			return;
		$link = htlink($page->url() . "?action=ViewNews&amp;page=News&amp;sname=" . $this->sname,str($this->heading));
		return $link->get();
	}
}
class newscategorylist
{
	var $list;
	function newscategorylist($right = "r")
	{
		global $event;
		global $db;
		$this->right = $right;
		$query = "SELECT permission,sname,heading,description FROM news_categories WHERE ";
		$query .= "gid = '" . $db->escape($event->gid) . "';";
		$this->list = array();
		$db->query($query,&$this);
		return $this;
	}

	function sqlcb($row)
	{
		global $event;
		if(me_perm($row['permission'],$this->right,$event->gid))
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
	function newslist($sname = false,$rss = false)
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
		$query .= " ORDER BY date DESC;";
		$db->query($query,&$this);
		$this->rss = $rss;
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
		global $event;
		if ($this->rss)
		{
			$page->rss();
			$box = new box();
			$box->add(str("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"));
			$box->add(str("<rss version=\"2.0\">\n"));
			$box->add(str("<channel>\n<title>" . $event->title . " Nyheter</title>\n"));
			$box->add(str("<link>" . $page->url() . "</link>\n"));
			$box->add(str("<description>Nyheter fra og om " . $event->title . "</description>\n"));
			foreach ($this->list as $item)
			{
				$box->add(str("<item>\n"));
				$box->add(str("<title>" . $item->title . "</title>\n"));
				$box->add(str("<author>" . $item->user->get_name() . "&lt;" . $item->user->mail . "&gt;</author>"));
				$box->add(str("<link>" . $page->url() . "?action=ViewNews&amp;page=News&amp;news=" . $item->identifier . "</link>\n"));
				$box->add(str("<description>" . $item->title . "</description>\n"));
				$box->add(str("</item>\n"));
			}
			$box->add(str("</channel>\n"));
			$box->add(str("</rss>\n"));
			return $box->get();
		}
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
		$ctrl = new box();
		if (!is_array($this->category)) {
			$ctrl->add(htlink($page->url() . "?action=RssNews&amp;sname=" . $this->category->sname,str("RSS"),"rsslink"));
			if (me_perm($this->category->permission,"w",$event->gid))
			{
				$ctrl->add(htlink($page->url() . "?page=NewsEditor&amp;action=EditNews",str("Skriv en nyhet")));
				if (me_perm(null,"w",$event->gid))
					$ctrl->add(htlink($page->url() . "?page=NewsEditor&amp;action=DeleteNewsCategory",str("Slett denne nyhetskategorien")));
			}
		} else {
			$ctrl->add(htlink($page->url() . "?action=RssNews",str("RSS"),"rsslink"));
			foreach ($this->category as $cat)
			{
				if (me_perm($cat->permission,"w",$event->gid))
				{
					$ctrl->add(htlink($page->url() . "?page=NewsEditor&amp;action=EditNews",str("Skriv en nyhet")));
					break;
				}
			}
		}
		if (me_perm(null,"w",$event->gid))
			$ctrl->add(htlink($page->url() . "?page=NewsEditor&amp;action=MakeNewsCategory",str("Lag en nyhetskategori")));
		return $ctrl->get() .  $box->get();
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
		$query .= "news.title,news.content,news.identifier,news.date,users.* FROM news,news_categories,users ";
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
class newsedit extends news
{
	function newsedit($save = false)
	{
		global $me;
		if ($me->uid == 0)
			return; 
		if ($_REQUEST['sname'])
			$sname = $_REQUEST['sname'];
		else 
			$sname = false;
		$this->sname = $sname;
		if ($_REQUEST['news'])
		{
			$this->new = false;
			$this->id = $_REQUEST['news'];
		} else
			$this->new = true;
		
		if (!$this->new) 
			$this->get_content();
		if(!$save)
			$this->print_edit();
		else
		{
			$this->get_edit();
		}
	}
	
	function get_edit()
	{
		global $event;
		global $db;
		global $me;
		$brand = $_REQUEST['brandnew'];
		$sname = $_REQUEST['sname'];
		$title = $_REQUEST['title'];
		$content = $_REQUEST['content'];
		$eid = 0;
		if ($_REQUEST['eid'] == "true")
		{
			$eid = $event->eid;
		}
		if ($brand == "true")
			$id = $this->gen_id($title);
		else
			$id = $_REQUEST['identifier'];

		$cat = new newscategory();
		if(!$cat->find($sname))
		{
			return false;
		}
		if (!me_perm($cat->permission,"w",$event->gid))
			return false;
		if (strlen($title) < 4)
		{
			return false;
		}
		
		if ($brand == "true")
		{
			$query = "INSERT INTO news VALUES('$eid','";
			$query .= $db->escape($sname) . "','";
			$query .= $db->escape($title) . "','";
			$query .= $db->escape($me->uid) . "','";
			$query .= $db->escape($content) . "',NOW(),'";
			$query .= $db->escape($id) . "','";
			$query .= $db->escape($event->gid) . "');";
			if (!$db->insert($query))
				return false;
		} else {
			$query = "UPDATE news SET title = '";
			$query .= $db->escape($title) . "', content = '";
			$query .= $db->escape($content) . "', uid = '";
			$query .= $db->escape($me->uid) . "' WHERE gid = '";
			$query .= $db->escape($event->gid) . "' AND identifier = '";
			$query .= $db->escape($id) . "';";
			if(!$db->insert($query))
				return false;
		}
		global $page;
		$page->setrefresh($page->url() . "?action=ViewNews&amp;news=" . $id);
	}

	/* Create a new identifier based on the title */
	function gen_id($title)
	{
		global $event;
		global $db;
		$id = ucwords($title);
		$id = eregi_replace('[^a-z]', "", $id);
		$id = substr($id,0,95);
		$basequery = "SELECT * FROM news WHERE gid = '";
		$basequery .= $db->escape($event->gid) . "' AND identifier = '";
		$query = $basequery . $db->escape($id) . "';";
		$ret = $db->query($query);
		if (!$ret)
			return $id;
		$i = $db->escape($id);
		for ($num = 2; $num < 20; $num++)
		{
			$query = $basequery . $i . $num . "';";
			if(!$db->query($query))
			{
				return $i . $num;
			}
		}
		return false;
	}

	function print_edit()
	{
		$form = new form();
		$form->add(fhidden("EditNewsSave"));
		if (!$this->new)
		{
			$form->add(fhidden($this->sname,"sname"));
			$form->add(fhidden($this->id,"identifier"));
		}
		else
		{
			$b = new selectbox("sname");
			$list = new newscategorylist("w");
			foreach ($list->list as $item)
			{
				$b->add(foption($item->sname,$item->heading));
			}
			$form->add(str("Kategori:"));
			$form->add(htmlbr());
			$form->add($b);
			$form->add(htmlbr());
			$form->add(fhidden("true","brandnew")); 
		}
		$form->add(str("Overskrift:"));
		$form->add(htmlbr());
		$form->add(ftext("title",$this->title,80));
		$form->add(htmlbr());
		$form->add(str("Nyhetsinnhold:"));
		$form->add(htmlbr());
		$form->add(textarea("content", htmlentities($this->content, ENT_NOQUOTES, 'UTF-8')));
		$form->add(htmlbr());
		$form->add(fsubmit("Lagre"));
		$this->form = $form;
	}
	
	function get_content()
	{
		global $event;
		global $db;
		$query = "SELECT news_categories.permission,news_categories.sname,news_categories.heading,";
		$query .= "news.title,news.content,news.date,users.* FROM news,news_categories,users ";
		$query .= "WHERE news.sname = news_categories.sname AND users.uid = news.uid AND ";
		$query .= "news_categories.gid = '";
		$query .= $db->escape($event->gid) . "' AND news.identifier = '";
		$query .= $db->escape($this->id) . "';";
		$row = $db->query($query);
		if (!$row)
			return; //FIXME
		$this->permission = $row['permission'];
		if (!me_perm($this->permission,"r",$event->gid))
			return;  
			// You can SEE the edit box even if you can't use it. 
			// The idea beeing that you can read it anyway, and if you know how to edit
			// news, you might be interested in exactly how someone achived something,
			// not actually modifying it. Besides, we are displaying text here, not
			// actually writing. Keep in mind you still have to log in.
		$this->title = $row['title'];
		$this->content = $row['content'];
		$this->sname = $row['sname'];
		$this->date = $row['date'];
		$this->user = new userinfo($row);
		return true;
	}
	
	function get()
	{
		if (!is_object($this->form))
			return "";
		return $this->form->get();
	}
}

class newsdelete extends newsedit
{
	function newsdelete($verified = false)
	{
		global $me;
		global $page;
		if ($me->uid == 0)
			return;
		if (!isset($_REQUEST['news']))
			return;
		$this->id = $_REQUEST['news'];
		if (!$this->get_content())
			return;
		if (!isset($this->title))
			return;
		if (!me_perm($this->permission,"w",$event->gid))
			return;
		if (!$verified) {
			$this->set_verify_box();
		} else {
			$this->delete_it();
		}
	}
	function delete_it()
	{
		global $db;
		global $event;
		global $page;	
		$query = "DELETE FROM news WHERE gid = '";
		$query .= $db->escape($event->gid) . "' AND sname = '";
		$query .= $db->escape($this->sname) . "' AND identifier = '";
		$query .= $db->escape($this->id) . "' LIMIT 1;";

		if ($db->insert($query))
			$page->warn->add(h1("Nyhet slettet."));

	}
	function set_verify_box()
	{
		$b = new form();
		$table = new table(2,"newsdelete");
		$b->add(fhidden("NewsDeleteVerified"));
		$b->add(fhidden($this->id,"news"));
		$b->add(h1("Er du sikker på at du vil slette: "));
		$b->add(h2($this->title));
		$table->add(str("Skrevet av"));
		$u = new dropdown($this->user->get_name());
		$u->add($this->user);
		$table->add($u);
		$table->add(str("Dato"));
		$table->add(str($this->date));
		$table->add(str("Kategori"));

		$cat = new newscategory();
		$cat->find($this->sname);
		$table->add($cat);
		$table->add(str("ID"));
		$table->add(str($this->id));
		$table->add(fsubmit("JA! Slett denne for godt."),2);
		$b->add($table);
		$this->content = $b;
	}
	function get()
	{
		if (isset($this->content) && $this->content != null && is_object($this->content))
			return $this->content->get();
		return;
	}
}

?>
