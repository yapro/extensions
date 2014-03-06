<?php
$charset = 'windows-1251';// если не указывать, то по-умолчанию: UTF-8

@header('content-type:text/html; charset='.$charset);

// скрипт дает возможность редактировать редиректы

$user_name = 'SphinxR';// login
$user_pass = 's3538Z';// password

function authenticate($s = '¬ы должны ввести корректный логин и пароль дл€ получени€ доступа к ресурсу') {

  header('WWW-Authenticate: Basic realm="Test Authentication System"');
  header('HTTP/1.0 401 Unauthorized');
  echo $s."\n";
  exit;

}

if ( !isset($_SERVER['PHP_AUTH_USER']) ) {

  authenticate();

} else if($_SERVER['PHP_AUTH_USER'] !== $user_name){

  authenticate('Login:error');

} else if($_SERVER['PHP_AUTH_PW'] !== $user_pass){

  authenticate('Password:error');

}

require_once("../app/init.php");
require_once("../inc/db.php");

//----------------------------------------------------------------------------

$result = '';

if( isset($_POST['id']) && $_POST['id'] && isset($_POST['status']) && isset($_POST['correctly']) ){

  if( $db->q('UPDATE '.$db->getPrefix().'search_redirects SET
    status = '.(int)$_POST['status'].',
    correctly = \''.( $_POST['correctly']? $db->escape(trim($_POST['correctly'])) : '' ).'\'
  WHERE id = '.$_POST['id']) ){

    $result = '»справлени€ сохранены';

  }else{

    $result = 'ќшибка сохранени€ данных';

  }
}

//----------------------------------------------------------------------------

function status($value = 0){
  return '<select name="status">'.str_replace('="'.$value.'"', '="'.$value.'" selected', '
    <option value="0">новый</option>
    <option value="1">проверенный</option>
    <option value="2">отключен</option>
  ').'</td>';
}

//----------------------------------------------------------------------------

$status = $_GET['status']? (int)$_GET['status'] : 0;

$url = $_SERVER['PHP_SELF'].'?status='.$status;

//----------------------------------------------------------------------------

$sql = 'FROM '.$db->getPrefix().'search_redirects WHERE status = '.$status;

//----------------------------------------------------------------------------

$max = 100;// максимум строк в таблице
$folio = (int)$_GET['folio'];// номер страницы
$start = ($folio*$max);// начало просмотра строк в базе данных

//-------------------------------------------------------

$count = $db->num_rows($db->q('SELECT id '.$sql));// кол-во записей по запросу

include_once('listing.php');

//----------------------------------------------------------------------------

$tr = '';

if( $q = $db->q('SELECT * '.$sql.' ORDER BY redirects DESC, str_search LIMIT '.$start.', '.$max) ){

  $a = array();

  while($r = $db->fetch_a($q) ){

    //<td><input type="checkbox" name="off"'.($r['off']? ' checked' : '').'></td>

    $tr .= '<form action="'.$url.'&folio='.$folio.'" method="post">
    <tr title="'.$r['mid'].':'.$r['cid'].'">
      <td><input type="text" readonly style="width:97%;" value="'.htmlspecialchars($r['str_search'], ENT_COMPAT | ENT_HTML401, $charset).'"></td>
      <td><input type="text" readonly style="width:97%;" value="'.htmlspecialchars($r['str_found'], ENT_COMPAT | ENT_HTML401, $charset).'"></td>
      <td>'.$r['redirects'].'</td>
      <td><input type="text" readonly style="width:97%;" value="'.htmlspecialchars($r['url'], ENT_COMPAT | ENT_HTML401, $charset).'"></td>
      <td><input type="text" name="correctly" style="width:97%;" value="'.htmlspecialchars($r['correctly'], ENT_COMPAT | ENT_HTML401, $charset).'"></td>
      <td>'.status($r['status']).'</td>
      <td><input type="hidden" name="id" value="'.$r['id'].'"><input type="submit" value="—охранить"></td>
    </tr></form>';

  }

}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru-ru" xml:lang="ru-ru">
<head>
	<title>»справл€лка</title>
	<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">
	<meta http-equiv="Content-Language" content="ru">
	<script language="Javascript" type="text/javascript" src="http://yandex.st/jquery/1.9.1/jquery.min.js"></script>
</head>
<body>
  '.$result.'<br>
  <table border="0" cellpadding="3"><tr>
      <td>выборка:</td>
      <td>
        <select id="status">'.str_replace('="'.$status.'"', '="'.$status.'" selected', '
          <option value="0">новые</option>
          <option value="1">проверенные</option>
          <option value="2">отключенные</option>
      ').'</td>
    </tr>
  </table>
  <table border="1" cellpadding="3" width="100%">
    <tr>
      <td>что искали</td>
      <td>что найдено</td>
      <td width="1">редиректов</td>
      <td>url</td>
      <td>правильный</td>
      <td width="1">статус</td>
      <td width="1">действие</td>
    </tr>
    '.$tr.'
  </table>
  <style type="text/css">
  #Listing { padding: 5px 15px; text-align:center; }
  #Listing SPAN { line-height: 25px; padding: 0 5px; }
  #Listing EM { font-weight: bold }
  </style>
  '.$list.'
  <script type="text/javascript">
  $("#status").change(function(){
    document.location.href = "'.$_SERVER['PHP_SELF'].'?status="+ $(this).val();
  });
  </script>
</body>
</html>';
?>