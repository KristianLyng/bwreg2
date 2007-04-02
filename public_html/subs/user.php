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
	function userinfo()
	{
		global $plugins;
		$this->pluginextra = new box();
		for($tmp = 0; $tmp < $plugins->nUserinfo; $tmp++)
		{
			$plugins->userinfo[$tmp]->userinfo(&$this);
		}
	}
	function get()
	{
		$box = $this->get_box();
		return $box->get();
	
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
			$box->add(str("Brukernavn: " . $this->uname));
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
	function list_perms($gid, $checker)
	{
		$array  = array();
		foreach ($this->perms->list as $value)
		{
			if($value->gid == $gid && !isset($array[$value->resource]))
			{
				$array[$value->resource] = true;
				$string .= "<option value=\"" . $value->resourceid . "\"";
				if ( $value->resourceid == $checker )
					$string .= " selected ";
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
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra,private FROM users WHERE uname = '";
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
		$this->uid = $row[0];
		$this->uname = $row[1];
		$this->userinfo->firstname = $row[2];
		$this->userinfo->lastname = $row[3];
		$this->userinfo->mail = $row[4];
		$this->userinfo->born = new dateStuff($row[5]);
		$this->userinfo->adress = $row[6];
		$this->userinfo->phone = $row[7];
		$this->userinfo->extra = $row[8];
		$this->userinfo->options = $row['private'];
		$this->userinfo->uname = $row['uname'];	
		
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
			return $this->userinfo->firstname . " " . $this->userinfo->lastname;
	}
}
class groupmember extends userinfo
{
	function groupmember($row)
	{
		parent::userinfo();
		$this->firstname = $row['firstname'];
		$this->lastname = $row['lastname'];
		$this->phone = $row['phone'];
		$this->mail = $row['mail'];
		$this->extra = $row['extra'];
		$this->adress = $row['adress'];
		$this->private = $row['private'];
		$this->uname = $row['uname'];
		$this->born = new dateStuff($row['birthyear']);
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
	function group($name, $id, $gid, $desc)
	{
		$this->getit = "name";
		$this->name = $name;
		$this->id = $id;
		$this->gid = $gid;
		$this->desc = $desc;
	}
	// TODO: Range checks.
	function get_members()
	{
		global $db;
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
	function create()
	{
		global $me;
		if ($me->uid == 0)
			return false;
		global $db;
		$query = "SELECT COUNT(*) FROM groups WHERE gid = ';";
		$query .= $db->escape($this->gid) . "' AND groupname = '";
		$query .= $db->escape($this->name) . "';";
		$result = $db->query($query);
		if ($result > 0 or $result == NULL or $result == false)
			return false;
		$query = "INSERT INTO groups (gid,group_name,group_description,owner) VALUES('";
		$query .= $db->escape($this->gid) . "','";
		$query .= $db->escape($this->name) . "','";
		$query .= $db->escape($this->desc) . "','";
		$query .= $db->escape($me->uid) . "');";
		$db->insert($query);
		$query = "SELECT groupid FROM groups WHERE gid = ';";
		$query .= $db->escape($this->gid) . "' AND groupname = '";
		$query .= $db->escape($this->name) . "';";
		$result = $db->query($query);
		if ($result == false)
			return false;
		$query = "INSERT INTO group_members VALUES('$result','";
		$query .= $db->escape($me->uid) . "','10','Group creator');";
		$db->query($query);
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
class oldgroup extends group
{
	function oldgroup($id)
	{
		global $db;
		parent::group("","","","");
		$query = "SELECT groups.groupid,groups.gid,groups.group_name,groups.group_description,options FROM groups WHERE groupid = '";
		$query .= $db->escape($id) . "';";
		$db->query($query, &$this);
	}

	function sqlcb($row)
	{
		$this->options = $row['options'];
		parent::group($row['group_name'],$row['groupid'],$row['gid'], $row['group_description']);
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
		$this->list[] = new group($row['group_name'],$row['groupid'],$row['gid'], $row['group_description']);
	}
	function get()
	{
		return "Not implemented";
	}
}

class grouplistopen extends grouplist
{
	function grouplistopen($uname,$existing = null,$perm)
	{
		global $db;
		global $event;
		$gid = $event->gid;
		$this->existing = $existing;
		parent::grouplist();
		$query = "SELECT groupid,gid,group_name,group_description FROM groups WHERE gid = '";
		$query .= $db->escape($gid) . "' AND options LIKE '%$perm%';";
		$this->uname = $uname;
		$db->query($query,&$this);
	}
	function get()
	{
		global $me;
		global $page;
		global $event;
		foreach ($this->list as $group)
		{
			$skip = false;
			foreach ($this->existing->list as $mygroup)
			{
				if ($group->id == $mygroup->id)
					$skip = true;
			}
			if ($skip)
				continue;
			$string .= $group->name; 
			if ($me->uname == $this->uname || me_perm(null,"w",$group->gid))
			{
				$url = $page->url() . "?action=JoinGroup&amp;ourgid=";
				$url .= $group->gid . "&amp;user=";
				$url .= $this->uname . "&amp;group=";
				$url .= $group->id;
				$link = htlink($url,str("Bli med i gruppa"));
				$string .= "  - " . $link->get();
			}
			$string .=  " <br />";
			
		}
		return $string;
	}
}
class grouplistuser extends grouplist
{
	function grouplistuser($uname,$mod = false )
	{
		global $db;
		if ($mod)
			$level = "=";
		else
			$level = ">";
		$query = "SELECT groups.groupid,groups.gid,groups.group_name,groups.group_description FROM groups,group_members,users WHERE groups.groupid = group_members.groupid AND users.uid = group_members.uid AND group_members.level $level 0 AND users.uname = '";
		$query .= $db->escape($uname) . "';";
		$db->query($query,&$this);
		$this->uname = $uname;
	}
	function get()
	{
		global $me;
		global $page;
		global $event;
		foreach ($this->list as $group)
		{
			$string .= $group->name; 
			if ($me->uname == $this->uname || me_perm(null,"w",$group->gid))
			{
				$url = $page->url() . "?action=LeaveGroup&amp;ourgid=";
				$url .= $group->gid . "&amp;user=";
				$url .= $this->uname . "&amp;group=";
				$url .= $group->id;
				$link = htlink($url,str("Forlat gruppa"));
				$string .= "  - " . $link->get();
			}
			$string .=  " <br />";
			
		}
		return $string;
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
		$form->add(fhidden("ResourceAddGroup"));
		$form->add(fhidden($this->resource, "resource"));
		$form->add(fhidden($this->gid,"ourgid"));
		$form->add(fhidden($this->eid,"oureid"));
		$form->add(str("<select name=\"groupid\">"));
		foreach ($this->list as $group)
		{
			$string = "<option value=\"" . $group->id;
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
		$fo = new namedbox("class","form");
		$fo->add($groupadd);
		$box->add(p($groupadd->get()));

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
		$this->lastleavegroup =& add_action("LeaveGroup",&$this);
		$this->lastjoingroup =& add_action("JoinGroup",&$this);
		$this->lasteditinfo =& add_action("EditUserInfo",&$this);
		$this->lastcommitinfo =& add_action("CommitUserInfo",&$this);
		$this->lastcommitpass =& add_action("CommitPassChange",&$this);
		$resources = $this->perms->find_resource_rights("m");
		if ($resources == false)
			return;
		$this->lastresourcectrl = add_action("ResourceControl", &$this);
		$this->lastresourceadd = add_action("ResourceAddGroup",&$this);
		$this->lastresourcedel = add_action("ResourceRmGroup",&$this);
		global $page;
		$menu = new dropdown("Resource Control");
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
			$page->content->add(htlink($page->url() . "?page=Userinfo&amp;action=EditUserInfo&amp;user=$user",p("Endre brukerinformasjonen")));
		$modlist = new grouplistuser($user,true);
		$list = new grouplistuser($user,false);
		if (!strstr($userinfo->options,"g") || $seeall) 
		{
			if (isset($list->list)) {
				$page->content->add(h2("Grupper $caption er med i"));
				$page->content->add($list);
			}
		}
		if ($this->userinfo == $userinfo || me_perm(null,"w",$event->gid))
		{
			if (isset($modlist->list)) {
				$page->content->add(h2("Modererte grupper $caption har søkt på"));
				$page->content->add($modlist);
			}
			$list2 = new grouplistopen($user,$list,"o");
			if (isset($list2->list))
			{
				$page->content->add(h2("Åpne grupper $caption kan meldes på"));
				$page->content->add($list2);
			}
			$list3 = new grouplistopen($user,$modlist,"m");
			if (isset($list3->list))
			{
				$page->content->add(h2("Modererte grupper $caption kan meldes på"));
				$page->content->add($list3);
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
		$result = $db->query($query);
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
		$result = $db->query($query);
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
		$group = new oldgroup($groupid);
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
			else
				return ;
			$query = "SELECT count(*) FROM group_members WHERE groupid = '";
			$query .= $db->escape($groupid) . "' AND uid = '";
			$query .= $db->escape($uid) . "';";
			$res = $db->query($query);
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
	
	function handle_leave_group()
	{
		$gid = $_REQUEST['ourgid'];
		$user = $_REQUEST['user'];
		$groupid = $_REQUEST['group'];
		global $me;
		$group = new oldgroup($groupid);
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
		$res = $db->query($query);
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
	function actioncb($action)
	{
		if ($action == "ResourceControl")
		{
			global $page;
			$page->content->add(new resourcectrl($_REQUEST['resource']));
			next_action($action,$this->lastresourcectrl);
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
		} else if ($action == "JoinGroup") {
			$this->handle_join_group();
			$this->handle_user_info();
			next_action($action,$this->lastjoingroup);
		} else if ($action == "LeaveGroup") {
			$this->handle_leave_group();
			$this->handle_user_info();
			next_action($action,$this->lastleavegroup);
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
		$form = new form();
		$form->add(ftext("uname","uname",8));
		$form->add(fpass("pass",8));
		$form->add(fsubmit("Login", "action"));
		$form->add(htlink( $page->url() . "?action=PrintNewUser&amp;page=" . $event->gname . "PrintNewUser",str("Register")));
		return $form->get();
	}
	function print_logout($box)
	{
		global $page;
		global $me;
		$url = $page->url();
		$url .= "?action=Logout";
		$object = htlink($url, str("Logg av"));
		$url = $page->url();
		$url .= "?page=Userinfo&amp;action=UserGetInfo&amp;user=";
		$url .= $me->uname;
		$object2 = htlink($url, str("Brukerinfo"));
		$box->add($object);
		$box->add($object2);
		return ;
	}
	function get()
	{
		if($this->failed && $this->uid == 0) {
			return "Login failed" . $this->print_box();
		} else if ($this->uid > 0) {
			$box = new menu(h1(parent::get()));
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
	function multiuser($any,$fname,$lname)
	{
		global $db;
		$this->userinfo = new userinfo();
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE firstname like '";
		$query .= $db->escape($fname);
		if ($any)
				$query .= "%' or lastname like '";
		else
				$query .= "%' and lastname like '";
		$query .= $db->escape($lname);
		$query .= "%';";
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
	function multiuserlist($any, $fname, $lname)
	{
		$this->userinfo = new userinfo();
		$this->multiuser($any,$fname,$lname);
	}

	function get()
	{
		$box = new box();
		for($tmp = 0; $tmp < $this->nItems; $tmp++) 
		{
			$dropdown = new dropdown($this->items[$tmp]->getname());
			$dropdown->add($this->items[$tmp]->userinfo);
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
		$form = new form();
		$u = $this->userinfo;
		$form->add(str("<table style=\"UserForm\"><tr>"));
		$form->add(str("<td><label for=\"firstname\" title=\"Fornavn\">Fornavn</label></td><td>"));
		$form->add(ftext("firstname",$u->firstname));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Etternavn</td><td>"));
		$form->add(ftext("lastname",$u->lastname));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Telefonnummer</td><td>"));
		$form->add(ftext("phone",$u->phone));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("E-post</td><td>"));
		$form->add(ftext("mail",$u->mail));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Adresse</td><td>"));
		$form->add(ftext("address",$u->adress));
		$form->add(str("</td></tr><tr><td>\n"));
		if (!$this->update)
		{
			$form->add(str("Brukernavn</td><td>"));
			$form->add(ftext("user"));
			$form->add(str("</td></tr><tr><td>\n"));
			$form->add($this->set_pass(true));
		} else
			$form->add(fhidden($u->uname,"user"));
		$form->add(str("Tilleggsinformasjon</td><td>"));
		$form->add(ftext("extra",$u->extra));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Fødselsår</td><td>"));
		if (!isset($u->born))
			$b = "19";
		else 
			$b = $u->born->get();
		$form->add(ftext("born",$b,5));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Skjul *"));
		$form->add(str("</td><td>"));
		$form->add($this->set_private_form());
		$form->add(str("</td></tr><tr><td colspan=\"2\">\n"));
		if ($this->update)
			$form->add(fhidden("CommitUserInfo"));
		else
			$form->add(fhidden("NewUserStore"));
		$form->add(fsubmit("Lagre"));
		$form->add(str("</td></tr></table>"));
		return $form;
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
			$form = new form();
			$form->add(fhidden($this->userinfo->uname,"user"));
			$form->add(fhidden("CommitPassChange"));
			$form->add(str("<table style=\"UserForm\"><tr>"));
			$form->add(str("<td colspan=\"2\">\n"));
			$form->add(str("Bytt passord</td></tr><tr><td>\n"));
			$form->add(str("Gammelt passord</td><td>"));
			$form->add(fpass("oldpass",8));
			$form->add(str("</td></tr><tr><td>\n"));
		} else
			$form = new box();
		$form->add(str("Passord</td><td>"));
		$form->add(fpass("pass",8));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Bekreft passord</td><td>"));
		$form->add(fpass("pass_confirm",9));
		$form->add(str("</td></tr><tr>"));
		if (!$inline)
		{
			$form->add(str("<td colspan=\"2\">\n"));
			$form->add(fsubmit("Endre"));
			$form->add(str("</td></tr></table>"));
		} else
			$form->add(str("<td>\n"));
		return $form;
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
			$private .= $pr;
		$born = $_REQUEST['born'];	
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
			if (strlen($user) < 2 || strlen($user) > 10)
			{
				$this->error = "Brukernavn må være minst 2 og maks 10 tegn";
				return false;
			}
		}
		$query = "SELECT uid FROM users WHERE uname = '";
		$query .= $db->escape($user);
		$query .= "';";
		$res = $db->query($query);
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
			$query .= $db->escape($extra) . "' WHERE uname = '";
			$query .= $db->escape($user) . "' AND uid = '$res';";
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
