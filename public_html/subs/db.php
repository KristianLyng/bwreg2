<?
 
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
	function query($query, $cb = null)
	{
		$result = mysql_query($query); // or sql_error($query);
		
		while ($row = mysql_fetch_array($result))
		{
			if($cb == null)
			{
				$ans = $row[0];
				mysql_free_result($result);
				return $ans;
			}
			$cb->sqlcb($row);
		}
		mysql_free_result($result);
	}
}

?>
