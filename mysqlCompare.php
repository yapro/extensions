<?php
/*

Examples for use:

1. show some database:

http://pma-lebedenko.ru/mysqlCompare.php?dbname=DatabaseName

2. compary two databases:

http://extensions.local/mysqlCompare.php?remote_script=http://site.ru/mysqlCompare.php

*/
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
$dbms = 'mysql';
$dbhost = 'localhost';
$dbport = '';
$dbname = isset($_GET['dbname'])? $_GET['dbname'] : 'mysql';
$dbuser = 'root';
$dbpasswd = '';

/************************************************************************************/

function config_20101117($str=''){
	
	@header("content-type:text/html; charset=UTF-8");
	@header("Expires: ".gmdate("D, d M Y H:i:s", (time()-777) )." GMT");// отменяем всяческое кеширование
	@header("Last-Modified: ".gmdate("D, d M Y H:i:s", time() )." GMT");// делаем дату модификации новой
	header("HTTP/1.0 503 Service Unavailable");
	echo '<H2 style="text-align: center; padding-top: 150px;">'.($str? $str : 'Проблемы в настройках').'</H2>';
	exit;
	
}

$dbcnx = @mysql_connect($dbhost.($dbport?$dbport:''),$dbuser,$dbpasswd);
if(!$dbcnx){ $error_connect_mysql_server++; }
if(!@mysql_select_db($dbname,$dbcnx)){ $error_connect_mysql_db++; }

if($error_connect_mysql_server || $error_connect_mysql_db){
	
	$info = '<br>Информацию о хостинге можно <a href="https://www.nic.ru/whois/?query='.$_SERVER["SERVER_ADDR"].'">узнать здесь &#8250;</a>';
	
	if(mysql_errno()=='1040'){
		
		config_20101117('База данных временно недоступна. Слишком много подключений или нехватка места на жестком диске'.$info);
		
	}else if($error_connect_mysql_server){
		
		config_20101117('В настоящий момент сервер базы данных недоступен'.$info);
		
	}else{
		
		config_20101117('В настоящий момент база данных недоступна'.$info);
		
	}
}

mysql_query("SET NAMES UTF8");

/************************************************************************************/

function SHOW_TABLES_20111029(){
	$result = mysql_query('SHOW TABLES');
	while($i = mysql_fetch_array($result)){
		$result_columns = mysql_query('SHOW FULL COLUMNS FROM `'.$i[0].'`');
		while($j = mysql_fetch_array($result_columns)) {
			$current_base[$i[0]][$j['Field']] = array(
				'field'		=> $j['Field'],
				'type'		=> $j['Type'],
				'null'		=> $j['Null'],
				'key'		=> $j['Key'],
				'default'	=> $j['Default'],
				'extra'		=> $j['Extra'],
				'comment'		=> $j['Comment']
			);
		}
	}
	if($q = mysql_query('SHOW TABLE STATUS')){
		while($r = mysql_fetch_assoc($q)){
			$current_base[$r['Name']]['LEBNIK_TABLE_Comment'] = $r['Comment'];
		}
	}
	return $current_base;
}

/************************************************************************************/

$current_base = SHOW_TABLES_20111029();

$remote_base  =  array();

/************************************************************************************/

// Если скрипт вызван удаленно, просто отдаем получившийся массив 
if(isset($_GET['remote'])) {
	echo serialize($current_base);
	exit();
}

if($_GET['remote_script']){
	$remote_data = @file_get_contents($_GET['remote_script'].'?remote');
	if($remote_data){
		$remote_base = unserialize($remote_data);
	}else{
		config_20101117('не смог получить данные с удаленного сервера');
	}
}

/************************************************************************************/

if($_GET['dbl']){
	mysql_select_db($_GET['dbl']) or die(mysql_error());
	$remote_base = SHOW_TABLES_20111029();
}

/************************************************************************************/

if($remote_base){// проверим, может быть в удаленной базе есть таблицы, которых нет в текущей
	foreach ($remote_base as $table=>$data) {
		if(!$current_base[$table]){	
			$current_base[$table] = array();
		}
	}
}

$ret = '<table border="0" width="100%" class="styleTable5" style="margin-bottom:25px">';

$ret .= $remote_base?'<tr><td colspan="6"><b>База '.htmlspecialchars($dbname).'</b></td><td colspan="6"><b>База удаленная</b></td></tr>':'';

$exist = array();

