<?php 
include "subs/env.php";

/* This page is a sort of template/demo of the 
 * "top page" of bwreg.
 * It will be updated to test/demo functionality, and much of 
 * the functionality this page demonstrates will be used internally
 * in other parts of bwreg2 as they are developed.
 */
$grp = new group("foo","3","1","foo");
$grp->get_members();
$page->content->add($grp);

?>
