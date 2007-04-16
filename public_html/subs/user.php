<?
/* BWReg2 user classes
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

/* This file deals with users and permissions for said users 
 * It is one of the most important files, and contains the classes for
 * improtant objects such as $me.
 */

/* Checks if the current user has $perm permission on $resource, or any
 * resource overriding it (ie: BWReg2 or gname)
 */
function me_perm($resource, $perm, $gid = null)
{
	global $me;
	global $event;
	if ($resource != null && strstr($me->permission($resource),$perm))
		return true;
	if (strstr($me->permission("BWReg2"),$perm))
		return true;
	if ($gid != null && $event->gid == $gid && strstr($me->permission($event->gname),$perm))
		return true;
	return false;
}
/* Userinfo object. Plugins get noticed when this is created and a get
 * is called. 
 */
class userinfo 
{
	var $firstname = "Anonymous";
	var $lastname = "";
	var $phone = "";
	var $mail = "";
	var $extra = "";
	var $adress = "";
	var $born = null;
	var $options;
	var $uname;
	var $pluginextra; // Plugins add userinfo stuff here.
	function userinfo($row = null)
	{
		global $plugins;
		$this->pluginextra = new box();
		for($tmp = 0; $tmp < $plugins->nUserinfo; $tmp++)
		{
			$plugins->userinfo[$tmp]->userinfo(&$this);
		}
		if ($row != null)
		{
			$this->firstname = $row['firstname'];
			$this->options = $row['private'];
			$this->lastname = $row['lastname'];
			$this->phone = $row['phone'];
			$this->mail = $row['mail'];
			$this->adress = $row['adress'];
			$this->born = str($row['birthyear']); //FIXME
			$this->uname = $row['uname'];
			$this->extra = $row['extra'];
		}
	}
	function get()
	{
		$box = $this->get_box();
		return $box->get();
	
	}
	function get_name()
	{
		global $me;
		global $event;
		$seeall = false;
		if ($me->uid != 0 && ($me->userinfo == $this || me_perm(null,"r",$event->gid)))
			$seeall = true;
		if (!strstr($this->options,"f") || $seeall)
			$name = $this->firstname;
		if (!strstr($this->options,"l") || $seeall)
			$name .= " " . $this->lastname;
		if (!isset($name))
			$name = "Anonym";
		return $name;
	}
	function get_box()
	{
		global $me;
		global $event;
		$box = new userinfoboks();
		$seeall = false;
		if ($me->uid != 0 && ($me->userinfo == $this || me_perm(null,"r",$event->gid)))
			$seeall = true;
		if (!strstr($this->options,"f") || $seeall)
			$name = $this->firstname;
		if (!strstr($this->options,"l") || $seeall)
			$name .= " " . $this->lastname;
		if (!isset($name))
			$name = "(Skjult)";
		if (!strstr($this->options,"m") || $seeall)
			$mail = $this->mail;
		else
			$mail = false;
		if ($mail)
			$box->add(h1(htlink("mailto:" . $mail, str($name))));
		else
			$box->add(h1(str($name)));
		if (!strstr($this->options,"u") || $seeall)
		{
			$link = htlink(uinfolink($this->uname),str($this->uname));
			$box->add(str("Brukernavn: "));
			$box->add($link);
			$box->add(htmlbr());
		}
		if (!strstr($this->options,"p") || $seeall)
		{
			$box->add(str("Telefonnummer: " . $this->phone));
			$box->add(htmlbr());
		}
		if (!strstr($this->options,"x") || $seeall)
		{
			$box->add(str("Ekstra: " . $this->extra));
			$box->add(htmlbr());
		}
		if (!strstr($this->options,"a") || $seeall)
		{
			$box->add(str("Adresse: " . $this->adress));
			$box->add(htmlbr());
		}
		if (!strstr($this->options,"b") || $seeall)
		{
			$born = $this->born->get();
			if($born != null && $born != 0 && $born != "0")
				$box->add(str("Født: " . $this->born->get()));
		}
		
		$box->add($this->pluginextra);
		return $box;
	}
}

class perm 
{
	var $gid;
	var $eid;
	var $resourceid;
	var $resource;
	var $permission;
	function perm($gid, $eid, $resourceid, $resource, $permission)
	{
		$this->gid = $gid;
		$this->eid = $eid;
		$this->resourceid = $resourceid;
		$this->resource = $resource;
		$this->permission = $permission;
	}
}
/* permissions class contains a list with the permissions a user has
 * on a named resource. It also contains a $keys array with a list of
 * all the named resources the user has access to in a more easily
 * read fashion (checking is done with $list, but listing of resources
 * is done with $keys. This mostly happens through the user object)
 */
class permissions 
{
	var $list;
	var $keys; 

	function permissions($uid)
	{
		global $db;
		if(!isset($uid) or $uid == "")
			$uid = 0;
		$query = "SELECT @super := COUNT(*) FROM permissions,users,group_members WHERE permissions.groupid = group_members.groupid AND group_members.uid = users.uid AND permissions.gid = 0 AND permissions.eid = 0 ";
		$query .= "AND users.uid = '";
		$query .= $db->escape($uid);
		$query .= "';";
		$db->query($query);
		$query = "SELECT " . 
			 "permissions.resource, " . 
			 "permissions.resource_name, " .
			 "IF(@super > 0, \"rwm\", permissions.permissions) as permissions, " .
			 "permissions.eid, " .
			 "permissions.gid " . 
			 "FROM permissions LEFT JOIN groups ON groups.groupid = permissions.groupid " .
			 "LEFT JOIN group_members ON group_members.groupid = groups.groupid " .
			 "WHERE group_members.level > 0 && group_members.uid = '";
		if (!is_int($uid))
			$query .= $db->escape($uid);
		else
			$query .= $uid;
		$query .= "' OR group_members.uid = 0 OR @super > 0;";
		$db->query($query,&$this);
	}
	function sqlcb($row)
	{
		$this->keys[$row['resource']] = $row['resource_name'];
		$this->list[] = new perm($row['gid'], $row['eid'], $row['resource'], $row['resource_name'], $row['permissions']);
	}

	function find_specific_resource($resource)
	{
		if (!isset($this->list))
			return null;
		$current = null;
		foreach ($this->list as $item)
		{
			if ($item->resource == $resource)
			{
				$current = $this->greater($item,$current);
			}
		}
		return $current;
	}
	function find($resource, $gid = null, $eid = null)
	{
		global $event;
		if ($gid == null)
			$gid = $event->gid;
		if ($eid == null)
			$eid = $event->eid;

		$current = null;
		if (!isset($this->list))
			return null;
		foreach ($this->list as $item)
		{
			if ($item->resource == $resource || $item->resourceid == $resource)
			{
				if ($item->gid == $gid || $item->gid == 0 && ($item->eid == 0 || $item->eid == $eid))
					$current = $this->greater($item,$current);
			}
		}
		if ($current == null)
		{
			return null;
		}
		
		return $current->permission;
	}
	function find_resource_rights($right)
	{	
		$return = array();
		if (!isset($this->list))
			return false;
		foreach ($this->list as $item)
			if (strstr($item->permission,$right))
			{
				$add = true;
				foreach ($return as $retitem)
					if ($retitem == $item->resource)
							$add = false;
				if($add)
					$return[] = $item->resource;
			}

		
		return $return;
	}
	function greater($perm1, $perm2)
	{
		if($perm2 == null)
			return $perm1;
		if(strstr($perm1->permission,"rwm") && !strstr($perm2->permission,"rwm"))
			return $perm1;
		if(strstr($perm2->permission,"rwm") && !strstr($perm1->permission,"rwm"))
			return $perm2;
		if($perm1->eid == 0 && $perm2->eid != 0)
			return $perm1;
		return $perm2;
	}
}

/* A generic user.
 * This gets all information about a user.
 */
