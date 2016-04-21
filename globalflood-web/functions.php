
<?php

function most_recent_t()  { 
// function to find the most recent time at which there are output data

$datadir='/var/www/html/LIS/PRECIP/ARCTAS/CREST/global/output';
$ts=3*60*60;   // 3-hrly in sec

$currT = mktime(21, 0, 0, date("m"), date("d"), date("Y"));

for ($tmpT=$currT; $tmpT>$currT-$ts*24; $tmpT -= $ts) { //back search 3 days 

 $ftime = date("YmdH", $tmpT); 
 $dfile = $datadir . "/global." . $ftime . ".Level.bif";
 if (file_exists($dfile)) { 
   return $tmpT;
 }

}

return -1; 

}

/*-

$testT=most_recent_t(); 

if ($testT > 0 ) { 
   echo  date("YmdH", $testT) . "<br>";
   $gdtime =  date("H\ZdMY", $testT);   // grads time format
   echo "Grads time: $gdtime<br>"; 

} else { 
   echo "not found<br>"; 
}

$var = $_REQUEST["var"]; if (!isset($var)) { $var='runoff'; }

echo "$var<br>"; 
---*/

?>
