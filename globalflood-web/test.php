
<?php

//echo phpinfo(); 

$GADDIR='/home/yudong/grads-1.9b4/data';
$GASCRP='/home/yudong/grads-1.9b4/scripts';
$GRADS='/www/lis/PRECIP/ARCTAS/CREST/global/gradsc';
$varF='/www/lis/PRECIP/ARCTAS/CREST/global/out-runoff.ctl';
$imgd='/www/lis/PRECIP/ARCTAS/CREST/global/plots';
$var='runoff';
$clevs='0 10 100 1000 10000 100000';
$TIME='3Z10Jan2010';


$output=`export GADDIR=$GADDIR; export GASCRP=$GASCRP; export TERM=vt100; $GRADS -bl >/tmp/a.log 2>&1 <<EOF
open $varF
set gxout shaded
set grads off
set mpdset hires
set xlopts 1 6.0 0.15
set ylopts 1 6.0 0.15
set time $TIME
set clevs $clevs
d $var
draw title $var $TIME
cbarn
printim $imgd/$var.gif gif white x1000 y800
quit
EOF`;

echo $output; 

echo "done"; 

?>

