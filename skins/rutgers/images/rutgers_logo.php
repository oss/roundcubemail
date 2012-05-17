<?php

if ($_SERVER['QUERY_STRING'] == "test")
{
   echo ("user agent: " . $_SERVER['HTTP_USER_AGENT']. "<br>");
   echo ("<object type=\"image/svg+xml\" data=\"rutgers_logo.php\" width=165 height=45>");
   echo ("<img width=165 height=45 src=\"rutgers_logo.php\"></object>");
   echo ("<script type='text/javascript'>document.write(navigator.userAgent);</script>");
}
else if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE") != FALSE)
{
   header ("Content-Type: image/png");
   echo file_get_contents ("rutgers_logo.png");
}
else
{
   header ("Content-Type: image/svg+xml");
   echo file_get_contents ("rutgers_logo.svg");
}

?>