class user extends box
{
	var $userinfo;
	var $uid;
	var $uname;
	var $debug = true;
	var $perms;
	var $lastnewform;
	var $lastuserstore;
	function user($token = false, $password = null)
	{
		$this->userinfo = new userinfo();
		global $plugins;
		if ($password != null)
			$this->login($token,$password);
		else if (is_string($token))
			$this->c_uname($token);
		else if (is_int($token))
			$this->c_uid($token);
		else
			$this->guest();
		$this->perms =& new permissions($this->uid);
		for($tmp = 0; $tmp < $plugins->nUser; $tmp++)
			$plugins->user[$tmp]->user(&$this);
		$this->lastnewform =& add_action("PrintNewUser", &$this);
		$this->lastuserstore =& add_action("NewUserStore", &$this);
	}
	function actioncb($action)
	{
		global $page;
		if($action == "PrintNewUser")
		{
			$my = new newuser();
			$page->content->add($my->set_form());
			next_action($action,$this->lastnewform);
		}
		else if ($action == "NewUserStore")
		{
			$my = new newuser();
			if (!$my->get_form())
			{
				$page->warn->add(h1("Registreringen feilet"));
				$page->warn->add(p($my->error));
				next_action($action, $this->lastuserstore);
				return;
			}
			$page->content->add(h1("Registreringen lyktes!"));
			$page->content->add(str("Ditt navn: " . $my->firstname));
			next_action($action, $this->lastuserstore);
			
		}
	}
	function permission($param, $eid = null, $gid = null)
	{
		$perm = $this->perms->find($param, "$gid", "$eid");
		if($perm != null)
			return $perm;
		return false;
	}
	function list_perms($gid, $checker, $perm = "r")
	{
		$array  = array();
		foreach ($this->perms->list as $value)
		{
			if($value->gid == $gid && !isset($array[$value->resource]) && strstr($value->permission,$perm))
			{
				$array[$value->resource] = true;
				$string .= "<option value=\"" . $value->resourceid . "\"";
				if ( $value->resourceid == $checker )
					$string .= " selected=\"selected\"";
				$string .= ">" . $value->resource . "</option>\n";
			}
		}
		return $string;
	}
	function login($user, $password)
	{
		global $db;
		$myuser = $db->escape($user);
		$mypass = $db->escapepass($password);
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra,private,css FROM users WHERE uname = '";
		$query .= $myuser;
		$query .= "' AND pass = ";
		$query .= $mypass;
		$query .= ";";
		$uid = $db->query($query,&$this);
		if (!$uid) {
			$this->uid = 0;
			$this->userinfo = new userinfo();
			return false;
		}
	}

	function guest()
	{
		$this->uid = false;
		$this->uname = false;
		$this->userinfo = new userinfo();
	}

	function c_uname($user)
	{
		global $db;
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra,private FROM users WHERE uname = '";
		$query .= $db->escape($user);
		$query .= "';";
		return $db->query($query, &$this);
	}
	function c_uid($uid)
	{
		global $db;
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra,private FROM users WHERE uid = '";
		$query .= $db->escape($uid);
		$query .= "';";
		return $db->query($query, $this);
	}
	function sqlcb($row)
	{
		$this->uid = $row['uid'];
		$this->uname = $row['uname'];
		$this->userinfo->userinfo($row);
		global $page;
		if ($row['css'] != null && $row['css'] != "")
			$page->set_css($row['css']);
		$this->userinfo->css = $row['css'];
		for($tmp = 0; $tmp < $this->nItems; $tmp++)
		{
			if(function_exists($this->items[$tmp]->sqlcb))
				$this->items[$tmp]->sqlcb(&$this);
		}
	}

	function get()
	{
		return $this->userinfo->firstname . " " . $this->userinfo->lastname;
	}

	function getname()
	{
		global $me;
		global $event;
		$seeall = false;
		if($me->uid == $this->uid || me_perm(null,"r",$event->gid))
			$seeall = true;
		$string = "";
		if (!strstr($this->userinfo->options,"f") || $seeall)
			$string = $this->userinfo->firstname . " ";
		if (!strstr($this->userinfo->options,"l") || $seeall)
			$string .= $this->userinfo->lastname;
		if ($string == "") 
			$string = "(Anonym)";
		return $string;
	}
}
class groupmember extends userinfo
{
	function groupmember($row)
	{
		parent::userinfo($row);
		$this->level = $row['level'];
		$this->role = $row['role'];
	}
	function get()
	{
		$box = $this->get_box();
		$box->add(htmlbr());
		$box->add(str("Group role: " . $this->role));
		$box->add(htmlbr());
		$box->add(str("Group level: " . $this->level));
		return $box->get();
	}
	
}

