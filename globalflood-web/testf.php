
<?php
function gd2time($gdstr) {
// function to convert 03Z10Jan2008 to unix time

$mn["jan"] = 1; $mn["feb"] = 2; $mn["mar"] = 3; $mn["apr"] = 4; 
$mn["may"] = 5; $mn["jun"] = 6; $mn["jul"] = 7; $mn["aug"] = 8; 
$mn["sep"] = 9; $mn["oct"] = 10; $mn["nov"] = 11; $mn["dec"] = 121; 

list($hr, $dy, $ms, $yr) = sscanf($gdstr, "%dZ%2d%3s%4d");

$ms=strtolower($ms); 

//echo "$hr-$dy-$mn[$ms]-$yr<br>"; 
return mktime($hr, 0, 0, $mn[$ms], $dy, $yr); 

}

//echo gd2time("03Z10Jan2010"); 

$var = "dafsd!#asdfa123-0.5\nAA*/"; 

$pattern = '/[^a-zA-Z0-9+-\.]/';
$var = preg_replace($pattern, '', $var); 

echo "$var<br>"; 

?>
