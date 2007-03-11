<?php 

include "subs/html.php";

/* Create the objects */
$page = new page("BWReg2 raw dev page", "This is the header of the page");

$menu1 = new menu("En undermeny");
$menu2 = new menu("Petter");
$menu3 = new menu("Enda en meny");
$news1 = new news("Kristian Lyngstol", "cirka naa");
$news2 = new news("Jesus", "tusen aar siden");
$news3 = new news("Adam", "Tidenes morgen");
$infoboks1 = new infoboks();
$dropdown = new dropdown("Action");
$somebox = new box();

/* Popluate the content */
$page->content->add(&$infoboks1);
$page->content->add(&$news1);
$page->content->add(&$news2);
$page->content->add(&$news3);
$page->logo->add(img("images/bolerlanlogo.png","BølerLANÆ"));
$page->ctrl1->add($menu3);
$page->ctrl2->add($menu3);
$page->footer->add(h1("copyright ME"));
$page->info4->add(h1("Warning!"));
$page->info4->add(p("This is very very scary"));
$page->info4->add($dropdown);
$page->info1->add(h1("Tam info"));
$page->info1->add(p("This is quite boring"));


$somebox->addst("Somepage!");
$somebox->add(img("images/glider.png","glideralt"));
$menu3->add(htlink("somepage",str("foo")));
$menu3->add($menu2);
$menu3->add($menu1);
/* Populate them */
$infoboks1->add(p("Dette er en informativ infoboks ja."));
$infoboks1->add(p("VELDIG informativ."));

$menu1->add(htlink("foo", p("SomeItem Knis")));
$menu1->add(htlink("foo", p("SomeItem")));
$menu1->add(htlink("foo", p("SomeItem")));

$menu2->add(htlink("foo", p("Someother Item")));
$menu2->add(htlink("foo", p("SomeItem other")));
$menu2->add(htlink("foo", p("SomeItem")));
$menu2->add(htlink("abra",$somebox));

/* Populate the last menu with one normal item, and the two
 * other menus.
 */
$dropdown->add(htlink("foo.html",str("Read")));
$dropdown->add(htlink("bar.html",str("Hide")));


$news1->add(p("Fooooooo bar etc etc"));
$news2->add(p("Fooooooo bar etc etc"));
$news3->add(p("Fooooooo bar etc etc Knut. Dette er litt tamt..."));
$news3->add(p("Fordelen er at dette nesten ikke krever html-kunskap, og php-filene blir html-frie"));


/* Output the page */
$page->output();
?>

