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
		$box = new userinfoboks();
		$box->add(h1(htlink("mailto:" . $this->mail, str($this->firstname . " " . $this->lastname))));
		$box->add(str("phone: " . $this->phone));
		$box->add(htmlbr());
		$box->add(str("extra: " . $this->extra));
		$box->add(htmlbr());
		if($this->born != null && $this->born != 0)
		$box->add(str("born: " . $this->born->get()));
		$box->add($this->pluginextra);
		return $box->get();
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
			return;
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
			 "WHERE group_members.uid = '";
		if (!is_int($uid))
			$query .= $db->escape($uid);
		else
			$query .= $uid;
		$query .= "' OR @super > 0;";
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
			$page->content->add(p("(Egentlig ikke... hysj"));
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
	function list_perms($checker)
	{
		foreach ($this->perms->keys as $param => $value)
		{
			$string .= "<option value=\"" . $param . "\"";
			if ( $param == $checker )
				$string .= " selected ";
			$string .= ">" . $value . "</option>\n";
			
		}
		return $string;
	}
	function login($user, $password)
	{
		global $db;
		$myuser = $db->escape($user);
		$mypass = $db->escapepass($password);
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE uname = '";
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
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE uname = '";
		$query .= $db->escape($user);
		$query .= "';";
		return $db->query($query, &$this);
	}
	function c_uid($uid)
	{
		global $db;
		$query = "SELECT uid,uname,firstname,lastname,mail,birthyear,adress,phone,extra FROM users WHERE uid = '";
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
class group
{
	function group($name, $id, $gid, $desc)
	{
		$this->name = $name;
		$this->id = $id;
		$this->gid = $gid;
		$this->desc = $desc;
	}
	function get()
	{
		return $this->name;
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
	function grouplist($gid)
	{
		global $db;
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
		 else if ($_POST['uname'] && $_POST['pass']) {
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
	function print_logout()
	{
		global $page;
		$url = $page->url();
		$url .= "?action=Logout";
		$object = htlink($url, str("Logout"));
		return $object->get();
	}
	function get()
	{
		if($this->failed && $this->uid == 0) {
			return "Login failed" . $this->print_box();
		} else if ($this->uid > 0) {
			return parent::get() . " " . $this->print_logout();
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
	function newuser()
	{
	}
	function set_form()
	{
		$form = new form();
		$form->add(str("<table style=\"UserForm\"><tr>"));
		$form->add(str("<td><label for=\"firstname\" title=\"Fornavn\">Fornavn</label></td><td>"));
		$form->add(ftext("firstname"));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Etternavn</td><td>"));
		$form->add(ftext("lastname"));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Telefonnummer</td><td>"));
		$form->add(ftext("phone"));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("E-post</td><td>"));
		$form->add(ftext("mail"));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Addresse</td><td>"));
		$form->add(ftext("address"));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Brukernavn</td><td>"));
		$form->add(ftext("user"));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Passord</td><td>"));
		$form->add(fpass("pass",8));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Bekreft passord</td><td>"));
		$form->add(fpass("pass_confirm",9));
		$form->add(str("</td></tr><tr><td>\n"));
		$form->add(str("Tilleggsinformasjon</td><td>"));
		$form->add(ftext("extra"));
		$form->add(str("</td></tr><tr><td colspan=\"2\">\n"));
		$form->add(fhidden("NewUserStore"));
		$form->add(fsubmit("Lagre"));
		$form->add(str("</td></tr></table>"));
		return $form;
	}
	function get_form()
	{
		$firstname = $_REQUEST['firstname'];
		$lastname = $_REQUEST['lastname'];
		$phone = $_REQUEST['phone'];
		$mail = $_REQUEST['mail'];
		$address = $_REQUEST['address'];
		$pass = $_REQUEST['pass'];
		$confirm = $_REQUEST['pass_confirm'];
		$extra = $_REQUEST['extra'];
		$user = $_REQUEST['user'];
		global $db;
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
		if (strlen($user) < 2 || strlen($user) > 10)
		{
			$this->error = "Brukernavn må være minst 2 og maks 10 tegn";
			return false;
		}
		$query = "SELECT uid FROM users WHERE uname = '";
		$query .= $db->escape($user);
		$query .= "';";
		if ($db->query($query))
		{
			$this->error = "Brukernavnet du oppga eksisterer allerede";
			return false;
		}
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->phone = $phone;
		$this->mail = $mail;
		$this->address = $address;
		$this->pass = $pass;
		$this->extra = $extra;
		$query = "INSERT INTO users (uname,firstname,lastname,mail,adress,phone,extra,pass)";
		$query .= " VALUES('";
		$query .= $db->escape($user) . "','";
		$query .= $db->escape($firstname) . "','";
		$query .= $db->escape($lastname) . "','";
		$query .= $db->escape($mail) . "','";
		$query .= $db->escape($address) . "','";
		$query .= $db->escape($phone) . "','";
		$query .= $db->escape($extra) . "',";
		$query .= $db->escapepass($pass) . ");";
		if (!$db->insert($query))
		{
			$this->error = "En ukjent feil oppstod når brukeren din ble opprettet. Dette skal ikke skje...";
			return false;
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
