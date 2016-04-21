<?php
/* for flood map display only $Id: fmap-w-river.php,v 1.1 2012/02/29 19:23:08 ytian Exp ytian $
*/

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

$datadir='/data/web/html/CREST/global/output';
$ts=3*60*60;   // 3-hrly in sec

$currT = mktime(21, 0, 0, date("m"), date("d"), date("Y"));

//for ($tmpT=$currT; $tmpT>$currT-$ts*24; $tmpT -= $ts) { //back search 3 days 
for ($tmpT=$currT; $tmpT>$currT-$ts*240; $tmpT -= $ts) { //back search 30 days 

 $ftime = date("YmdH", $tmpT); 
 $dfile = $datadir . "/global." . $ftime . ".Level.bif";
 if (file_exists($dfile)) { 
   return $tmpT;
 }

}

return -1; 

}

$GADDIR='/data/web/html/CREST/grads-2.0.a7.1/data';
$GASCRP='/data/web/html/CREST/grads-2.0.a7.1/scripts';
$GRADS='/data/web/html/CREST/grads-2.0.a7.1/bin/grads';
$basedir='/data/web/html/CREST/global';
$datadir="$basedir/output";
$imgdir="$basedir/plots"; 
$imgurl='plots';
$tstep = 3*60*60;  // 3hrly in sec

// defaults states 
$var = $_REQUEST["var"]; if (!isset($var)) { $var='runoff'; }
$tp = $_REQUEST["tp"]; if (!isset($tp)) { $tp = 1; }  //plot type

// current, previous and next timestamp 
$ct0 = most_recent_t(); 
$ct = $_REQUEST["ct"]; if (!isset($ct)) { $ct = $ct0; }
// animation start/stop time, in 03Z10Jan2008 format  
$a = $_REQUEST["a"]; if (!isset($a)) { $a = 0; }
$at0 = date("H\ZdMY", $ct0 - 3*8*$tstep);
$at1 = $_REQUEST["at1"]; if (!isset($at1)) { $at1 = $at0; } 
$at2 = $_REQUEST["at2"]; if (!isset($at2)) { $at2 = date("H\ZdMY", $ct0); }

$lat1 = $_REQUEST["lat1"]; if (!isset($lat1)) { $lat1=-50; }
$lat2 = $_REQUEST["lat2"]; if (!isset($lat2)) { $lat2=50; }
$lon1 = $_REQUEST["lon1"]; if (!isset($lon1)) { $lon1=-127.25; }
$lon2 = $_REQUEST["lon2"]; if (!isset($lon2)) { $lon2=180; }
$lat0 = $_REQUEST["lat0"]; if (!isset($lat0)) { $lat0 = 0.5 * ($lat1 + $lat2); }
$lon0 = $_REQUEST["lon0"]; if (!isset($lon0)) { $lon0 = 0.5 * ($lon1 + $lon2); }


$pan=$_REQUEST["P"]; 
$zoom=$_REQUEST["ZM"]; 
$tseries=$_REQUEST["TS"];

// clean tainted variables for malicious user input: 
// only alphanumeric, '+', '-', '.' allowed. 
$pattern = '/[^a-zA-Z0-9\+\-\.]/';
$var = preg_replace($pattern, '', $var);
$tp = preg_replace($pattern, '', $tp);
$ct = preg_replace($pattern, '', $ct);
$at1 = preg_replace($pattern, '', $at1);
$at2 = preg_replace($pattern, '', $at2);
$lat1 = preg_replace($pattern, '', $lat1);
$lat2 = preg_replace($pattern, '', $lat2);
$lon1 = preg_replace($pattern, '', $lon1);
$lon2 = preg_replace($pattern, '', $lon2);
$lat0 = preg_replace($pattern, '', $lat0);
$lon0 = preg_replace($pattern, '', $lon0);
$pan = preg_replace($pattern, '', $pan);
$zoom = preg_replace($pattern, '', $zoom);
$tseries = preg_replace($pattern, '', $tseries);

$var="fmap";
$ctlf="$basedir/out-onesto.ctl"; 
$tctl="$basedir/Threshold_m3.ctl";   // threshold file

$h=$lat2-$lat1; 
$w=$lon2-$lon1; 

