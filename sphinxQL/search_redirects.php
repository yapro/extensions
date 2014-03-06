<?php
@header('content-type:text/html; charset=windows-1251');

require_once("../app/init.php");
require_once("../inc/db.php");

$tr = '';

if( $q = $db->q('SELECT * FROM '.$db->getPrefix().'search_redirects ORDER BY str_search') ){

  $a = array();

  while($r = $db->fetch_a($q) ){

    $tr .= '<tr>
      <td>'.$r['id'].'</td>
      <td>'.$r['mid'].'</td>
      <td>'.$r['cid'].'</td>
      <td>'.$r['str_search'].'</td>
      <td>'.$r['str_found'].'</td>
      <td>'.$r['url'].'</td>
      <td>'.$r['redirects'].'</td>
    </tr>';

  }

}

//----------------------------------------------------------------------------

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru-ru" xml:lang="ru-ru">
<head>
	<title>Статистика редиректов</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-Language" content="ru">
</head>
<body>
  <table border="1" cellpadding="3"><tr>
      <td>id</td>
      <td>ид производителя</td>
      <td>ид раздела (категории)</td>
      <td>что искали</td>
      <td>что найдено</td>
      <td>url</td>
      <td>кол-во редиректов</td>
    </tr>
    '.$tr.'
  </table>
</body>
</html>';