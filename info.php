<?php
//--------------------------------------------------------------------------------------------------------
if(isset($_GET['check_file_get_contents'])){
	echo file_get_contents("http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?echo_file_get_contents');
	exit;
}
if(isset($_GET['echo_file_get_contents'])){ echo 'ok'; exit; }
//--------------------------------------------------------------------------------------------------------

if(isset($_GET['phpinfo']) && function_exists('phpinfo')){ phpinfo(); exit; }

// узнаем размер б.д.
if( $sqlid = @mysql_query("SHOW TABLE STATUS FROM ".$dbname) ){
  while($r=mysql_fetch_array($sqlid)){
    $dbsize+=$r['Data_length'];
  }
}

// перевод числа из байтов в более читабельный вид
function filesize_($size=0, $round=2){
  $type = array('Байт', 'Кб',  'Мб', 'Гб', 'Тб', 'Пб');
  $i = 0;
  for (; $size>1024; $size=$size/1024){$i++;}
  if(empty($i)){$i='0';}
  //return round($size, $round).' '.noun(round($size,0), $type[$i], $type[$i].'а', $type[$i]);
  return round($size, $round).' '.$type[$i];
}

// Свободное дисковое пространство:
$free_hd = filesize_(@disk_free_space("/"));

// смотрим установки
$php_set = get_defined_constants();

$web_server =  preg_replace ('/php(.*)/si', '', $_SERVER["SERVER_SOFTWARE"]);
if(strlen($web_server)>40){ $web_server = mb_substr($web_server, 0, 40).' ...<a href="'.$_SERVER['SYSTEM']['URL_MODULE_PATH'].'?phpinfo">Подробнее &raquo;</a>'; }

//-------------------------------------------------------------------------------------------------------
$root = $_SERVER['DOCUMENT_ROOT'].'/';
$root_read = false;
if($dir = @dir($root)){
	while ($file = $dir-> read()){
		if($file != '.' && $file != '..' && (is_dir($root.$file) || is_file($root.$file)) ){
			$root_read = true;
		}
	}
	$dir->close();
}
//-------------------------------------------------------------------------------------------------------
// установленные кодировки
ob_start();
@passthru('locale -a | grep ru_RU');
$command_str = ob_get_contents();
ob_end_clean();
if($command_str){
	$arr = explode("\n", $command_str);
	$installed_locale = '<ul>';
	foreach($arr as $str){
		if($str && $str!='ru_RU'){
			$installed_locale .= '<li>'.$str.'</li>';
			$check_utf8[strtolower($str)]++;
		}
	}
	$installed_locale .= '</ul>';
}

$default_charset = ini_get('default_charset');
$tar = @exec('which tar');
$php = @exec('which php');

$temp = array_reverse(explode('.', $_SERVER['HTTP_HOST']));
$mx = getmxrr($temp['1'].'.'.$temp['0'], $mxhostsarr);// получает MX-записи, соответствующие имени данного Internet-хоста.

//----------------------------------------------------------------------------------------------------------------------

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru-ru" xml:lang="ru-ru">
<head>
	<title>Server Info</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Language" content="ru">
	<link href="http://yapro.ru/uploads/Files/Typography/style.css" type="text/css" rel="stylesheet">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>
<body bgcolor="white">
<a href="'.$_SERVER['SYSTEM']['URL_MODULE_PATH'].'?phpinfo">Информация</a>
<table border="0" cellspacing="1" width="100%" bgcolor="#ECECEC" class="overflowTable">
<tr class="yaproSortTR"><td width="50%"><b>Показатель</b></td><td><b>Значение</b></td></tr>
<tr><td><p><b>WEB-сервер:</b></p></td><td><p>'.$web_server.'</p></td></tr>
<tr><td><p><b>ОС:</b></p></td><td><p>'.PHP_OS.'</p></td></tr>
<tr><td><p><b>Ваш IP:</b></p></td><td><p>'.$_SERVER['REMOTE_ADDR'].'</p></td></tr>
<tr><td><p><b>IP сервера:</b></p></td><td><p>'.$_SERVER["SERVER_ADDR"].'</p></td></tr>
<tr><td><p><b>Время на сервере:</b></p></td><td><p id="ServerClock">'.date("H:i:s").'&nbsp;&nbsp;'.date("d.m.Y").'</p></td></tr>
<tr><td title="post_max_size"><p><b>Максимальный размер POST-даных:</b></p></td><td><p>'.ini_get('post_max_size').'</p></td></tr>
<tr><td title="upload_max_filesize"><p><b>Максимальный размер загр.файла:</b></p></td><td><p>'.ini_get('upload_max_filesize').'</p></td></tr>
<tr><td><p><b>Свободное дисковое пространство:</b></p></td><td><p>'.(((int)$free_hd)?$free_hd:'нет данных').'</p></td></tr>
<tr><td title="memory_limit"><p><b>Максимальная память ОЗУ:</b></p></td><td><p>'.ini_get('memory_limit').'</p></td></tr>
<tr><td><p><b>PHP версия:</b></p></td><td><p>'.$php_set['PHP_VERSION'].(($php_set['PHP_VERSION'] && $php_set['PHP_VERSION'] < 4.3)?' <font color="red">обновите до 4.3</font>':'').'</p></td></tr>
<tr><td><p><b>MySQL версия:</b></p></td><td><p>'.mysql_result(mysql_query("SELECT VERSION()"), 0).'</p></td></tr>
<tr><td><p><b>Zend версия:</b></p></td><td><p>'.zend_version().'</p></td></tr>
<tr><td><p><b>mb_substr:</b></p></td><td><p>'.(function_exists('mb_substr')?'определена - OK':'<font color="red">не определена - FATAL ERROR</font>').'</p></td></tr>
<tr><td><p><b>iconv:</b></p></td><td><p>'.(function_exists('iconv')?'определена - OK':'<font color="#FF9900">не определена</font>').'</p></td></tr>
<tr><td><p><b>locale UTF-8:</b></p></td><td><p>'.(($check_utf8['ru_ru.utf-8'] || $check_utf8['ru_ru.utf8'] || $default_charset=='' || $default_charset=='UTF-8')?'установлена - OK':'<font color="#FF9900">не установлена - WARNING</font>').'</p></td></tr>
<tr><td><p><b>default_charset (кодировка по умолчанию):</b></p></td><td><p>'.(($default_charset=='' || $default_charset=='UTF-8')? 'UTF-8 - ОК':'<font color="red">'.$default_charset.' - ERROR</font>').'</p></td></tr>
       	'.($installed_locale?'<tr class="no"><td><p><b>locale дополнительные:</b></p></td><td><p>'.$installed_locale.'</p></td></tr>':'').'
<tr><td title="проверяет, совпадает ли owner/владелец текущего скрипта с owner файла, которым оперирует функция работы с файлами"><p><b>safe_mode (безопасный режим):</b></p></td><td><p>'.(ini_get('safe_mode')?'<font color="red">включен - ERROR</font>':'отключен - OK').'</p></td></tr>
<tr><td><p><b>register_globals (глобальные переменные):</b></p></td><td><p>'.(ini_get('register_globals')?'<font color="red">включены - ERROR</font>':'отключены - OK').'</p></td></tr>
<tr><td><p><b>display_errors (сообщения об ошибках):</b></p></td><td><p>'.(ini_get('display_errors')?'<font color="red">включены - WARNING</font>':'отключены - OK').'</p></td></tr>
<tr><td><p><b>use_trans_sid (генерируемые session id автоматически добавляемые к URI):</b></p></td><td><p>'.(ini_get('session.use_trans_sid')?'<font color="red">включены - ERROR</font>':'отключены - OK').'</p></td></tr>
<tr><td><p><b>getmxrr (видимость собственных MX-записей):</b></p></td><td><p>'.($mx?'видимы - ОК':'<font color="red">не видимы - ERROR</font>').'</p></td></tr>
<tr><td><p><b>поддержка системных команд (exec):</b></p></td><td><p>'.(($tar || $php)?'включена - OK':'<font color="#FF9900">отключена</font>').'</p></td></tr>
<tr><td><p><b>путь к php (exec):</b></p></td><td><p>'.(!$php?'<font color="#FF9900">не известен</font>': $php.' - OK').'</p></td></tr>
<tr><td><p><b>путь к tar (exec):</b></p></td><td><p>'.(!$tar?'<font color="#FF9900">не известен</font>': $tar.' - OK').'</p></td></tr>
<tr><td><p><b>полный путь к системе администрирования:</b></p></td><td><p>'.$_SERVER['SYSTEM']['PATH'].'</p></td></tr>
<tr><td><p><b>просмотр корневой директории сайта:</b></p></td><td><p>'.($root_read?'OK': '<font color="#FF9900">невозможен</font>').'</p></td></tr>
<tr><td><p><b>Размер базы данных:</b></p></td><td><p>'.filesize_($dbsize).'</p></td></tr>
<tr><td><p title="allow_url_fopen = On"><b>Удаленные данных через file_get_contents:</b></p></td><td><p id="file_get_contents">идет проверка данных...</p></td></tr>
</table>
<script language="javascript">
var Time = new Date("'.substr(date('r'),0,-6).'");
</script>';
?>
<style type="text/css">

</style>
<script type="text/javascript">
function clock(){
	Time.setTime(Date.parse(Time)+1000);// дата + 1 секунда
	var hour   = Time.getHours();
	var minute = Time.getMinutes();
	var second = Time.getSeconds();

	var Day    = Time.getDate();
	var Month  = Time.getMonth()+1;
	var Year   = Time.getFullYear();

	if( hour < 10 ){ hour = "0" + hour; }
	if( minute < 10 ){ minute = "0" + minute; }
	if( second < 10 ){ second = "0" + second; }

	if( Day < 10 ){ Day = "0" + Day; }
	if( Month < 10 ){ Month = "0" + Month; }

	document.getElementById("ServerClock").innerHTML = hour + ":" + minute + ":" + second+"&nbsp;&nbsp;"+Day + ":" + Month + ":" + Year;
}
setInterval( "clock()", 1000 );

$(document).ready(function(){
	
	mouseMovementsClass("overflowTable");
	
	$.get(document.location.pathname+"?check_file_get_contents", function(r){
		
		$("#file_get_contents").html( (r && r=="ok")? 'OK' : '<font color="#FF9900">выключены - WARNING</font>' );
		
	});
	
});
</script>
</body>
</html>
