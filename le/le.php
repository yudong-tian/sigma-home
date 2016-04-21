<?php
/* PHP Version of Land Explorer. $Id: le.php,v 1.15 2013/02/15 03:47:03 ytian Exp ytian $
*/

include '/var/www/html/LIS/PMM/le/config.php'; 

// defaults states 
$ctl = $_REQUEST["ctl"]; if (!isset($ctl)) { $ctl='cnsr-00Z'; }
$ddir = dirname($ctl); 
$var = $_REQUEST["var"]; if (!isset($var)) { $var='em7V'; }
$tp = $_REQUEST["tp"]; if (!isset($tp)) { $tp = 3; }  //plot type
$scl = $_REQUEST["scl"]; if (!isset($scl)) { $scl = 1; }  //scaling factor
$dt = $_REQUEST["dt"]; if (!isset($dt)) { $dt = $tstep; }  // time interval in sec 

// current, previous and next timestamp, in 00Z12Jul2012 format 
//$ct0 = mid_night_t();    // most_recent_t(); 
$ct0 = '00Z1Jul2005';    // most_recent_t(); 
$ct = $_REQUEST["ct"]; if (!isset($ct)) { $ct = $ct0; }
// animation start/stop time, in 03Z10Jan2008 format  
$a = $_REQUEST["a"]; if (!isset($a)) { $a = 0; }
$at0 = $ct; 
$at1 = $_REQUEST["at1"]; if (!isset($at1)) { $at1 = $at0; }
$at2 = $_REQUEST["at2"]; if (!isset($at2)) { $at2 = date("H\ZdMY", 
                                             gd2time($ct) + 24*$dt ); }

$lat1 = $_REQUEST["lat1"]; if (!isset($lat1)) { $lat1=$dlat1; }
$lat2 = $_REQUEST["lat2"]; if (!isset($lat2)) { $lat2=$dlat2; }
$lon1 = $_REQUEST["lon1"]; if (!isset($lon1)) { $lon1=$dlon1; }
$lon2 = $_REQUEST["lon2"]; if (!isset($lon2)) { $lon2=$dlon2; }
$lat0 = $_REQUEST["lat0"]; if (!isset($lat0)) { $lat0 = 0.5 * ($lat1 + $lat2); }
$lon0 = $_REQUEST["lon0"]; if (!isset($lon0)) { $lon0 = 0.5 * ($lon1 + $lon2); }


$pan=$_REQUEST["P"]; 
$zoom=$_REQUEST["ZM"]; 
$tseries=$_REQUEST["TS"];

// clean tainted variables for malicious user input: 
// only alphanumeric, '+', '-', '.' allowed. 
//$pattern = '/[^a-zA-Z0-9\+\-\.]/';
// only alphanumeric, '+', '-', '.', '/', '_', '(', ')' and ',' allowed.
$pattern = '/[^a-zA-Z0-9\+\-\.\/_\(\),]/';
$var = preg_replace($pattern, '', $var);
$ctl = preg_replace($pattern, '', $ctl);
$ddir = preg_replace($pattern, '', $ddir);
$scl = preg_replace($pattern, '', $scl);
$tp = preg_replace($pattern, '', $tp);
$ct = preg_replace($pattern, '', $ct);
$dt = preg_replace($pattern, '', $dt);
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

$ctlf="$datadir/$ctl"; 

$h=$lat2-$lat1; 
$w=$lon2-$lon1; 

// animation control
if ($a == 1 ) { 
  $metas=''; $metae=''; $abutton='Stop'; $na=0; 
  $at1 = date("H\ZdMY", gd2time($at1) + $dt ); 
  $ct = $at1; 
  if (gd2time($at1) >= gd2time($at2) ) {  // stop the animation 
     $abutton='Animate';    
     $na = 1; $metas='!--'; $metae='--';
  }
} else { 
  $metas='!--'; $metae='--'; $abutton='Animate'; $na=1;
}