class group
{
	var $getit;
	function group($name="", $id=false, $gid="", $desc="", $level = false, $uname = false)
	{
		$this->getit = "name";
		$this->name = $name;
		$this->id = $id;
		$this->gid = $gid;
		$this->desc = $desc;
		$this->level = $level;
		$this->uname = $uname;
	}
	// TODO: Range checks.
	function get_members()
	{
		global $db;
		$this->members = array();
		$this->getit = "members";
		$query = "SELECT level, role, uname, firstname, lastname, phone, mail, birthyear, adress, extra,private FROM group_members,groups,users WHERE groups.groupid = group_members.groupid AND users.uid = group_members.uid AND groups.groupid = '";
		$query .= $db->escape($this->id) . "' AND gid = '";
		$query .= $db->escape($this->gid) . "';";
		$db->query($query,&$this);
	}
	function sqlcb($row)
	{
		$this->members[] = new groupmember($row);
	}
	function member_level($user,$level)
	{
		if ($user->uname == false || !isset($this->members))
			return false;
		foreach ($this->members as $mem)
			if ($mem->uname == $user->uname && $mem->level >= $level)
				return true;
		global $event;
		if (me_perm(null,"w",$event->gid))
			return true;
		return false;
	}
	function set_info_display()
	{
		global $me;
		global $page;
		global $event;
		$this->id = $_REQUEST['groupid'];
		if(!$this->fetch_old($this->id))
			return false;
		$this->get_members();
		if (!$this->member_level($me,"1") && preg_match("/[ld]/",$this->options))
			return false;
		$tab = new table(2);
		$tab->add(h1("Gruppeinformasjon"),2,"header");
		$tab->add(flabel("name","Gruppenavn"));
		$tab->add(p($this->name));
		$tab->add(flabel("desc","Gruppebeskrivelse"));
		$tab->add(p($this->desc));
		$tab->add(flabel("name","Påmelding"));
		if (strstr($this->options,'l'))
			$tab->add(p("Låst"));
		else if (strstr($this->options,'m'))
			$tab->add(p("Moderert"));
		else if (strstr($this->options,'o'))
			$tab->add(p("Åpen"));
		else if (strstr($this->options,'d'))
			$tab->add(p("Gruppen er slettet"));
		$tab->add(flabel("valg","Valg"));
		$b = new box();
		if ($this->get_opt("s"))
			$b->add(p("Gruppeadministrator kan reservere plasser for medlemmene"));
		if ($this->get_opt("t"))
			$b->add(p("Gruppeadministrator kan bestille billetter for medlemmene"));
		$tab->add($b);	
		
		$tab->add(flabel("compo","Compo"));
		$foo = $this->get_opt("c");
		if (!$foo)
			$tab->add(str("Ikke en konkuransegruppe"));
		else
			$tab->add(str("$foo mot $foo"));
		$page->content->add($tab);
		$this->set_form_members();
		
	}
	function get_opt($opt,$num = false)
	{
		if (!$num) {
			if (strstr($this->options,$opt))
				return true;
			return false;
		}
		if (!preg_match("/$opt" . '[0-9]/',$this->options,$match))
			return false;
		return preg_replace("/$opt/","",$match[0]);
	}
	function set_form()
	{
		global $event;
		global $me;
		if ($this->id)
		{
			$this->get_members();
			if (!$this->member_level($me,10))
				return false;
		}
			
		$tab = new table(2,"groupadmin");
		$tab->add(h1("Gruppeadministrering"),2,"header");
		$tab->add(flabel("groupname","Gruppenavn"));
		$tab->add(ftext("groupname",$this->name));
		$tab->add(flabel("groupdesc","Gruppebeskrivelse"));
		$tab->add(ftext("groupdesc",$this->desc));
		$tab->add(flabel("state","Påmelding"));
		$b = new selectbox("state");
		$tmp = strstr($this->options,"o");
		$b->add(foption("o","Åpen påmelding",$tmp));
		$tmp = strstr($this->options,"m");
		$b->add(foption("m","Moderert påmelding",$tmp));
		$tmp = strstr($this->options,"l");
		$b->add(foption("l","Ingen påmelding",$tmp));
		$tab->add($b);

		$tab->add(flabel("options","Gruppeadmin kan"));
		$bo = new box();
		if ($this->id && !me_perm(null,"w",$event->gid))
			$dis = true;
		else
			$dis = false;
		$tmp = strstr($this->options,"s");
		$bo->add(fcheck("options","s",$tmp,$dis));
		$bo->add(flabel("optionss","Reservere plasser for gruppemedlemmene"));
		$bo->add(htmlbr());
		$tmp = strstr($this->options,"t");
		$bo->add(fcheck("options","t",$tmp,$dis));
		$bo->add(flabel("optionst","Bestille plasser for gruppemedlemmene"));
		$bo->add(htmlbr());
		$tab->add($bo);
		
		$tab->add(flabel("componumber","Compo-gruppe"));
		$box = new selectbox("componumber");
		$tmp = strstr($this->options,"c");
		$box->add(foption("c0","Gruppa er ikke for compoer",!$tmp));
		$tmp = strstr($this->options,"c2");
		$box->add(foption("c2","2-on-2 gruppe",$tmp));
		$tmp = strstr($this->options,"c3");
		$box->add(foption("c3","3-on-3 gruppe",$tmp));
		$tmp = strstr($this->options,"c4");
		$box->add(foption("c4","4-on-4 gruppe",$tmp));
		$tmp = strstr($this->options,"c5");
		$box->add(foption("c5","5-on-5 gruppe",$tmp));
		$tab->add($box);
		if ($this->id)
			$tab->add(fsubmit("Oppdater gruppa"),2);
		else
			$tab->add(fsubmit("Opprett gruppa"),2);

		$form = new form();
		$form->add(fhidden("GroupAdmin"));
		if ($this->id) {
			$form->add(fhidden($this->id,"groupid"));
			$form->add(fhidden($this->gid,"ourgid"));
		} else {
			$form->add(fhidden($event->gid,"ourgid"));
		}
			
		$form->add($tab);
		global $page;
		$page->content->add($form);
		if ($this->id)
			$this->set_form_members();
	}
	function get_form()
	{
		global $event;
		global $me;
		if ($_REQUEST['groupid'])
		{ 
			$groupid = $_REQUEST['groupid'];
			$this->id = $groupid;
			if (!$this->fetch_old($groupid))
				return false;
			$this->get_members();
			if (!$this->member_level($me,10))
				return false;
		}
		$gid = $_REQUEST['ourgid'];
		$boss = me_perm(null,"w",$gid);
		
		$name = $_REQUEST['groupname'];
		$desc = $_REQUEST['groupdesc'];
		$state = $_REQUEST['state'];
		$options = $_REQUEST['options'];
		$componumber = $_REQUEST['componumber'];
		global $page;
		if (preg_match("/[^-æøå\w ]/",$name) || strlen($name) > 18) {
			$page->warn->add(p("Navnet er ikke gyldig"));
			return false;
		}
		$desc = htmlspecialchars($desc,ENT_NOQUOTES,'UTF-8');
		if (preg_match("/[^oml]/",$state) || strlen($state) != 1) {
			$page->warn->add(p("Påmeldingstypen er ikke gyldig"));
			return false;
		}
		$t = "";
		foreach ($options as $opt)
		{
			if ($opt != 's' && $opt != 't')
			{
				$page->warn->add(p("Valgene er ikke gyldig"));
				return false;
			}
			$t .= $opt;
		}
		$options = $t;
		if ($componumber == "c0") 
			$comp = "";
		else if (!preg_match("/c[0-9]/",$componumber))
		{
			$page->warn->add(p("Compovalget er ikke gyldig"));
			return false;
		} else
			$comp = $componumber;
		if ($this->id && !$boss) {
			$opt = preg_replace("/[^st]/","",$this->options);
			$opt .= $state . $comp;
		} else {
			$opt = $state . $options . $comp;
		}
		global $db;
		if (!$this->id || $this->name != $name)
		{
			$query = "SELECT * FROM groups WHERE group_name = '";
			$query .= $db->escape($name) . "' AND gid = '";
			$query .= $db->escape($gid) . "';";
			if ($db->query($query))
			{
				$page->warn->add(p("En gruppe med det navnet eksisterer alt"));
				return false;
			}
		}

		if ($this->id) {
			$query = "UPDATE groups SET group_name = '";
			$query .= $db->escape($name) . "', group_description = '";
			$query .= $db->escape($desc) . "', options = '";
			$query .= $db->escape($opt) . "' WHERE groupid = '";
			$query .= $db->escape($groupid) . "' AND gid = '";
			$query .= $db->escape($gid) . "' LIMIT 1;";
			$db->insert($query);
			$this->fetch_old($groupid);
		} else {
			$query = "INSERT INTO groups (gid,group_name,group_description,owner,options) VALUES('";
			$query .= $db->escape($gid) . "','";
			$query .= $db->escape($name) . "','" . $db->escape($desc) . "','";
			$query .= $db->escape($me->uid) . "','" . $db->escape($opt) . "');";
			$db->insert($query);
			$query = "SELECT groupid FROM groups WHERE group_name = '";
			$query .= $db->escape($name) . "' AND gid = '";
			$query .= $db->escape($gid) . "';";
			list($ret) = $db->query($query);
			if (!$ret)
				return false;
			$query = "INSERT INTO group_members VALUES('" . $db->escape($ret) . "','";
			$query .= $db->escape($me->uid) . "','10','Group Creator');";
			$db->insert($query);
			$this->fetch_old($ret);
		}
		$page->setrefresh($page->url() . "?action=ShowGroupAdmin&groupid=" . $this->id);
	}
	function set_form_members()
	{
		global $me;
		global $event;
		global $page;
		$boss = me_perm(null,"w",$event->gid);
		$set = false;
		$guest = false;
		if (!$this->member_level($me,1))
			return;
		if (!$this->member_level($me,10))
			$guest = true;

		$tab = new table(3);
		$tab->add(h1("Gruppemedlemmer"),3,"header");
		$tab->add(h1("Navn"));
		if ($guest) {
			$tab->add(h1("Nivå"),2);
		} else {
			$tab->add(h1("Nivå"));
			$tab->add(str(" "));
		}

		foreach ($this->members as $mem)
		{
			$set = true;
			$dropdown =& new dropdown($mem->get_name());
			$dropdown->addst($mem->get());
			$tab->add(&$dropdown);
			if (($mem->uname == $me->uname && !$boss) || $guest)
			{
				if ($mem->level == 0)
					$tab->add(str($mem->level . " (Søker på gruppe)"),2);
				else
					$tab->add(str($mem->level),2);
				continue;
			}
			$f = new form();
			$b =& new selectbox("level");
			for ($i = 0; $i < 11; $i++)
			{
				if ($i == 0) 
					$b->add(foption($i,$i . "(Søker)",$i == $mem->level));
				else
					$b->add(foption($i,$i,$i == $mem->level));
			}
			$f->add(&$b);
			$f->add(fhidden($mem->uname,"user"));
			$f->add(fhidden($this->id,"groupid"));
			$f->add(fhidden($this->gid,"ourgid"));
			$f->add(fhidden("GroupUserLevelChange"));
			$f->add(fsubmit("Endre"));
			$tab->add($f);
			$url = $page->url() . "?action=LeaveGroup&amp;ourgid=";
			$url .= $this->gid . "&amp;user=";
			$url .= $mem->uname . "&amp;group=";
			$url .= $this->id;
			$link = htlink($url,str("Fjern fra gruppe"));
			$tab->add($link);
		}
		if ($set)
			$page->content->add($tab);
	}

	function fetch_old($id)
	{
		global $db;
		$query = "SELECT groups.groupid,groups.gid,groups.group_name,groups.group_description,options FROM groups WHERE groupid = '";
		$query .= $db->escape($id) . "';";
		$row = $db->query($query);
		if (!$row)
			return false;
		$this->options = $row['options'];
		$this->group($row['group_name'],$row['groupid'],$row['gid'], $row['group_description']);
		return true;
	}
	function get()
	{
		if($this->getit == "name")
			return $this->name;
		else if ($this->getit == "members")
		{
			foreach ($this->members as $member)
			{
				$cap = htlink(uinfolink($member->uname),str($member->uname));
				$drop = new dropdown($cap->get());
				$drop->add($member);
				$string .= $drop->get();
			}
			return $string;
		}
	}
}
class groupresctrl extends group
{
	function groupresctrl($resource, $eid, $name, $id, $gid, $desc, $permissions)
	{
		$this->resource = $resource;
		$this->eid = $eid;
		$this->permissions = $permissions;
		parent::group($name,$id,$gid,$desc);
	}
	function get()
	{
		global $page;
		$url = $page->url() . "?action=ResourceRmGroup&amp;resource=";
		$url .= $this->resource . "&amp;ourgid=";
		$url .= $this->gid . "&amp;oureid=";
		$url .= $this->eid . "&amp;groupid=";
		$url .= $this->id;
		$text = $this->name . "  - " . $this->permissions . " ";
		$link = htlink($url,str("Remove"));
		return $text . $link->get();
	}
}

