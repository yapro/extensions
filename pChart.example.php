<?php
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
$dirGraphicRoot = '/var/www/site.ru/graphic/';
$dirCurrentRoot = '/var/www/site.ru/img/analytics_calendar/';

$ex_slash = array_reverse(explode('/',$_SERVER['REQUEST_URI']));
if(!$ex_slash['0']){ exit; }

$ex_extension = explode('.png',$ex_slash['0']);
if(!$ex_extension['0'] || !isset($ex_extension['1'])){ exit; }

$imageName = $ex_extension['0'];
$ex_dot = explode('.',$imageName);

if(!$ex_dot['0'] || !is_numeric($ex_dot['0']) || !$ex_dot['1'] || !$ex_dot['2']){ exit; }

$indikator = (int)$ex_dot['0'];

$min_ex = explode('-', $ex_dot['1']);
$max_ex = explode('-', $ex_dot['2']);

$min = @mktime(0,0,0, $min_ex['1'], $min_ex['2'], $min_ex['0']);
$max = @mktime(0,0,0, $max_ex['1'], ($max_ex['2']+1), $max_ex['0']);

include_once("/var/www/site.ru/class/connect_mysql.php");

$data = $date = array();

if($q = mysql_query("SELECT forecast_real, time_publication FROM kfforex_economic_calendar WHERE 
indikator_id = '".$indikator."' AND time_publication BETWEEN '".$min."' AND '".$max."' ORDER BY time_publication")){
	$max_x = $min_x = 0;
	while($r = mysql_fetch_assoc($q)){
		if($r['forecast_real']){

			// если имеется второе значение, то берем его - оно идет через символ - запятая + пробел
			$ex = explode(', ', $r['forecast_real']);
			$v = $ex['1']? $ex['1'] : $ex['0'];

			$x = round(str_replace(',', '.', $v), 2);
			$data[] = $x;
			$date[] = date('m.d', $r['time_publication']);
			$max_x = ($x > $max_x)? $x : $max_x;
			$min_x = ($x < $min_x)? $x : $min_x;
		}
	}
}
//$data[] = round(-0.5678, 2);
//print_r($data); exit;
if(!$data || !$date){ exit; }

// добавляем доп. значения по оси Y для правильного отображения графика
$max = (abs($max_x) > abs($min_x))? abs($max_x) : abs($min_x);// необходимо для графика вида /img/analytics_calendar/27.2009-09-30.2010-03-01.png

// Standard inclusions
include($dirGraphicRoot."pChart/pData.class");
include($dirGraphicRoot."pChart/pChart.class");

// Dataset definition 
$DataSet = new pData;
$DataSet->AddPoint($data,"site.ru");
$DataSet->AddPoint($date,"Serie3");
$DataSet->AddAllSeries();
$DataSet->RemoveSerie("Serie3");
$DataSet->SetAbsciseLabelSerie("Serie3");// говорит о том, чтобы данные Serie3 использовать в качестве значений оси Х 

// Initialise the graph
$Test = new pChart(670,215);// ширина и высота полотна
$Test->setFixedScale( (($min_x<0)? -($max*1.5) : 0), (($max_x>0)? ($max*1.5) : 0.000000001) );// задает максимальные нижнюю и верхнюю отметки ($min_x*2), ($max_x*2)
$Test->drawGraphAreaGradient(102,173,131,50,TARGET_BACKGROUND);// оформление цвета

$Test->setFontProperties($dirGraphicRoot."Fonts/tahoma.ttf",8);
$Test->setGraphArea(50,10,655,190);// размеры отступов и размеры самого графика (слева, сверху, ширина, высота)
$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_ADDALL,213,217,221,TRUE,0,2,TRUE);
$Test->drawGraphAreaGradient(163,203,167,50);
$Test->drawGrid(4,TRUE,230,230,230,20);

// Draw the 0 line  void drawTreshold($Value,$R,$G,$B,$ShowLabel=FALSE,$ShowOnRight=FALSE,$TickWidth=4,$FreeText=NULL)  
$Test->drawTreshold(0,255,255,255,TRUE,TRUE);// нулевая линия

// Draw the bar chart
$Test->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),70);// прозрачность свечей

// Draw the legend (название первого графика site.ru)
$Test->setFontProperties($dirGraphicRoot."Fonts/tahoma.ttf",8);
$Test->drawLegend(575,5,$DataSet->GetDataDescription(),236,238,240,52,58,82);// отступы (слева, сверху, ...)

// Render the picture
$Test->addBorder(2);
$Test->Render($dirCurrentRoot.$imageName.time().'.png');
?>