$pt = date("H\ZdMY", gd2time($ct) - $dt);  
$nt = date("H\ZdMY", gd2time($ct) + $dt);  
//if ($nt > $ct0 ) { $nt = $ct0; }

if ( isset($pan) ) { 
 switch ($pan) { 
  case 'u':
    $lat1 += $h/6; $lat2 += $h/6; break;  
  case 'd':
    $lat1 -= $h/6; $lat2 -= $h/6; break;  
  case 'r':
    $lon1 += $w/6; $lon2 += $w/6; break;  
  case 'l':
    $lon1 -= $w/6; $lon2 -= $w/6; break;  
 }
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

$select[$var] = 'SELECTED';

$gdtime=$ct;

$lonstr="$lon1 $lon2";
$latstr="$lat1 $lat2";
if ($tseries == 1 ) {
  $lonstr=$lon0;
  $latstr=$lat0;
  $gdtime="$at1 $at2";
}

$pid=rand();

// pid is web server's, which has a small, fixed range. 
//$pid = getmypid(); 

$output=`export GADDIR=$GADDIR; export GASCRP=$GASCRP; export TERM=vt100; $GRADS -bl <<EOF
open $ctlf
set gxout $type[$tp]
set grads off
set parea 0.8 10.5 1 8.0
set mpdset hires
set xlopts 1 6.0 0.15
set ylopts 1 6.0 0.15
set time $gdtime
set clevs $clevs[$var]
*set ccols $cclrs[$var]
set lat $latstr
set lon $lonstr
d $var
draw title $title[$var] $gdtime
my-cbarn $unit[$ctl] 
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
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<${metas}META http-equiv="refresh" content="1; url=le.php?ctl=$ctl&var=$var&dt=$dt&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&tp=$tp&at1=$at1&at2=$at2&a=1"$metae>
<title> Modeling Global Microwave Emissivity </title>
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
                top:380px;
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
                top:535px;
                padding: 5px;
                width:170px;
                height:55px;
                background:#cfcfcf;
                border:1px solid #000;
                }

        #downloadbox {
                position: absolute;
                left: 795px;
                top:618px;
                padding: 1px;
                width:179px;
                height:30px;
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

        #backgox {
                position: absolute;
                left: 795px;
                top:700px;
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
<a href="le.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&ctl=$ctl&dt=$dt&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&P=u"> <img src="images/ua.gif" border=0> </a>
</td></tr>
<tr><td>
<a href="le.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&ctl=$ctl&dt=$dt&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&P=l"> <img src="images/la.gif" border=0> </a>
</td>
    <td> &nbsp; &nbsp; &nbsp; &nbsp; </td>
    <td>
<a href="le.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&ctl=$ctl&dt=$dt&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&P=r"> <img src="images/ra.gif" border=0> </a>
</td></tr>
<tr>
<td colspan=3 align=center>
<a href="le.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&ctl=$ctl&dt=$dt&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&P=d"> <img src="images/da.gif" border=0> </a>
</td>
</tr>
</table>
</center>
</div>
<div id="zoombox">
<center>
<table cellpadding=4>
<tr><td>
<a href="le.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&ctl=$ctl&dt=$dt&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&ZM=1"> <img src="images/zi.gif" border=0> </a></td></tr>
<tr><td>
<a href="le.php?var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&ct=$ct&ctl=$ctl&dt=$dt&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp&ZM=-1"> <img src="images/zo.gif" border=0> </a></td></tr>
</table>
</center>
</div>
<div id="typebox">
<form method="post" action="le.php">
<input type="hidden" name="var" value="$var">
<input type="hidden" name="lat1" value="$lat1">
<input type="hidden" name="lat2" value="$lat2">
<input type="hidden" name="lon1" value="$lon1">
<input type="hidden" name="lon2" value="$lon2">
<input type="hidden" name="T" value="$T">
<input type="hidden" name="Z" value="$Z">
<input type="hidden" name="ct" value="$ct">
<input type="hidden" name="ctl" value="$ctl">
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
<form method="post" action="le.php">
<input type="hidden" name="var" value="$var">
<input type="hidden" name="tp" value="$tp">
<input type="hidden" name="T" value="$T">
<input type="hidden" name="Z" value="$Z">
<input type="hidden" name="ct" value="$ct">
<input type="hidden" name="ctl" value="$ctl">
<input type="hidden" name="dt" value="$dt">
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
<form method="post" action="le.php">
<input type="hidden" name="lat1" value="$lat1">
<input type="hidden" name="lat2" value="$lat2">
<input type="hidden" name="lon1" value="$lon1">
<input type="hidden" name="lon2" value="$lon2">
<input type="hidden" name="T" value="$T">
<input type="hidden" name="Z" value="$Z">
<input type="hidden" name="ct" value="$ct">
<input type="hidden" name="dt" value="$dt">
<input type="hidden" name="z" value="$z">
<input type="hidden" name="E" value="$E">
<input type="hidden" name="W" value="$W">
<input type="hidden" name="S" value="$S">
<input type="hidden" name="N" value="$N">
<b>Select variable: </b>
<select name="var">
<option value="em7V" ${select['em7V']} > 6.9G V-pol
<option value="em7H" ${select['em7H']} > 6.9G H-pol
<option value="em11V" ${select['em11V']} > 10.65G V-pol
<option value="em11H" ${select['em11H']} > 10.65G H-pol
<option value="em19V" ${select['em19V']} > 18.7G V-pol
<option value="em19H" ${select['em19H']} > 18.7G H-pol
<option value="em24V" ${select['em24V']} > 23.8G V-pol
<option value="em24H" ${select['em24H']} > 23.8G H-pol
<option value="em37V" ${select['em37V']} > 36.5G V-pol
<option value="em37H" ${select['em37H']} > 36.5G H-pol
<option value="em89V" ${select['em89V']} > 89.0G V-pol
<option value="em89H" ${select['em89H']} > 89.0G H-pol
<option value="sm" ${select['sm']} > Soil moisture 
</select>
<input type="submit" value="Plot">
</form>
</div>

<div id="downloadbox">
<center>
<a href="data/$ctl/" target="_parent"><input type="submit" value="Download data" onclick="window.location='data/$ctl/'"></a>
</center>
</div>

<div id="exitbox">
<center>
<a href="le.php?var=$var&ct=$ct&ctl=$ctl&dt=$dt"><input type="submit" value="Reset" onclick="window.location='le.php?var=$var&ct=$ct&ctl=$ctl&dt=$dt'"></a>
</center>
</div>

<div id="backgox">
<center>
<!--a href="javascript:javascript:history.go(-1)"><input type="submit" value="Go back"></a-->
<a href="/PMM/" target="_parent"><input type="submit" value="Home" onclick="window.location='/PMM/'"></a>
</center>
</div>

<div id="tzbox">
<center>
<table> <tr>
<td> <a href="le.php?ctl=$ctl&ct=$pt&dt=$dt&var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&t=$t&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp"> 
<b>Previous time step << </b></a>  </td> 
<td>&nbsp; &nbsp;&nbsp; &nbsp;  </td>
<td> <a href="le.php?ctl=$ctl&ct=$nt&dt=$dt&var=$var&lat1=$lat1&lat2=$lat2&lon1=$lon1&lon2=$lon2&t=$t&z=$z&T=$T&Z=$Z&E=$E&W=$W&S=$S&N=$N&tp=$tp"> 
<b> >> Next time step </b></a>  </td> 
</tr> </table>
</center>
</div>

<div id="animbox">  
<center>
<form method="post" action="le.php">
<input type="hidden" name="var" value="$var">
<input type="hidden" name="lat1" value="$lat1">
<input type="hidden" name="lat2" value="$lat2">
<input type="hidden" name="lon1" value="$lon1">
<input type="hidden" name="lon2" value="$lon2">
<input type="hidden" name="T" value="$T">
<input type="hidden" name="ct" value="$ct">
<input type="hidden" name="ctl" value="$ctl">
<input type="hidden" name="dt" value="$dt">
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
