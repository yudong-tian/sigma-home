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

function most_recent_t()  { 
// function to find the most recent time at which there are output data

$datadir='/data/www/data';
$ts=24*60*60;   // 3-hrly in sec

$currT = mktime(21, 0, 0, date("m"), date("d"), date("Y"));

#for ($tmpT=$currT; $tmpT>$currT-$ts*24; $tmpT -= $ts) { //back search 3 days 
for ($tmpT=$currT; $tmpT>$currT-$ts*240; $tmpT -= $ts) { //back search 30 days 

 $ftime = date("YmdH", $tmpT); 
 $dfile = $datadir . "/global." . $ftime . ".Level.bif";
 if (file_exists($dfile)) { 
   return $tmpT;
 }

}

return -1; 

}

function mid_night_t()  {
// function to give the last  midnight time 

$ct= mktime(0, 0, 0, date("m"), date("d"), date("Y"));
return date("H\ZdMY", $ct); 

}


$GADDIR='/DATA/LIS/grads-2.0.a7.1/data';
$GASCRP='/var/www/html/LIS/PMM/le/scripts';
$GRADS='/DATA/LIS/grads-2.0.a7.1/bin/grads';
$basedir='/var/www/html/LIS/PMM/';
$datadir="$basedir/OUTPUT-global-0.25";
$imgdir="$basedir/le/plots"; 
$imgurl='plots';
$tstep = 24*60*60;  // 3hrly in sec

#default lat/lon box: CONUS
$dlat1=-20; 
$dlat2=70; 
$dlon1=-127.25; 
$dlon2=60; 

# plot type
$type[1] = "shaded";
$type[2] = "contour";
$type[3] = "grfill";

$clevs["em7V"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em7H"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em11V"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em11H"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em19V"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em19H"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em24V"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em24H"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em37V"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em37H"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em89V"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["em89H"]='.72 .75 .78 .81 .84 .87 .90 .93 .96 .99'; 
$clevs["sm"]='0 .05 0.1 .15 0.2 .25 0.3 .35 0.4 .45'; 

$title["em7V"]='Emissivity 6.9 GHz V-pol';
$title["em7H"]='Emissivity 6.9 GHz H-pol';
$title["em11V"]='Emissivity 10.65 GHz V-pol';
$title["em11H"]='Emissivity 10.65 GHz H-pol';
$title["em19V"]='Emissivity 18.7 GHz V-pol';
$title["em19H"]='Emissivity 18.7 GHz H-pol';
$title["em24V"]='Emissivity 23.8 GHz V-pol';
$title["em24H"]='Emissivity 23.8 GHz H-pol';
$title["em37V"]='Emissivity 36.5 GHz V-pol';
$title["em37H"]='Emissivity 36.5 GHz H-pol';
$title["em89V"]='Emissivity 89.0 GHz V-pol';
$title["em89H"]='Emissivity 89.0 GHz H-pol';
$title["sm"]='Soil moisture (m3/m3)'; 

$unit["SSMI/F13-daily"]='(mm/h)';  

$cclrs["fmap"]='15 3 5 4 7 8 2'; 
$cclrs["p95"]='15 3 5 4 7 8 2'; 
$cclrs["onesto"]='15 3 5 4 7 8 2'; 
$cclrs["runoff"]='15 3 5 4 7 8 2'; 
$cclrs["rain"]='15 10 3 5 4 7 8 2'; 
$cclrs["rain1d"]='15 10 3 5 4 7 8 2';
$cclrs["rain3d"]='15 10 3 5 4 7 8 2'; 
$cclrs["rain7d"]='15 10 3 5 4 7 8 2'; 

// swtich for select form
$select["fmap"] = '';
$select["runoff"] = '';
$select["rain"] = '';
$select["rain1d"] = '';
$select["rain3d"] = '';
$select["rain7d"] = '';
$select["onew"] = '';
$select["level"] = '';
$select["onesto"] = '';
$select["oneexc"] = '';
$select["p95"] = '';

?>