// animation control
if ($a == 1 ) { 
  $metas=''; $metae=''; $abutton='Stop'; $na=0; 
  $at1 = date("H\ZdMY", gd2time($at1) + $tstep ); 
  $ct = gd2time($at1); 
  if (gd2time($at1) > gd2time($at2) ) {  // stop the animation 
     $at1 = $at0; $abutton='Animate';    
     $na = 1; $metas='!--'; $metae='--';
     $ct = $ct0; 
  }
} else { 
  $metas='!--'; $metae='--'; $abutton='Animate'; $na=1;
}

$pt = $ct - $tstep;  
$nt = $ct + $tstep;  
if ($nt > $ct0 ) { $nt = $ct0; }

switch ($pan) { 
  case 'u':
    $lat1 += $h/3; $lat2 += $h/3; break;  
  case 'd':
    $lat1 -= $h/3; $lat2 -= $h/3; break;  
  case 'r':
    $lon1 += $w/3; $lon2 += $w/3; break;  
  case 'l':
    $lon1 -= $w/3; $lon2 -= $w/3; break;  
}

switch ($zoom) { 
  case '1':
    $lat1 += $h/4; $lat2 -= $h/4; 
    $lon1 += $w/4; $lon2 -= $w/4; 
    break; 
  case '-1':
    $lat1 -= $h/4; $lat2 += $h/4; 
    $lon1 -= $w/4; $lon2 += $w/4; 
    break; 
}

$lat1 = round($lat1, 2); 
$lat2 = round($lat2, 2); 
$lon1 = round($lon1, 2); 
$lon2 = round($lon2, 2); 
$lat0 = round($lat0, 2);
$lon0 = round($lon0, 2);

# river network density determined by zomming level
$rdens1=($lon2-$lon1);
$rdens2=$rdens1*100;
$rdens3=$rdens1*10000;

# plot type
$type[1] = "shaded";
$type[2] = "contour";
$type[3] = "grfill";

$clevs["fmap"]='0 1'; 
$clevs["rain"]='0 1 2 4 8 16 32 64 128'; 
$clevs["onew"]='0 20 40 60 80 100 120 140'; 
$clevs["level"]='1 2 3 4 5 6 7'; 
$clevs["onesto"]='0 1 2 4 8 16 32 64 128'; 
$clevs["oneexc"]=''; 
$clevs["p95"]='5 10 20 50 100 200'; 

$cclrs["p95"]='15 3 5 4 7 6 2'; 

$title["fmap"]='Flood Estimate (Method 3) [mm]';
$title["runoff"]='Stream flow (m^3/s)';
$title["rain"]='Rainfall (mm/h)'; 
$title["onew"]='Soil moisture (mm)'; 
$title["level"]='Flood level'; 
$title["onesto"]='Routed surface runoff (mm)'; 
$title["oneexc"]='Direct runoff (mm)'; 
$title["p95"]='Relative routed runoff (mm)'; 

// swtich for select form
$select["fmap"] = '';
$select["runoff"] = '';
$select["rain"] = '';
$select["onew"] = '';
$select["level"] = '';
$select["onesto"] = '';
$select["oneexc"] = '';
$select["p95"] = '';

$select[$var] = 'SELECTED';

if ($ct > 0 ) {
   $gdtime =  date("H\ZdMY", $ct);   // grads time format
} else {
   echo "No data files found<br>";
   exit; 
}

$lonstr="$lon1 $lon2";
$latstr="$lat1 $lat2";
if ($tseries == 1 ) {
  $lonstr=$lon0;
  $latstr=$lat0;
  $gdtime="$at1 $at2";
}


$pid = getmypid(); 

$output=`export GADDIR=$GADDIR; export GASCRP=$GASCRP; export TERM=vt100; $GRADS -bl <<EOF
open $ctlf
open $tctl
open $basedir/FAM.ctl
set gxout $type[$tp]
set grads off
set mpdset hires
set xlopts 1 6.0 0.15
set ylopts 1 6.0 0.15
set time $gdtime
set lat $latstr
set lon $lonstr
set clevs $rdens1 $rdens2 $rdens3
set ccols 15 0 0
d fam.3(t=1)
set clevs  0 5 10 20 50 100 200 
set ccols 15 3 5 4 7 8 6 2
d maskout(onesto-tm3.2(t=1), onesto-tm3.2(t=1)-5) 
*d maskout(const(const(maskout(onesto, onesto-tm3.2(t=1)), 1), 0, -u), onesto-0) 

draw title $title[$var] $gdtime
cbarn
printim $imgdir/$var-$pid.gif gif white x780 y650
quit
EOF`;