class grouplist
{
	var $list;
	function grouplist($gid = null)
	{
		global $db;
		if ($gid == null)
			return;
		$query = "SELECT groupid,gid,group_name,group_description FROM groups WHERE gid = '";
		$query .= $db->escape($gid) . "';";
		$db->query($query,&$this);
	}
	function sqlcb($row)
	{
		$this->list[] = new group($row['group_name'],$row['groupid'],$row['gid'], $row['group_description'],$row['level'],$this->uname);
	}
	function get()
	{
		return "Not implemented";
	}
}

class grouplistopen extends grouplist
{
	function grouplistopen($uname,$existing = null,$perm,$title)
	{
		global $db;
		global $event;
		$gid = $event->gid;
		$this->existing = $existing;
		$this->title = $title;
		parent::grouplist();
		$query = "SELECT groupid,gid,group_name,group_description FROM groups WHERE gid = '";
		$query .= $db->escape($gid) . "' AND options LIKE '%$perm%' AND group_name != 'All';";
		$this->uname = $uname;
		$db->query($query,&$this);
	}
	function get()
	{
		global $me;
		global $page;
		global $event;
		$tab = new table(2,"grouplist");
		$tab->add(h1($this->title),2,"header");
		$notblank = false;
		foreach ($this->list as $group)
		{
			$skip = false;
			if (is_array($this->existing->list)) {
				foreach ($this->existing->list as $mygroup) {
					if ($group->id == $mygroup->id)
						$skip = true;
				}
			}
			if ($skip)
				continue;
			$notblank = true;
			if (me_perm(null,"w",$group->gid)) {
				$top =& htlink($page->url() . "?page=GroupAdmin&amp;action=ShowGroupAdmin&amp;groupid=" . $group->id,str($group->name));
				$tab->add(&$top); 
			} else {
				$top =& htlink($page->url() . "?page=GroupInfo&amp;action=GroupInfoDisplay&amp;groupid=" . $group->id,str($group->name));
				$tab->add(&$top); 
			}
			$string = "";
			if ($me->uname == $this->uname || me_perm(null,"w",$group->gid))
			{
				$url = $page->url() . "?action=JoinGroup&amp;ourgid=";
				$url .= $group->gid . "&amp;user=";
				$url .= $this->uname . "&amp;group=";
				$url .= $group->id;
				$link = htlink($url,str("Bli med i gruppa"));
				$string = $link->get();
			}
			$tab->add(str($string),1,"actions");
		}
		if (!$notblank)
			return "";
		return $tab->get();
	}
}
class grouplistuser extends grouplist
{
	function grouplistuser($uname,$mod = false ,$title="")
	{
		global $db;
		if ($mod)
			$level = "=";
		else
			$level = ">";
		$query = "SELECT groups.groupid,groups.gid,groups.group_name,groups.group_description,group_members.level FROM groups,group_members,users WHERE groups.groupid = group_members.groupid AND users.uid = group_members.uid AND group_members.level $level 0 AND users.uname = '";
		$query .= $db->escape($uname) . "';";
		$this->uname = $uname;
		$this->title = $title;
		$db->query($query,&$this);
	}
	function get()
	{
		global $me;
		global $page;
		global $event;
		$notblank = false;
		$tab = new table(2,"grouplist");
		$tab->add(h1($this->title),2,"header");
		if (!is_array($this->list))
				return "";
		foreach ($this->list as $group)
		{
			$super = me_perm(null,"w",$group->gid);
			if (($group->level >= 10 && $group->uname == $me->uname) || $super) {
				$top =& htlink($page->url() . "?page=GroupAdmin&amp;action=ShowGroupAdmin&amp;groupid=" . $group->id,str($group->name));
				$tab->add(&$top);
			} else if (preg_match("/[^ld]/",$group->options)){
				$top =& htlink($page->url() . "?page=GroupInfo&amp;action=GroupInfoDisplay&amp;groupid=" . $group->id,str($group->name));
				$tab->add(&$top); 
			} else 
				$tab->add(str($group->name));
			$string = "";
			if ($me->uname == $this->uname || $super)
			{
				$url = $page->url() . "?action=LeaveGroup&amp;ourgid=";
				$url .= $group->gid . "&amp;user=";
				$url .= $this->uname . "&amp;group=";
				$url .= $group->id;
				$link = htlink($url,str("Forlat gruppa"));
				$string = $link->get();
			}
			$tab->add(str($string),1,"actions");
			$notblank = true;
		}
		if (!$notblank)
			return "";
		return $tab->get();
	}
}
class grouplistresadd extends grouplist
{
	function grouplistresadd($gid, $eid, $resource)
	{
		$this->resource = $resource;
		$this->gid = $gid;
		$this->eid = $eid;
		parent::grouplist($gid);
	}
	function get()
	{
		if (!isset($this->list))
			return "";
		$form = new form();
		$form->add(str("<div>"));
		$form->add(fhidden("ResourceAddGroup"));
		$form->add(fhidden($this->resource, "resource"));
		$form->add(fhidden($this->gid,"ourgid"));
		$form->add(fhidden($this->eid,"oureid"));
		$form->add(str("<select name=\"groupid\">\n"));
		foreach ($this->list as $group)
		{
			$string = "\t<option value=\"" . $group->id;
			$string .= "\">" . $group->name . "(";
			$string .= $group->desc . ")</option>\n";
			$form->add(str($string));
		}
		$form->add(str("</select>\n"));
		$form->add(str("<select name=\"permissions\">\n"));
		$form->add(str("<option value=\"r\">Read Only</option>\n"));
		$form->add(str("<option value=\"rw\">Read/Write</option>\n"));
		$form->add(str("<option value=\"rwm\">Read/Write/Modify</option>\n"));
		$form->add(str("</select>\n"));
		$form->add(fsubmit("Add"));
		$form->add(str("</div>"));
		return $form->get();
	}
}

/* Class for displaying and applying resource control changes */
class resourcectrl
{
	var $perm;
	var $list;
	function resourcectrl($resource)
	{
		global $me;
		global $db;
		$perm = $me->perms->find_specific_resource($resource);
		if(!strstr($perm->permission, "m"))
			return ;
		$this->perm = $perm;
		$query = "SELECT permissions.resource_name,permissions.gid,groups.group_name,groups.groupid,groups.group_description,permissions.eid,permissions.permissions FROM permissions,groups where permissions.groupid = groups.groupid AND ";
		$query .= "resource_name = '" . $db->escape($resource) . "' ";
		$query .= "AND permissions.gid = '" . $db->escape($perm->gid) . "' ";
		$query .= "AND permissions.eid = '" . $db->escape($perm->eid) . "';";
		$db->query($query,&$this);
		$this->resource = $resource;
	}
	function sqlcb($row)
	{
		$this->list[] = new groupresctrl($row['resource_name'], $row['eid'],$row['group_name'], $row['groupid'], $row['gid'], $row['group_description'],$row['permissions']);
	}
	function get()
	{
		if (!isset($this->perm))
			return "No such group or you don't have permission to modify it";
		$box = new box();
		$box->add(h1($this->perm->resource));
		$box->add(p("GID: " . $this->perm->gid . " EID: " . $this->perm->eid));
		$menu = new menu("Member groups");
		foreach ($this->list as $group)
			$menu->addst($group->get());
		$box->add($menu);
		$groupadd = new grouplistresadd($this->perm->gid, $this->perm->eid, $this->resource);
		$box->add($groupadd);

		return $box->get();
	}
}

/* Class for $me.
 * Contains the current user. Logs in if necesarry and provides
 * the login box etc.
 */
