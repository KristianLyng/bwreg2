<?
/* This defines the database interface. 
 * This is where the majority of changes would take place to
 * accomodate diffrent databases.
 */
class database
{
	var $link;
	function database()
	{
		global $config;
		$this->link = mysql_pconnect($config->db->host,$config->db->user,$config->db->pass);
		if($this->link && mysql_select_db($config->db->db))
		{
			return;
		}
		print("Couldn't connect to the database.");
	}
	function error($query)
	{
		global $me;
		print "An SQL error occured while loading the page. \n";
		print "Please contact the administrator if this happens again. \n";
		if (is_object($me))
		{
				if(me_perm(null,"r"))
					print "SQL query: $query\n";
		}
		return false;
	}
	function insert($query)
	{
		return mysql_query($query) or $this->error($query);
	}
	function query($query, $cb = null)
	{
		$result = mysql_query($query) or $this->error($query);
		if (!$result)
			return false;
		$bla = false;	
		while ($row = mysql_fetch_array($result))
		{
			if($cb == null)
			{
				$ans = $row;
				mysql_free_result($result);
				return $ans;
			}
			$bla = true;
			
			$cb->sqlcb($row);
		}
		mysql_free_result($result);
		return $bla;
	}
	function escape($string)
	{
			return mysql_escape_string($string);
	}
	function escapepass($string)
	{
			return "'" . mysql_escape_string($string) . "'";
	}
}

?>
