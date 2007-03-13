<?php 
include "subs/env.php";

/* This page is a sort of template/demo of the 
 * "top page" of bwreg.
 * It will be updated to test/demo functionality, and much of 
 * the functionality this page demonstrates will be used internally
 * in other parts of bwreg2 as they are developed.
 */
$dropdown2 = new dropdown("");
$menu1 = new menu("En undermeny");
$menu2 = new menu("Petter");
$menu3 = new menu("Enda en meny");
$news1 = new news(&$dropdown2, "cirka naa");
$news3 = new news("Adam", "Tidenes morgen");
$dropdown = new dropdown("Action");
$somebox = new box();
$page->content->add(&$news1);
$page->content->add(&$news3);

$page->ctrl1->add($menu3);

$somebox->addst("Somepage!");
$somebox->add(img("images/glider.png","glideralt"));
$menu3->add(htlink("somepage",str("foo")));
$menu3->add($menu2);
$menu3->add($menu1);

$menu1->add(htlink("foo", str("SomeItem")));
$menu1->add(htlink("foo", str("SomeItem")));

$menu2->add(htlink("foo", str("SomeItem")));
$menu2->add(htlink("abra",$somebox));

$news1->add(p("Fooooooo bar etc etc"));
$news3->add(p("Fooooooo bar etc etc Knut. Dette er litt tamt..."));
$news3->add(p("Fordelen er at dette nesten ikke krever html-kunskap, og php-filene blir html-frie"));

?>