class myuser extends user
{
	var $failed = true; 
	function myuser()
	{	
		parent::user();
		global $page;
		if ($_REQUEST['action'] == 'Logout')
		{
			$page->ctrl2->add(str("Velkommen tilbake"));
			$this->logout();
		}
		if($_SESSION['uname'] && $_SESSION['pass'] ) 
			$this->login($_SESSION['uname'],$_SESSION['pass']);
		 else if ($_POST['action'] == 'Login' && $_POST['uname'] && $_POST['pass']) {
			$_SESSION['uname'] = $_POST['uname'];
			$_SESSION['pass'] = $_POST['pass'];
			$this->login($_POST['uname'],$_POST['pass']);
		} else
			$this->failed = false;
		if($this->uid == 0)
		{
			$this->logout();
		}
		$this->perms =& new permissions($this->uid);
		$this->find_resource_rights();
	}
	/* Finds resources this user can modify and add a link
	 * to the administration interface. Todo:
	 * Make real links. Make it usefull...
	 */
	function find_resource_rights()
	{
		$this->lastgetuserinfo =& add_action("UserGetInfo", &$this);
		$this->lastgetuserinfolist =& add_action("UserGetInfoList", &$this);
		$this->lastleavegroup =& add_action("LeaveGroup",&$this);
		$this->lastjoingroup =& add_action("JoinGroup",&$this);
		$this->lasteditinfo =& add_action("EditUserInfo",&$this);
		$this->lastcommitinfo =& add_action("CommitUserInfo",&$this);
		$this->lastcommitpass =& add_action("CommitPassChange",&$this);
		$this->lastresourcectrladdacl =& add_action("ResourceControlAddList",&$this);
		$this->lastshowgroupadmin =& add_action("ShowGroupAdmin",&$this);
		$this->lastgrouplevelchange =& add_action("GroupUserLevelChange",&$this);
		$this->lastgroupadmin =& add_action("GroupAdmin",&$this);
		$this->lastgroupinfodisplay =& add_action("GroupInfoDisplay",&$this);
		$resources = $this->perms->find_resource_rights("m");
		if ($resources == false)
			return;
		$this->lastresourcectrl = add_action("ResourceControl", &$this);
		$this->lastresourceadd = add_action("ResourceAddGroup",&$this);
		$this->lastresourcedel = add_action("ResourceRmGroup",&$this);
		global $page;
		$s = htlink($page->url() . "?page=ResourceControl&amp;action=ResourceControl",str("Rettighetskontroll"));
		$t = $s->get();
		$menu = new dropdown($t);
		foreach ($resources as $item)
		{
			$link = $page->url() . "?action=ResourceControl&amp;resource=";
			$link .= $item . "&amp;page=ResourceControl";
			$menu->add(htlink($link,str($item)));
		}
		$page->ctrl3->add($menu);
	}
	function handle_resource_add()
	{
		global $me;
		$resource = $_REQUEST['resource'];
		$groupid = $_REQUEST['groupid'];
		$permissions = $_REQUEST['permissions'];
		$eid = $_REQUEST['oureid'];
		$gid = $_REQUEST['ourgid'];
		if (!isset($resource) || !isset($groupid) || !isset($permissions))
			return;
		if (!strstr($me->permission($resource, $eid, $gid),"m"))
			return;
		$res = $me->perms->find_specific_resource($resource);
		if (!isset($res))
			return;
		global $db;
		global $page;
		$query = "INSERT INTO permissions VALUES('";
		$query .= $db->escape($gid) . "','";
		$query .= $db->escape($eid) . "','";
		$query .= $db->escape($res->resourceid) . "','";
		$query .= $db->escape($res->resource) . "','";
		$query .= $db->escape($permissions) . "','";
		$query .= $db->escape($groupid) . "');";
		$db->insert($query);
		$page->content->add(p("Adding $groupid to $resource with $permissions (gid: $gid eid: $eid) \n"));
	}
	
	/* This will list the information for a user. It will evaluate the 
	 * "private" field of a user record to decide what information will
	 * displayed. It also lists groups the user is in, and possibly a link
	 * to leave the group. If $me us a BWReg2 or genre superuser, he/she
	 * will have the same control as if he/she was actually logged in as $user.
	 */
	function handle_user_info($user = null)
	{
		global $page;
		global $me;
		global $event;
		if ($user == null)
			$user = $_REQUEST['user'];
		if ($user == $this->uname) {
			$caption = "du";
			$userinfo = $this->userinfo;
		} else { 
			$userob = new user($user);
			if ($userob->uid == 0)
			{
				$page->warn->add(new content("ErrorUserInfoNotFound"));
				return;
			}
			$userinfo = $userob->userinfo;
			$caption = $userinfo->uname;
		}
		$seeall = false;
		if ($me->uid != 0 && ($me->userinfo == $userinfo || me_perm(null,"r",$event->gid)))
			$seeall = true;
		$page->content->add($userinfo);
		if ($me->uid != 0 && ($me->userinfo == $userinfo || me_perm(null,"w")))
			$page->content->add(htlink($page->url() . "?page=UserinfoChange&amp;action=EditUserInfo&amp;user=$user",str("Endre brukerinformasjonen")));
		$tmp = "Modererte grupper $caption har søkt på";
		$modlist = new grouplistuser($user,true,$tmp);
		$tmp = "Grupper $caption er med i";
		$list = new grouplistuser($user,false,$tmp);
		if (!strstr($userinfo->options,"g") || $seeall) 
			$page->content->add($list);

		if ($this->userinfo == $userinfo || me_perm(null,"w",$event->gid))
		{
			$page->content->add($modlist);
			$list2 = new grouplistopen($user,$list,"o","Åpne grupper $caption kan meldes på");
			$page->content->add($list2);
			$modlist2 = $modlist;
			if (is_array($list->list))
				foreach ($list->list as $item)
					$modlist2->list[] = $item;
			$list3 = new grouplistopen($user,$modlist2,"m", "Modererte grupper $caption kan meldes på");
			$page->content->add($list3);
			if (me_perm(null,"w",$event->gid))
			{
				$list4 = new grouplistopen($user,$modlist2,"l","Låste grupper $caption kan valse rett inn i");
				$page->content->add($list4);
			}
		}	
	}

	function handle_resource_del()
	{
		global $me;
		$resource = $_REQUEST['resource'];
		$groupid = $_REQUEST['groupid'];
		$eid = $_REQUEST['oureid'];
		$gid = $_REQUEST['ourgid'];
		if (!isset($resource) || !isset($groupid) || !isset($eid))
			return;
		if (!strstr($me->permission($resource, $eid, $gid),"m"))
			return;
		$res = $me->perms->find_specific_resource($resource);
		if (!isset($res))
			return;
		global $db;
		global $page;
		$query = "SELECT COUNT(*) FROM permissions,users,group_members WHERE permissions.gid = '";
		$query .= $db->escape($gid) . "' AND permissions.eid = '";
		$query .= $db->escape($eid) . "' AND resource = '";
		$query .= $db->escape($res->resourceid) . "' AND resource_name = '";
		$query .= $db->escape($res->resource) . "' AND permissions.groupid != '";
		$query .= $db->escape($groupid) . "' AND permissions.groupid = group_members.groupid AND group_members.uid = users.uid AND ";
		$query .= "permissions.permissions = \"rwm\" AND users.uid = '" . $db->escape($me->uid) . "';";
		list($result) = $db->query($query);
		if (($result == 0 || $result == "0") && !strstr($me->permission("BWReg2"),"rwm"))
		{
			$page->content->add(p("Can't delete the last group granting you rwm permissions"));
			return ;
		}
		$query = "SELECT COUNT(*) FROM permissions WHERE permissions.gid = '";
		$query .= $db->escape($gid) . "' AND permissions.eid = '";
		$query .= $db->escape($eid) . "' AND resource = '";
		$query .= $db->escape($res->resourceid) . "' AND resource_name = '";
		$query .= $db->escape($res->resource) . "';"; 
		list($result) = $db->query($query);
		if ($result < 2) 
		{
			$page->content->add(p("Can't delete the last group..."));
			return;
		}
		$query = "DELETE FROM permissions WHERE gid = '";
		$query .= $db->escape($gid) . "' AND eid = '";
		$query .= $db->escape($eid) . "' AND resource = '";
		$query .= $db->escape($res->resourceid) . "' AND resource_name = '";
		$query .= $db->escape($res->resource) . "' AND groupid = '";
		$query .= $db->escape($groupid) . "' LIMIT 1;";
		$db->insert($query);
		$page->content->add(p("Deleted $groupid on $resource (gid: $gid eid: $eid) \n"));
	}

	function handle_join_group()
	{
		$gid = $_REQUEST['ourgid'];
		$user = $_REQUEST['user'];
		$groupid = $_REQUEST['group'];
		global $me;
		$group = new group();
		$group->fetch_old($groupid);
		if (!isset($group->id))
		{
			$page->warn->add(content("ErrorGroupNotFound"));
			return;
		}
		if ($user == $this->uname || me_perm(null,"w",$group->gid))
		{
			if (!isset($group->name))
				return;
			if ($user != $this->uname)
			{
				$userob = new user($user);
				if (!isset($userob->uid) || $userob->uid == 0)
					return;
				$uid = $userob->uid;
			} else 
				$uid = $this->uid;
			global $db;
			if (strstr($group->options,"o"))
				$level = 1;
			else if (strstr($group->options,"m"))
				$level = 0;
			else if (strstr($group->options,"l") && me_perm(null,"w",$group->gid))
				$level = "1";
			else
				return ;
			$query = "SELECT count(*) FROM group_members WHERE groupid = '";
			$query .= $db->escape($groupid) . "' AND uid = '";
			$query .= $db->escape($uid) . "';";
			list($res) = $db->query($query);
			if ($res && $res > 0)
			{
				global $page;
				$page->warn->add(str("Du er alt med i denne gruppa"));
				return;
			}
			$query = "INSERT INTO group_members VALUES('";
			$query .= $db->escape($groupid) . "','";
			$query .= $db->escape($uid) . "','$level','');";
			$db->insert($query);
		}
	}
	
