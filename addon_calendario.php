<?php
/*
addon_calendario.php : calendar add-on for miniBB search module.
This file is part of miniBB. miniBB is free discussion forums/message board software, without any warranty. See COPYING file for more details. Copyright (C) 2006, 2008 Juan Carlos Udias (udias.com), Paul Puzyrev (minibb.net)
Latest File Update: 2008-Mar-11
*/

/* Options */

$tipo_semana = 1; // Displays full or short Day titles (1: short 2: full)
$tipo_mes = 1; // Displays full or short Month titles (1: short 2: full)
$sunBackground='FFC5C1'; //background colouring weekend days
$startDay=1; //if Week begins from Sunday, set to 0; if from Monday, set to 1
$searchTopics=TRUE; //if false, script will display all messages posted at the defined date; else only new topics will be displayed

//Así empieza la semana en lunes --------------Así empieza la semana en domingo
$SEMANAABREVIADA[0] = 'Sun'; // $SEMANAABREVIADA[0] = 'Dom';
$SEMANAABREVIADA[1] = 'Mon'; // $SEMANAABREVIADA[1] = 'Lun';
$SEMANAABREVIADA[2] = 'Tue'; // $SEMANAABREVIADA[2] = 'Mar';
$SEMANAABREVIADA[3] = 'Wed'; // $SEMANAABREVIADA[3] = 'Mie';
$SEMANAABREVIADA[4] = 'Thu'; // $SEMANAABREVIADA[4] = 'Jue';
$SEMANAABREVIADA[5] = 'Fri'; // $SEMANAABREVIADA[5] = 'Vie';
$SEMANAABREVIADA[6] = 'Sat'; // $SEMANAABREVIADA[6] = 'Sáb';

/* --End of options */

include('./setup_options.php');
include($pathToFiles.'/lang/'.$lang.'.php');

//Output header
echo <<<out
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head><title>Calendar</title>
<link href="{$main_url}/bb_{$skin}_style.css" type="text/css" rel="stylesheet" />
</head>
<body class="gbody">
out;

$months=explode(':', $l_months);
$i=1;
foreach($months as $m) {
$MESABREVIADO[$i]=$m;
$i++;
}

if(isset($_GET['dia'])) $dia=$_GET['dia']+0; elseif(isset($_POST['dia'])) $dia=$_POST['dia']+0; else $dia=date('d');
if(isset($_GET['mes'])) $mes=$_GET['mes']+0; elseif(isset($_POST['mes'])) $mes=$_POST['mes']+0; else $mes=date('n');
if(isset($_GET['ano'])) $ano=$_GET['ano']+0; elseif(isset($_POST['ano'])) $ano=$_POST['ano']+0; else $ano=date('Y');

$TotalDiasMes = date('t',mktime(0,0,1,$mes,$dia,$ano)); 
$DiaSemanaEmpiezaMes = date('w',mktime(0,0,1,$mes,1,$ano));
$DiaSemanaTerminaMes = date('w',mktime(0,0,1,$mes,$TotalDiasMes,$ano)); 

//echo "Total days: $TotalDiasMes Start day: $DiaSemanaEmpiezaMes End day: $DiaSemanaTerminaMes Day: $dia Month: $mes Year: $ano";

$AnoAnteriorAno = $ano-1;
$AnoSiguienteAno = $ano+1;

if($mes == 1){
$MesAnterior = 12; 
$MesSiguiente = $mes + 1; 
$AnoAnterior = $ano - 1; 
$AnoSiguiente = $ano; 
}

elseif($mes == 12){
$MesAnterior = $mes - 1; 
$MesSiguiente = 1; 
$AnoAnterior = $ano; 
$AnoSiguiente = $ano + 1;
}

else{
$MesAnterior = $mes - 1; 
$MesSiguiente = $mes + 1; 
$AnoAnterior = $ano; 
$AnoSiguiente = $ano; 
}

echo <<<out
<table class="tbTransparent" style="width:1%">
<tr>
<td style="width:1%"><span class="txtNr"><a href="{$main_url}/addon_calendario.php?mes={$mes}&amp;ano={$AnoAnteriorAno}">&laquo;</a></span></td>
<td style="width:1%"><span class="txtNr"><a href="{$main_url}/addon_calendario.php?mes={$MesAnterior}&amp;ano={$AnoAnterior}">&lt;</a></span></td>
<td style="width:1%;text-align:center;white-space:nowrap;" colspan="3"><span class="txtNr" style="font-weight:bold">{$MESABREVIADO[$mes]} - {$ano}</span></td>
<td style="width:1%;text-align:right"><span class="txtNr"><a href="{$main_url}/addon_calendario.php?mes={$MesSiguiente}&amp;ano={$AnoSiguiente}">&gt;</a></span></td>
<td style="width:1%;text-align:right"><span class="txtNr"><a href="{$main_url}/addon_calendario.php?mes={$mes}&amp;ano={$AnoSiguienteAno}">&raquo;</a></span></td>
</tr>
<tr>
out;

$i=$startDay;
$a=0;
while($a<7){
echo "<td class=\"caption4\" style=\"font-weight:normal;padding:1px;text-align:center\">{$SEMANAABREVIADA[$i]}</td>";
$a++;
if($i+1>=7) $i=0; else $i++;
}

echo '</tr><tr>'; 

/* display calendar */
$cd=1;
$trc=0;
$left=$startDay;
$startCount=FALSE;

while($cd<=$TotalDiasMes){

if($left>6) $left=0;

if($trc>6) { echo '</tr><tr>'; $trc=0; }

if($left==$DiaSemanaEmpiezaMes and !$startCount) $startCount=TRUE;

if(!$startCount) echo '<td class="caption1" style="padding:1px;">&nbsp;</td>';
else{

//current day - mark bold
if($cd == date('d') && $mes == date('n') && $ano == date('Y'))
$currBold=array('<strong>', '</strong>');
else $currBold=array('', '');

//saturdays, sundays - mark background red
if($left!=0 and $left!=6) $bgClr=''; else $bgClr='background-color:#'.$sunBackground.';';

if($searchTopics) $whereSearch='where=1&amp;'; else $whereSearch='';

echo <<<out
<td class="caption1" style="padding:0px;text-align:center;{$bgClr}"><a href="{$main_url}/{$indexphp}action=search&amp;sDay={$cd}&amp;sMonth={$mes}&amp;sYear={$ano}&amp;eDay={$cd}&amp;eMonth={$mes}&amp;eYear={$ano}&amp;{$whereSearch}searchGo=1" target="_top">{$currBold[0]}{$cd}{$currBold[1]}</a></td>
out;

$cd++;
}

$left++;
$trc++;

}


while($trc<7){
echo '<td class="caption1" style="padding:1px;">&nbsp;</td>';
$trc++;
}
echo '</tr>';
echo '</table></body></html>';

?>