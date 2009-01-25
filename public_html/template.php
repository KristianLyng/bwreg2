<?php 
include "subs/env.php";

/* This page is a sort of template/demo of the 
 * "top page" of bwreg.
 * It will be updated to test/demo functionality, and much of 
 * the functionality this page demonstrates will be used internally
 * in other parts of bwreg2 as they are developed.
 */
 global $page;
 $crew = perm_path("Crew","r");
 $foo = perm_path("Foo","w");
 $deep = perm_path("Crew/Ticket/Admin","r");
 
 $str = "";
 if ($crew)
 	$str .= "Crew ok!<br>\n";
 if ($foo)
 	$str .= "Foo ok!<br>\n";
 if ($deep)
 	$str .= "Deep ok!<br>\n";
 $page->content->add(str($str));
global $me;

?>