	function handle_group_level_change()
	{
		$gid = $_REQUEST['ourgid'];
		$user = $_REQUEST['user'];
		$groupid = $_REQUEST['groupid'];
		$level = $_REQUEST['level'];
		global $me;
		$group = new group();
		if(!$group->fetch_old($groupid))
			return false; // FIXME
		$group->get_members();
		if (!is_numeric($level))
			return;
		if (!$group->member_level($me,$level+1))
			return;
		if (me_perm(null,"w",$group->gid) || $group->member_level($me,10))
		{
			if (!isset($group->name))
				return;
			$userob = new user($user);
			if (!isset($userob->uid) || $userob->uid == 0)
				return;
			$uid = $userob->uid;
			global $db;
			$query = "UPDATE group_members SET level = '" . $db->escape($level) . "' WHERE groupid = '";
			$query .= $db->escape($groupid) . "' AND uid = '";
			$query .= $db->escape($uid) . "' LIMIT 1;";
			$db->insert($query);
		}
	}
	function handle_leave_group()
	{
		$gid = $_REQUEST['ourgid'];
		$user = $_REQUEST['user'];
		$groupid = $_REQUEST['group'];
		global $me;
		$group = new group();
		$group->fetch_old($groupid);
		$group->get_members();
		if ($user == $this->uname || me_perm(null,"w",$group->gid) || $this->member_level($me,10))
		{
			if (!isset($group->name))
				return;
			if ($user != $this->uname)
			{
				$userob = new user($user);
				if (!isset($userob->uid) || $userob->uid == 0)
					return;
				$uid = $userob->uid;
			} else 
				$uid = $this->uid;
			global $db;
			$query = "DELETE FROM group_members WHERE groupid = '";
			$query .= $db->escape($groupid) . "' AND uid = '";
			$query .= $db->escape($uid) . "' LIMIT 1;";
			$db->insert($query);
		}
	}
	function handle_user_edit_show()
	{
		global $me;
		global $event;
		global $page;
		$user = $_REQUEST['user'];
		if (!isset($user) || $me->uid == 0 || ($me->uname != $user && !me_perm(null,"w")))
		{
			$page->warn->add(new content("ErrorPermissionDenied"));
			return;
		}
		if ($user == $this->uname) {
			$userinfo = $this->userinfo;
		} else { 
			$userob = new user($user);
			if ($userob->uid == 0)
			{
				$page->warn->add(new content("ErrorUserInfoNotFound"));
				return;
			}
			$userinfo = $userob->userinfo;
		}
		$upuser = new newuser(true);
		$upuser->userinfo = $userinfo;
		$page->content->add($upuser->set_form());
		$page->content->add($upuser->set_pass());
	}

	function handle_user_edit_commit()
	{
		global $me;
		global $event;
		global $page;
		$user = $_REQUEST['user'];
		if (!isset($user) || $me->uid == 0 || ($me->uname != $user && !me_perm(null,"w")))
		{
			$page->warn->add(new content("ErrorPermissionDenied"));
			return;
		}
		if ($user == $this->uname) {
			$userinfo = $this->userinfo;
		} else { 
			$userob = new user($user);
			if ($userob->uid == 0)
			{
				$page->warn->add(new content("ErrorUserInfoNotFound"));
				return;
			}
			$userinfo = $userob->userinfo;
		}
		$upuser = new newuser(true);
		if (!$upuser->get_form())
		{
			$page->warn->add(h1("Oppdateringen feilet"));
			$page->warn->add(p($upuser->error));
		} else {
			$page->setrefresh(uinfolink($user));
		}
		
	}
	function handle_user_pass_commit()
	{
		global $me;
		global $event;
		global $page;
		$user = $_REQUEST['user'];
		if (!isset($user) || $me->uid == 0 || ($me->uname != $user && !me_perm(null,"w")))
		{
			$page->warn->add(new content("ErrorPermissionDenied"));
			return;
		}
		if ($user == $this->uname) {
			$userinfo = $this->userinfo;
		} else { 
			$userob = new user($user);
			if ($userob->uid == 0)
			{
				$page->warn->add(new content("ErrorUserInfoNotFound"));
				return;
			}
			$userinfo = $userob->userinfo;
		}
		$upuser = new newuser(true);
		$oldpass = $_REQUEST['oldpass'];
		if ($oldpass != $_SESSION['pass'] && $user == $me->uname)
		{
			$page->warn->add(h1("Feil passord"));
			$page->warn->add(p("Det gammle passordet du oppga stemmet ikke."));
			return ;
		}
		$pass = $upuser->get_pass();
		if (!$pass)
		{
			$page->warn->add(h1("Passordet ble ikke oppdatert"));
			$page->warn->add(p($upuser->error));
			return;
		}
		global $db;
		$query = "SELECT COUNT(*) FROM users WHERE uname = '";
		$query .= $db->escape($user) . "';";
		list($res) = $db->query($query);
		if ($res != "1")
		{
			$page->warn->add(h1("Passordet ble ikke oppdatert"));
			$page->warn->add(p("Det oppstod en feil under oppdatering av passordet."));
			return;
		}
		$query = "UPDATE users SET pass = ";
		$query .= $db->escapepass($pass);
		$query .= " WHERE uname = '";
		$query .= $db->escape($user) . "';";
		$res = $db->insert($query);
		$page->warn->add(h1("Passordet er oppdatert"));
		$page->warn->add(p("Du må logge ut og inn igjenn..."));
	}
	function handle_user_info_list() 
	{
		global $page;
		$fname = $_REQUEST['fname'];
		$lname = $_REQUEST['lname'];
		$user = $_REQUEST['user'];
		if ($_REQUEST['any'][0] == "true")
			$any = true;
		else 
			$any = false;
		$b = new form();
		$b->add(fhidden("UserGetInfoList"));
		$b->add(fhidden("real","real"));
		$b->add(str("<table class=\"search\">"));
		$b->add(str("<tr><td>"));
		$b->add(str("Hvilken som helst"));
		$b->add(str("</td><td>"));
		$b->add(fcheck("any","true",$any));
		$b->add(str("</td></tr>"));
		$b->add(str("<tr><td>"));
		$b->add(str("Fornavn:"));
		$b->add(str("</td><td>"));
		$b->add(ftext("fname",$fname));
		$b->add(str("</td></tr>"));
		$b->add(str("<tr><td>"));
		$b->add(str("Etternavn: "));
		$b->add(str("</td><td>"));
		$b->add(ftext("lname",$lname));
		$b->add(str("</td></tr>"));
		$b->add(str("<tr><td>"));
		$b->add(str("Brukernavn: "));
		$b->add(str("</td><td>"));
		$b->add(ftext("user",$user));
		$b->add(str("<tr><td colspan=\"2\">"));
		$b->add(fsubmit("Søk"));
		$b->add(str("</td></tr>"));
		$b->add(str("</table>"));
		$page->content->add($b);
		if ($_REQUEST['real'] == 'real')
		{
			$f = new multiuserlist($any, $fname, $lname, $user);
			$page->content->add($f);
		}
	}
	