$imgurl = "$imgurl/$var-$pid.gif"; 

//html head as heredoc
$head = <<<HEAD
<html>
<head>
<META HTTP-EQUIV="Content-Style-Type" CONTENT="text/css">
<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT">
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<${metas}META http-equiv="refresh" content="1; url=fmap-w-river.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&tp=$tp&at1=$at1&at2=$at2&a=1"$metae>
<title> Flood Monitoring with 3B42RT and CREST </title>
<style type="text/css">
 A:link {text-decoration: none}
 A:visited {text-decoration: none}
 A:active {text-decoration: none}
 A:hover {text-decoration: none; color: green; }

        #leftbox {
                position: absolute;
                left:0px;
                top:0px;
                width:785px;
                background:#fff;
                border:1px solid #000;
                }

      #panbox {
                position: absolute;
                left: 795px;
                top:5px;
                padding: 5px;
                width:170px;
                height:150px;
                background:#fff;
                border:1px solid #000;
                }

      #zoombox {
                position: absolute;
                left: 795px;
                top:175px;
                padding: 5px;
                width:170px;
                height:100px;
                background:#fff;
                border:1px solid #000;
                }

       #typebox {
                position: absolute;
                left: 795px;
                top:305px;
                padding: 5px;
                width:170px;
                height:55px;
                background:#cfcfcf;
                border:1px solid #000;
                }


        #locbox {
                position: absolute;
                left: 795px;
                top:390px;
                padding: 5px;
                width:170px;
                height:135px;
                background:#cfcfcf;
                border:1px solid #000;
                }

        #tzbox {
                position: absolute;
                left: 1px;
                top:660px;
                padding: 1px;
                width:783px;
                height:30px;
                background:#cfcfcf;
                border:1px solid #000;
                }

        #animbox {
                position: absolute;
                left: 1px;
                top:700px;
                padding: 1px;
                width:783px;
                height:30px;
                background:#cfcfcf;
                border:1px solid #000;
                }

        #varbox {
                position: absolute;
                left: 795px;
                top:555px;
                padding: 5px;
                width:170px;
                height:75px;
                background:#cfcfcf;
                border:1px solid #000;
                }


        #exitbox {
                position: absolute;
                left: 795px;
                top:660px;
                padding: 1px;
                width:179px;
                height:30px;
                background:#cfcfcf;
                border:1px solid #000;
                }

</style>
</head>
HEAD;

//html body as heredoc

$body = <<<EOF

<body>
<div id="leftbox">
<img src="$imgurl" border=0 align=right>
</div>
<div id="panbox">
<center>
<table>
<tr><td colspan=3 align=center>
<a href="fmap-w-river.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&P=u"> <img src="images/ua.gif" border=0> </a>
</td></tr>
<tr><td>
<a href="fmap-w-river.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&P=l"> <img src="images/la.gif" border=0> </a>
</td>
    <td> &nbsp; &nbsp; &nbsp; &nbsp; </td>
    <td>
<a href="fmap-w-river.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&P=r"> <img src="images/ra.gif" border=0> </a>
</td></tr>
<tr>
<td colspan=3 align=center>
<a href="fmap-w-river.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&P=d"> <img src="images/da.gif" border=0> </a>
</td>
</tr>
</table>
</center>
</div>
<div id="zoombox">
<center>
<table cellpadding=4>
<tr><td>
<a href="fmap-w-river.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&ZM=1"> <img src="images/zi.gif" border=0> </a></td></tr>
<tr><td>
<a href="fmap-w-river.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&ZM=-1"> <img src="images/zo.gif" border=0> </a></td></tr>
</table>
</center>
</div>
<div id="typebox">
<form method="post" action="fmap-w-river.php">
<input type="hidden" name="var" value="$var">
<input type="hidden" name="lat1" value="$lat1">
<input type="hidden" name="lat2" value="$lat2">
<input type="hidden" name="lon1" value="$lon1">
<input type="hidden" name="lon2" value="$lon2">
<input type="hidden" name="T" value="$T">
<input type="hidden" name="Z" value="$Z">
<input type="hidden" name="ct" value="$ct">
<input type="hidden" name="z" value="$z">
<input type="hidden" name="E" value="$E">
<input type="hidden" name="W" value="$W">
<input type="hidden" name="S" value="$S">
<input type="hidden" name="N" value="$N">
<b>Select plot type: </b>
<select name="tp">
<option value="1">Shaded
<option value="2">Contour
<option value="3">Gridfill
</select>
<input type="submit" value="Plot">
</form>
</div>

