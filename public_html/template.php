<?php 
include "subs/env.php";

/* This page is a sort of template/demo of the 
 * "top page" of bwreg.
 * It will be updated to test/demo functionality, and much of 
 * the functionality this page demonstrates will be used internally
 * in other parts of bwreg2 as they are developed.
 */
$menu1 = new menuboks($event->title);
$menu1->add(new content($event->gname . "Menu"));
$page->ctrl1->add(&$menu1);

?>