	function handle_resource_control_blank()
	{
		global $event;
		if (!me_perm(null,"m",$event->gid))
			return;
		$form = new form();
		$form->add(fhidden("ResourceControlAddList"));
		$tab = new table(2,"resourecontrol");
		$tab->add(str("Lag en ny tilgangskontrolliste"),2,"header");
		$tab->add(str("Navn"));
		$tab->add(ftext("aclname"));
		$tab->add(str("Startgruppe"));
		$box = new box();
		$box->add(str("<div><select name=\"groupid\">\n"));
		$grouplist = new grouplist($event->gid);
		foreach ($grouplist->list as $group)
			$box->add(str("<option value=\"" . $group->id . "\">\n\t" . $group->name . "\n</option>\n"));
		$box->add(str("</select></div>"));
		$tab->add($box);
		$tab->add(fsubmit("Legg til ACL"),2);
		$form->add($tab);
		global $page;
		$page->content->add($form);
	}
	function handle_resource_control_add_acl()
	{
		global $event;
		global $db;
		global $page;	
		if (!me_perm(null,"m",$event->gid))
			return;
		$resource = $_REQUEST['aclname'];
		$groupid = $_REQUEST['groupid'];
		if (strlen($resource) < 4)
		{
			$page->warn->add(str("Listenavn må ha minst 4 tegn."));
			return ;
		}
		if (!is_numeric($groupid))
		{
			$page->warn->add(str("Ugyldig gruppeid."));
			return;
		}
		$query = "SELECT max(resource) FROM permissions;";
		list($resid) = $db->query($query);
		
		$query = "SELECT resource FROM permissions WHERE gid = '";
		$query .= $db->escape($event->gid) . "' AND resource_name = '";
		$query .= $db->escape($resource) . "';";
		global $page;	
		if ($db->query($query))
		{
			$page->warn->add(str("Denne lista eksisterer alt."));
			return;
		}
		$query = "INSERT INTO permissions VALUES('";
		$query .= $db->escape($event->gid) . "','0',$resid,'";
		$query .= $db->escape($resource) . "','rwm','";
		$query .= $db->escape($groupid) . "');";
		$db->insert($query);
		$page->content->add(str("La til en ny liste gitt..."));
		
	}
	function actioncb($action)
	{
		if ($action == "ResourceControl")
		{
			global $page;
			if (isset($_REQUEST['resource']))
				$page->content->add(new resourcectrl($_REQUEST['resource']));
			else
				$this->handle_resource_control_blank();
			next_action($action,$this->lastresourcectrl);
		} else if ($action == "ResourceControlAddList") {
			$this->handle_resource_control_add_acl();
			next_action($action,$this->lastresourcectrladdacl);
		} else if ($action == "ResourceAddGroup") {
			$this->handle_resource_add();
			global $page;
			$page->content->add(new resourcectrl($_REQUEST['resource']));
			next_action($action,$this->lastresourceadd);
		} else if ($action == "ResourceRmGroup") {
			$this->handle_resource_del();
			global $page;
			$page->content->add(new resourcectrl($_REQUEST['resource']));
			next_action($action,$this->lastresourcedel);
		} else if ($action == "UserGetInfo") {
			$this->handle_user_info();
			next_action($action,$this->lastgetuserinfo);
		} else if ($action == "EditUserInfo") {
			$this->handle_user_edit_show();
			next_action($action,$this->lasteditinfo);
		} else if ($action == "CommitUserInfo") {
			$this->handle_user_edit_commit();
			next_action($action,$this->lastcommitinfo);
		} else if ($action == "CommitPassChange") {
			$this->handle_user_pass_commit();
			next_action($action,$this->lastcommitpass);
		} else if($action == "UserGetInfoList") {
			$this->handle_user_info_list();
			next_action($action,$this->lastgetuserinfolist);
		} else if ($action == "JoinGroup") {
			$this->handle_join_group();
			$this->handle_user_info();
			next_action($action,$this->lastjoingroup);
		} else if ($action == "LeaveGroup") {
			$this->handle_leave_group();
			$this->handle_user_info();
			next_action($action,$this->lastleavegroup);
		} else if ($action == "ShowGroupAdmin") {
			$g = new group();
			if ($_REQUEST['groupid'])
				$g->fetch_old($_REQUEST['groupid']);
			$g->set_form();
			next_action($action,$this->lastshowgroupadmin);
		} else if ($action == "GroupAdmin") {
			$g = new group();
			$g->get_form();
			$g->set_form();
			next_action($action,$this->lastgroupadmin);
		} else if ($action == "GroupUserLevelChange") {
			$this->handle_group_level_change();
			$g = new group();
			$g->fetch_old($_REQUEST['groupid']);
			$g->set_form();
			next_action($action,$this->lastgrouplevelchange);
		} else if ($action == "GroupInfoDisplay") {
			$g = new group();
			$g->set_info_display();
			next_action($action, $this->lastgroupinfodisplay);
		} else {
			parent::actioncb($action);
		}
	}
	function logout()
	{
		$_SESSION['uname'] = null;
		$_SESSION['pass'] = null;
		$this->guest();
	}
	function print_box()
	{
		global $page;
		global $event;
		$t = new table("2","loginboks");
		$form = new form();
		$t->add(str("Brukernavn"));
		$t->add(ftext("uname","",9));
		$t->add(str("Passord"));
		$t->add(fpass("pass",9,8));
		$t->add(str(""));
		$b= new box();
		$b->add(fsubmit("Login", "action"));
		$b->add(htlink( $page->url() . "?action=PrintNewUser&amp;page=" . $event->gname . "PrintNewUser",str("Ny bruker")));
		$t->add($b);
		$form->add($t);
		return $form->get();
	}
	function print_logout($box)
	{
		global $page;
		global $me;
		$url = $page->url();
		$url .= "?action=Logout";
		$object = htlink($url, str("Logg ut"));
		$url = $page->url();
		$url .= "?page=Userinfo&amp;action=UserGetInfo&amp;user=";
		$url .= $me->uname;
		$object2 = htlink($url, str("Brukerinformasjon"));
		$box->add($object);
		$box->add($object2);
		return ;
	}
	function get()
	{
		if($this->failed && $this->uid == 0) {
			return "Login failed" . $this->print_box();
		} else if ($this->uid > 0) {
			$box = new menu(parent::get());
			$this->print_logout(&$box);

			return $box->get();
		} else {
			return $this->print_box();
		}
	}
}

/* multiuser lets us search for and find multiple users.
 * It adds one user per result. It will be further extended
 * for modules that add other search-parameters and whatnot.
 */
class oneuser extends user 
{
	function oneuser($data) 
	{ 
		$this->userinfo = new userinfo();
		$this->sqlcb($data);
	}
}
class multiuser extends user 
{
	var $pre;
	function multiuser($any = true,$fname = "",$lname = "", $user = "")
	{
		global $db;
		parent::box();
		$set = false;
		$this->userinfo = new userinfo();
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,private,extra FROM users WHERE ";
		if ($fname != "")
		{
			$set = true;
			$query .= "firstname like '";
			$query .= $db->escape($fname);
			$query .= "%' ";
		}
		if ($lname != "")
		{
			if ($set)
			{
				if ($any)
					$query .= "or ";
				else
					$query .= "and ";
			}
			$query .= "lastname like '";
			$query .= $db->escape($lname);
			$query .= "%' ";
			$set = true;
		}
		if ($user != "")
		{
			if ($set)
			{
				if ($any)
					$query .= "or ";
				else
					$query .= "and ";
			}
			$query .= "uname like '";
			$query .= $db->escape($user);
			$query .= "%'";
			$set = true;
		}
		if (!$set)
			return;
		$query .= " LIMIT 50;";
		$db->query($query, &$this);
	}
	function sqlcb($row)
	{
		$this->add(new oneuser(&$row));
	}
	function get()
	{
		$string = "";
		for($tmp=0; $tmp < $this->nItems; $tmp++)
			$string .= $this->items[$tmp]->userinfo->get();
		return $string;
	}
}

class multiuserlist extends multiuser
{
	function multiuserlist($any = true, $fname = "", $lname = "", $user = "")
	{
		$this->userinfo = new userinfo();
		parent::multiuser($any,$fname,$lname,$user);
	}

	function get()
	{
		$box = new box();
		foreach($this->items as $item)
		{
			$dropdown = new dropdown($item->getname());
			$dropdown->add($item->userinfo);
			$box->add(&$dropdown);
			unset($dropdown);
		}
		return $box->get();
	}
}

