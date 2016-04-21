<?php
/* Flow Explorer. $Id: fe.php,v 1.2 2010/04/08 21:51:51 ytian Exp ytian $
*/

$basedir='/data/web/html/CREST/global'; 
$rrdfile="$basedir/global.rrd";
// sat and gauge from Kavango 
$kbasedir='/data/web/html/CREST/Kavango'; 
$obsrrd="$kbasedir/rundu.rrd";
$satrrd="$kbasedir/sat-rundu.rrd";
$rrdtool="/data/web/html/CREST/rrdtool/bin/rrdtool";

$twindow = 120*24*60*60;  // time window to show data 

// only works with php 5.1 and up
//date_default_timezone_set('UTC');

// start and end time 
$ct0 = time(); 
$tw = $_REQUEST["tw"]; if (!isset($tw)) { $tw = $twindow; }
$et = $_REQUEST["et"]; if (!isset($et)) { $et = $ct0; }
$st = $_REQUEST["st"]; if (!isset($st)) { $st = $et - $tw ; }
$pan=$_REQUEST["P"]; 
$zoom=$_REQUEST["ZM"]; 
$act=$_REQUEST["act"]; 

// clean tainted variables for malicious user input: 
// only alphanumeric, '+', '-', '.' allowed. 
$pattern = '/[^a-zA-Z0-9\+\-\.]/';
$tw = preg_replace($pattern, '', $tw);
$et = preg_replace($pattern, '', $et);
$st = preg_replace($pattern, '', $st);
$pan = preg_replace($pattern, '', $pan);
$zoom = preg_replace($pattern, '', $zoom);
$act = preg_replace($pattern, '', $act);

switch ($pan) { 
  case 'r':
    $st += $tw/6; $et += $tw/6; break;  
  case 'l':
    $st -= $tw/6; $et -= $tw/6; break;  
}

switch ($zoom) { 
  case '1':
    $st += $tw/6; break;  
  case '-1':
    $st -= $tw/6; break;  
}

$st= round($st, 0); 
$et= round($et, 0); 
$tw= $et-$st; 
 
if ( $act == 'plot') {

$mgrid = round($tw/(30*24*60*60), 0);  //major and minor grid spacing, in days
if ( $mgrid < 1 ) { $mgrid = 1; } 
$Mgrid = $mgrid*3; 

passthru("export LD_LIBRARY_PATH=/data/web/html/CREST/rrdtool/lib; $rrdtool graph - \
 --imgformat=PNG \
 --start $st --end $et --step 86400\
 --title=\"CREST flowrate at Rundu (m^3/s)\" \
 --rigid --base=1000 --height=200 --width=630 \
 --alt-autoscale-max --lower-limit=0 \
 --vertical-label=\"flowrate (m^3/s)\" \
 --right-axis 0.024:0 \
 --right-axis-label \"Rainrate(mm/day)\" \
 --right-axis-format \"%4.1lf\" \
 --x-grid DAY:$mgrid:DAY:$Mgrid:DAY:$Mgrid:0:%b/%d/%y \
 DEF:r=\"$rrdfile\":rain:AVERAGE \
 DEF:f=\"$rrdfile\":flow:AVERAGE \
 DEF:of=\"$obsrrd\":flow:AVERAGE \
 DEF:sf=\"$satrrd\":flow:AVERAGE \
 CDEF:rain=r,1000,* \
 CDEF:ssf=sf,0.05,* \
 AREA:rain#0000CF:\"Rain\" \
 LINE2:f#00DD00:\"Model flowrate\" \
 LINE2:of#DD0000:\"Obs flowrate\" \
 LINE1:ssf#FF8C00:\"DFO Riverwatch flowrate\" ");

} else { 

$html = <<<HTML
<html>
<head>
<META HTTP-EQUIV="Content-Style-Type" CONTENT="text/css">
<META HTTP-EQUIV="expires" CONTENT="Wed, 26 Feb 1997 08:21:57 GMT">
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<title> Flood Monitoring with 3B42RT and CREST </title>
<style type="text/css">
 A:link {text-decoration: none}
 A:visited {text-decoration: none}
 A:active {text-decoration: none}
 A:hover {text-decoration: none; color: green; }

     #leftbox { position: absolute; left:0px; top:0px;
                width:785px; background:#fff; border:1px solid #000; }

     #panbox { position: absolute; left: 795px; top:5px;
                padding: 5px; width:170px; height:120px; background:#fff;
                border:1px solid #000; }

     #zoombox { position: absolute; left: 795px; top:155px; padding: 5px;
                width:170px; height:100px; background:#fff;
		 border:1px solid #000; }

</style>
</head>

<body>
<div id="leftbox">
<img src="fe.php?act=plot&tw=$tw&P=$pan&ZM=$zoom&st=$st&et=$et" 
 border=0 align=right> 
</div>
<div id="panbox">
<br><br>
<center>
<table>
<tr><td>
<a href="fe.php?P=l&tw=$tw&st=$st&et=$et"><img src="images/la.gif" border=0></a> 
</td>
    <td> &nbsp; &nbsp; &nbsp; &nbsp; </td>
    <td>
<a href="fe.php?P=r&tw=$tw&st=$st&et=$et"><img src="images/ra.gif" border=0></a>
</td></tr>
</table>
</center>
</div>
<div id="zoombox">
<center>
<table cellpadding=4>
<tr><td>
<a href="fe.php?tw=$tw&ZM=1&st=$st&et=$et"><img src="images/zi.gif" border=0></a> 
</td></tr>
<tr><td>
<a href="fe.php?tw=$tw&ZM=-1&st=$st&et=$et"><img src="images/zo.gif" border=0></a>
</td></tr>
</table>
</center>
</div>

</body></html>
HTML;

echo $html; 

}

?>