<div id="locbox">
<form method="post" action="fmap-w-river.php">
<input type="hidden" name="var" value="$var">
<input type="hidden" name="tp" value="$tp">
<input type="hidden" name="T" value="$T">
<input type="hidden" name="Z" value="$Z">
<input type="hidden" name="ct" value="$ct">
<input type="hidden" name="z" value="$z">
<input type="hidden" name="E" value="$E">
<input type="hidden" name="W" value="$W">
<input type="hidden" name="S" value="$S">
<input type="hidden" name="N" value="$N">
<input type="hidden" name="TS" value="1">
<b>Jump to (lat, lon):<br> </b>
<table cellpadding=0 cellspacing=0>
<tr><td> <input type="text" name="lat0" maxlength=8 size=6 value="$lat0"></td>
    <td> <input type="text" name="lon0" maxlength=8 size=6 value="$lon0"></td> </tr>
<tr>
<td colspan=2>T1: <input type="text" name="at1" maxlength=12 size=12 value="$at1"></td>
</tr> <tr>
<td colspan=2>T2: <input type="text" name="at2" maxlength=12 size=12 value="$at2"></td>
</tr>
</table>
<center><input type="submit" value="See time series"></center>
</form>
</div>

<div id="varbox">
<form method="post" action="fmap-w-river.php">
<input type="hidden" name="lat1" value="$lat1">
<input type="hidden" name="lat2" value="$lat2">
<input type="hidden" name="lon1" value="$lon1">
<input type="hidden" name="lon2" value="$lon2">
<input type="hidden" name="T" value="$T">
<input type="hidden" name="Z" value="$Z">
<input type="hidden" name="ct" value="$ct">
<input type="hidden" name="z" value="$z">
<input type="hidden" name="E" value="$E">
<input type="hidden" name="W" value="$W">
<input type="hidden" name="S" value="$S">
<input type="hidden" name="N" value="$N">
<b>Select variable: </b>
<select name="var">
<option value="fmap" ${select['fmap']} >Flood map
</select>
<input type="submit" value="Plot">
</form>
</div>

<div id="exitbox">
<center>
<a href="fmap-w-river.php"><input type="submit" value="Reset"></a>
</center>
</div>

<div id="tzbox">
<center>
<table> <tr>
<td> <a href="fmap-w-river.php?ct=$pt&var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&t=$t&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp"> 
<b>Previous time step << </b></a>  </td> 
<td>&nbsp; &nbsp;&nbsp; &nbsp;  </td>
<td> <a href="fmap-w-river.php?ct=$nt&var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&t=$t&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp"> 
<b> >> Next time step </b></a>  </td> 
</tr> </table>
</center>
</div>

<div id="animbox">  
<center>
<form method="post" action="fmap-w-river.php">
<input type="hidden" name="var" value="$var">
<input type="hidden" name="lat1" value="$lat1">
<input type="hidden" name="lat2" value="$lat2">
<input type="hidden" name="lon1" value="$lon1">
<input type="hidden" name="lon2" value="$lon2">
<input type="hidden" name="T" value="$T">
<input type="hidden" name="ct" value="$ct">
<input type="hidden" name="Z" value="$Z">
<input type="hidden" name="tp" value="$tp">
<input type="hidden" name="z" value="$z">
<input type="hidden" name="E" value="$E">
<input type="hidden" name="W" value="$W">
<input type="hidden" name="S" value="$S">
<input type="hidden" name="N" value="$N">
<input type="hidden" name="a" value="$na">
<table cellpadding=0 cellspacing=0>
<tr>
<td>Start time: &nbsp; &nbsp; </td><td><input type="text" name="at1" maxlength=12 size=12 value="$at1"></td>
<td>&nbsp; &nbsp; End time: &nbsp; &nbsp; </td><td><input type="text" name="at2" maxlength=12 size=12 value="$at2"></td>
<td>&nbsp; &nbsp; <input type="submit" value="$abutton"></td> 
</tr> 
</table>
</form>
</center>
</div>

<!--pre>
$output
</pre-->
</body></html>

EOF;

echo $head; 
echo $body; 

?>
