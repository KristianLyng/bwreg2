<?

class session
{
	function session()
	{
		session_start();
		header("Cache-control: private");
	}
}

?>
