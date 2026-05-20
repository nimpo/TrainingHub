<?php

#$d = scandir('/srv/html/mail');
$d = preg_grep('/^([^.])/', scandir('/srv/html/mail'));
natcasesort($d);
foreach ($d as $name) { 
  echo "$name<br />\n";
}
?>