foreach ($current_base as $table=>$data) {
	
	$checked[ $table ] = true;

	$str_table = '';

	$str_fields = false;
	
	$str_table .= '<tr><td colspan="12"><p style="padding-top: 25px;">таблица <b>'.$table.' : </b>'.$data['LEBNIK_TABLE_Comment'].'</p></td></tr>';

	unset($current_base[$table]['LEBNIK_TABLE_Comment']);
	unset($remote_base[$table]['LEBNIK_TABLE_Comment']);

	$str_table .= '<tr class="yaproSortTR">
		<td>Comment</td>
		<td>Field</td>
		<td>Type</td>
		<td>Null</td>
		<td>Key</td>
		<td>Default</td>
		<td>Extra</td>
		'.($remote_base?'
			<td>Comment</td>
			<td>Field</td>
			<td>Type</td>
			<td>Null</td>
			<td>Key</td>
			<td>Default</td>
			<td>Extra</td>
		':'').'
	</tr>';

	foreach ($data as $field=>$fdata) {

		$color = ($current_base[$table][$field] != $remote_base[$table][$field])? 'FFFFCC':'FFF';//CCFFCC

		if(!$remote_base){
			$color = 'FFF';
		}elseif($color != 'FFF'){
			$str_fields = true;
		}

		if(isset($current_base[$table][$field]) && isset($remote_base[$table][$field])) {
			
			$str_table .= '<tr style="background-color:#'.$color.'">
					<td class="comment">'.str_replace("\n", '<br>', $current_base[$table][$field]['comment']).'</td>
					<td>'.$current_base[$table][$field]['field'].'</td>
					<td>'.$current_base[$table][$field]['type'].'</td>
					<td>'.$current_base[$table][$field]['null'].'</td>
					<td>'.$current_base[$table][$field]['key'].'</td>
					<td>'.$current_base[$table][$field]['default'].'</td>
					<td>'.$current_base[$table][$field]['extra'].'</td>
				'.($remote_base?'
					<td class="comment">'.str_replace("\n", '<br>', $remote_base[$table][$field]['comment']).'</td>
					<td>'.$remote_base[$table][$field]['field'].'</td>
					<td>'.$remote_base[$table][$field]['type'].'</td>
					<td>'.$remote_base[$table][$field]['null'].'</td>
					<td>'.$remote_base[$table][$field]['key'].'</td>
					<td>'.$remote_base[$table][$field]['default'].'</td>
					<td>'.$remote_base[$table][$field]['extra'].'</td>
				':'').'
			</tr>';

		} elseif (isset($current_base[$table][$field]) && !isset($remote_base[$table][$field])) {
			
			$color = $remote_base?'CCFFCC':'FFF';

			if($remote_base && $color != 'FFF'){
				$str_fields = true;
			}

			$str_table .= '<tr style="background-color:#'.$color.'">
					<td class="comment">'.str_replace("\n", '<br>', $current_base[$table][$field]['comment']).'</td>
					<td>'.$current_base[$table][$field]['field'].'</td>
					<td>'.$current_base[$table][$field]['type'].'</td>
					<td>'.$current_base[$table][$field]['null'].'</td>
					<td>'.$current_base[$table][$field]['key'].'</td>
					<td>'.$current_base[$table][$field]['default'].'</td>
					<td>'.$current_base[$table][$field]['extra'].'</td>
				'.($remote_base?'
					<td class="comment">'.str_replace("\n", '<br>', $remote_base[$table][$field]['comment']).'</td>
					<td>'.$remote_base[$table][$field]['field'].'</td>
					<td>'.$remote_base[$table][$field]['type'].'</td>
					<td>'.$remote_base[$table][$field]['null'].'</td>
					<td>'.$remote_base[$table][$field]['key'].'</td>
					<td>'.$remote_base[$table][$field]['default'].'</td>
					<td>'.$remote_base[$table][$field]['extra'].'</td>
				':'').'
			</tr>';
		}

		$exist[ $table ][ $field ] = true;

	}
	
	$color = 'CCFFFF';
	foreach ($remote_base[$table] as $field=>$fdata) {
		if(!isset($exist[ $table ][ $field ])) {

			$str_fields = true;

			$str_table .= '<tr style="background-color:#'.$color.'">
					<td class="comment">'.str_replace("\n", '<br>', $current_base[$table][$field]['comment']).'</td>
					<td>'.$current_base[$table][$field]['field'].'</td>
					<td>'.$current_base[$table][$field]['type'].'</td>
					<td>'.$current_base[$table][$field]['null'].'</td>
					<td>'.$current_base[$table][$field]['key'].'</td>
					<td>'.$current_base[$table][$field]['default'].'</td>
					<td>'.$current_base[$table][$field]['extra'].'</td>

					<td class="comment">'.str_replace("\n", '<br>', $remote_base[$table][$field]['comment']).'</td>
					<td>'.$remote_base[$table][$field]['field'].'</td>
					<td>'.$remote_base[$table][$field]['type'].'</td>
					<td>'.$remote_base[$table][$field]['null'].'</td>
					<td>'.$remote_base[$table][$field]['key'].'</td>
					<td>'.$remote_base[$table][$field]['default'].'</td>
					<td>'.$remote_base[$table][$field]['extra'].'</td>
				</tr><tr style="background-color:#FFF">
					<td colspan="12"><textarea onclick="this.select()" style="width:100%">'.htmlspecialchars('ALTER TABLE `'.$table.'` ADD `'.$field.'` '.$fdata['type'].' '.(($fdata['null']==='YES')?'NULL':'').' '.($fdata['default']? 'default \''.$fdata['default'].'\'':'').' '.$remote_base[$table][$field]['extra'].' ').'</textarea></td>
				</tr>';
		}
	}

	if($str_fields || !$remote_base){
		$ret .= $str_table;
	}

	//echo '<pre>'.print_r(array_diff_key($current_base[$table], $remote_base[$table])).'</pre>';
	
}
$ret .= '</table>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru-ru" xml:lang="ru-ru">
<head>
	<title>MySQL compare by Lebnik</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Language" content="ru">
	<link href="http://yapro.ru/uploads/Files/Typography/style.css" type="text/css" rel="stylesheet">
</head>
<body bgcolor="white">
<style>
TD {
	color: #000000;
	font: 12px Arial,Helvetica,sans-serif;
}
.styleTable5 TD {
	padding: 0.3em 0.7em;
}

.yaproSortTR {
	background-color:#E2E8E5;
}
.comment {
	text-align: right;
}
</style>
<h2 style="text-align:center; margin:15px 0;">Структура таблиц баз<?php echo $remote_base?' (одинаковые таблицы не показаны)':'ы'; ?></h2>
<?php
echo $ret;
?>
</body>
</html>
