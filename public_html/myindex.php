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

/* Popluate the content */
$page->content->add(&$infoboks1);
$page->content->add(&$news1);
$page->content->add(&$news2);
$page->content->add(&$news3);
$page->ctrl1->add($menu3);
$page->ctrl2->add($menu3);
$page->footer->addst("<h1>copyright ME</h1>");
$page->info4->addst("<h1>Warning!</h1>");
$page->info4->addst("<p>This is very very scary</p>");
$page->info4->add($dropdown);
$page->info1->addst("<h1>Tam info</h1>");
$page->info1->addst("<p>This is quite boring</p>");

$menu3->addst("<a href=\"somepage\">somepage</a>");
$menu3->add($menu2);
$menu3->add($menu1);

/* Populate them */
$infoboks1->addst("<p>Dette er en informativ infoboks ja.</p>");
$infoboks1->addst("<p>VELDIG informativ.</p>");

$menu1->addst("<a href=\"foo\">Some item</a>");
$menu1->addst("<a href=\"foo\">Some item</a>");
$menu1->addst("<a href=\"foo\">Some item</a>");

$menu2->addst("<a href=\"foo\">Some item</a>");
$menu2->addst("<a href=\"foo\">Some item</a>");
$menu2->addst("<a href=\"foo\">Some item</a>");

/* Populate the last menu with one normal item, and the two
 * other menus.
 */
$dropdown = new dropdown("Action");
$dropdown->addst("<a href=\"foo.html\">Read</a>");
$dropdown->addst("<a href=\"foo.html\">Hide this</a>");


$news1->addst("<p>Fooooooo bar etc etc</p>");
$news2->addst("<p>Fooooooo bar etc etc</p>");
$news3->addst("<p>Fooooooo bar etc etc</p>");


/* Output the page */
$page->output();
?>