class newuser
{
	var $userinfo;
	var $form;
	function newuser($update = false)
	{
		$this->update = $update;
		if (!$update)
			$this->userinfo->options = "pmagb";
	}
	function set_private_form()
	{
		$box = new box();
		$o = $this->userinfo->options;
		$val = strstr($o,"u");
		$box->add(fcheck("private","u",$val));
		$box->addst("Brukernavn");
		$box->add(htmlbr());
		$val = strstr($o,"f");
		$box->add(fcheck("private","f",$val));
		$box->addst("Fornavn");
		$box->add(htmlbr());
		$val = strstr($o,"l");
		$box->add(fcheck("private","l",$val));
		$box->addst("Etternavn");
		$box->add(htmlbr());
		$val = strstr($o,"m");
		$box->add(fcheck("private","m",$val));
		$box->addst("E-postadresse");
		$box->add(htmlbr());
		$val = strstr($o,"b");
		$box->add(fcheck("private","b",$val));
		$box->addst("Fødselsår");
		$box->add(htmlbr());
		$val = strstr($o,"a");
		$box->add(fcheck("private","a",$val));
		$box->addst("Adresse");
		$box->add(htmlbr());
		$val = strstr($o,"p");
		$box->add(fcheck("private","p",$val));
		$box->addst("Telefonnummer");
		$box->add(htmlbr());
		$val = strstr($o,"x");
		$box->add(fcheck("private","x",$val));
		$box->addst("Ekstrainformasjon");
		$box->add(htmlbr());
		$val = strstr($o,"g");
		$box->add(fcheck("private","g",$val));
		$box->addst("Gruppemedlemskap");
		$box->add(htmlbr());
		return $box;
	}
	function set_form()
	{
		$form = new table(2,"userform");
		$t = new form();
		$u = $this->userinfo;
		$form->add(str("Fornavn"));
		$form->add(ftext("firstname",$u->firstname));
		$form->add(str("Etternavn"));
		$form->add(ftext("lastname",$u->lastname));
		$form->add(str("Telefonnummer"));
		$form->add(ftext("phone",$u->phone));
		$form->add(str("E-post"));
		$form->add(ftext("mail",$u->mail));
		$form->add(str("Adresse"));
		$form->add(ftext("address",$u->adress));
		if (!$this->update)
		{
			$form->add(str("Brukernavn"));
			$form->add(ftext("user"));
			$p = $this->set_pass(true);
			foreach ($p->items as $ob)
				$form->add($ob);
		} else
			$t->add(fhidden($u->uname,"user"));
		$form->add(str("Tilleggsinformasjon"));
		$form->add(ftext("extra",$u->extra));
		$form->add(str("Fødselsår"));
		if (!isset($u->born))
			$b = "19";
		else 
			$b = $u->born->get();
		$form->add(ftext("born",$b,5));
		$form->add(str("Skjul *"));
		$form->add($this->set_private_form());
		if ($this->update)
		{
			$form->add(str("CSS fil**"));
			$form->add(ftext("css",$u->css));
			$t->add(fhidden("CommitUserInfo"));
		}
		else
			$t->add(fhidden("NewUserStore"));
		$form->add(fsubmit("Lagre"),2);
		$t->add($form);
		return $t;
	}

	function get_pass()
	{
		$pass = $_REQUEST['pass'];
		$confirm = $_REQUEST['pass_confirm'];
		if ($pass != $confirm)
		{
			$this->error = "Passordene du oppga var ikke like";
			return false;
		}
		if (strlen($pass) < 4 || strlen($pass) > 9)
		{
			$this->error = "Passordet må være minst 4 tegn og mindre enn 10";
			return false;
		}
		return $pass;
	}

	function set_pass($inline = false)
	{
		if (!$inline)
		{
			$form = new form(2,"UserForm");
			$t = new table(2);
			$form->add(fhidden($this->userinfo->uname,"user"));
			$form->add(fhidden("CommitPassChange"));
			$t->add(str("Bytt passord"),2);
			$t->add(str("Gammelt passord"));
			$t->add(fpass("oldpass",8));
			$t->add(str("Passord"));
			$t->add(fpass("pass",8));
			$t->add(str("Bekreft passord"));
			$t->add(fpass("pass_confirm",9));
			$t->add(fsubmit("Endre"),2);
			$form->add($t);
			return $t;
		} else {
			$form = new box();
			$form->add(str("Passord"));
			$form->add(fpass("pass",8));
			$form->add(str("Bekreft passord"));
			$form->add(fpass("pass_confirm",9));
			return $form;
		}
	}

	function get_form()
	{
		$firstname = $_REQUEST['firstname'];
		$lastname = $_REQUEST['lastname'];
		$phone = $_REQUEST['phone'];
		$mail = $_REQUEST['mail'];
		$address = $_REQUEST['address'];
		$extra = $_REQUEST['extra'];
		$user = $_REQUEST['user'];
		foreach ($_REQUEST['private'] as $pr)
		{
			$private .= $pr;
		}
		$born = $_REQUEST['born'];	
		$css = $_REQUEST['css'];
		global $db;
		if (!$this->update)
		{
			$pass = $this->get_pass();
			if (!$pass)
				return false;
		}
		if (!eregi("[uflmbapxg]{0,9}",$private))
		{
			$this->error = "Du har oppgitt ugyldig informasjon i \"skjult\" feltet. Dette er enten en programmeringsfeil eller så tukkler du...";
			return false;
		}
		if (!eregi("[0-9]{4}",$born) || !is_numeric($born))
		{
			$this->error = "Du har oppgitt et ugyldig fødselsår. Fødselsåret må bestå av 4 tall. F.eks 1991.";
			return false;
		}
		list($nborn) = sscanf($born,"%d");
		$date = getdate();
		if (($nborn < ($date['year']-100)) || ($nborn > ($date['year'] - 10)))
		{
			$this->error = "Du har oppgitt en ugyldig alder. Du må i ditt tiende leveår (og ikke eldre enn 100 år) for å registrere deg her. Kontakt administratorene om dette er et problem.";
			return false;
		}
		if (strlen($firstname) < 2 || strlen($lastname) < 2 || strlen($firstname) > 40 || strlen($lastname) > 40)
		{
			$this->error = "Du oppga ikke både fornavn og etternavn, eller du oppga for lang navnstreng";
			return false;
		}
		if (strlen($phone) < 8 || strlen($phone) > 15)
		{
			$this->error = "Du oppga ikke et gyldig telefonnummer";
			return false;
		}
		if (strlen($mail) < 7 || strstr($mail,"@") == false || strlen($mail) > 50)
		{
			$this->error = "Du oppga ikke en gyldig e-post adresse";
			return false;
		}
		if (strlen($address) < 5 || strlen($address) > 50)
		{
			$this->error = "Du oppga ikke en gyldig addresse";
			return false;
		}
		if (strlen($extra) > 250)
		{
			$this->error = "Du oppga for mye tilleggsinformasjon";
			return false;
		}
		if (!$this->update)
		{
			if (strlen($user) < 2 || strlen($user) > 10 || !ctype_alnum($user))
			{
				$this->error = "Brukernavn må være minst 2 og maks 10 tegn, og kun vanlige bokstaver og tall.";
				return false;
			}
		}
		$query = "SELECT uid FROM users WHERE uname = '";
		$query .= $db->escape($user);
		$query .= "';";
		list($res) = $db->query($query);
		if ($res && !$this->update)
		{
			$this->error = "Brukernavnet du oppga eksisterer allerede";
			return false;
		}
		if (!$res && $this->update)
		{
			$this->error = "Brukernavnet du oppga eksisterer ikke";
			return false;
		}
		$query = "SELECT uname FROM users WHERE firstname = '";
		$query .= $db->escape($firstname) . "' AND lastname = '";
		$query .= $db->escape($lastname) . "';";
		list($res) = $db->query($query);
		if ($res && $this->update && $res != $user)
		{
			$this->error = "Du forsøker å bytte navn, men det finns allerede en bruker ($res) med det navnet.";
			return false;
		}
		if ($res && !$this->update)
		{
			$this->error = "Du har allerede en bruker ($res) i systemet. ";
			return false;
		}
		$this->css = $css;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->phone = $phone;
		$this->mail = $mail;
		$this->address = $address;
		$this->pass = $pass;
		$this->extra = $extra;
		$this->options = $private;
		if (!$this->update) 
		{
			$query = "INSERT INTO users (uname,firstname,lastname,mail,adress,phone,extra,private,birthyear,pass)";
			$query .= " VALUES('";
			$query .= $db->escape($user) . "','";
			$query .= $db->escape($firstname) . "','";
			$query .= $db->escape($lastname) . "','";
			$query .= $db->escape($mail) . "','";
			$query .= $db->escape($address) . "','";
			$query .= $db->escape($phone) . "','";
			$query .= $db->escape($extra) . "','";
			$query .= $db->escape($private) . "','";
			$query .= $db->escape($nborn) . "',";
			$query .= $db->escapepass($pass) . ");";
			if (!$db->insert($query))
			{
				$this->error = "En ukjent feil oppstod når brukeren din ble opprettet. Dette skal ikke skje...";
				return false;
			}
		} else {
			//(uname,firstname,lastname,mail,adress,phone,extra,pass)";
			$query = "UPDATE users SET firstname = '"; 
			$query .= $db->escape($firstname) . "', lastname = '";
			$query .= $db->escape($lastname) . "', mail = '";
			$query .= $db->escape($mail) . "', adress = '";
			$query .= $db->escape($address) . "', private = '";
			$query .= $db->escape($private) . "', birthyear = '";
			$query .= $db->escape($nborn) . "', phone = '";
			$query .= $db->escape($phone) . "', extra = '";
			$query .= $db->escape($extra) . "', css = '";
			$query .= $db->escape($css) . "' WHERE uname = '";
			$query .= $db->escape($user) . "';";
			if (!$db->insert($query))
			{
				$this->error = "En ukjent feil oppstod når brukeren din ble opprettet. Dette skal ikke skje...";
				return false;
			}
		}
		return true;
	}
	function get()
	{
		$foo = $this->set_form();
		return $foo->get();
	}
	function sqlcb($row)
	{
	}
}

?>
