<?php
// MIT license.

// TODO: make it possible to login as a different user. via http-auth e.g.
include "googleLogin.php";
include "config.php";
$group = $_GET['group'];
$g = new googleLogin();
$rss = $g->curlGet("https://groups.google.com/a/$domain/group/$group/feed/rss_v2_0_msgs.xml?num=50");
// if not logged in, login
if (strpos ($rss, "Sign in") != FALSE)
{
    $g->login("$user","$pass");

    $rss = $g->curlGet("https://groups.google.com/a/$domain/group/$group/feed/rss_v2_0_msgs.xml?num=50");
}

header("Content-Type: text/xml");

print $rss;
