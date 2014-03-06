<?php
$charset = 'windows-1251';// если не указывать, то по-умолчанию: UTF-8

@header('content-type:text/html; charset='.$charset);

require_once('../sphinxQL/sphinxQL.php');

require_once ('../sphinxQL/Text_LangCorrect-1.4.3/ReflectionTypeHint.php');
require_once ('../sphinxQL/Text_LangCorrect-1.4.3/Text/LangCorrect.php');
require_once ('../sphinxQL/Text_LangCorrect-1.4.3/UTF8.php');
sphinxQL::i()->corrector = new Text_LangCorrect();
sphinxQL::i()->charset = $charset;

/***********************************************************************/

// подключаемся к базе данных mysql
$dbname = 'vseinstrumenti_test5';
$dbuser = 'root';
$dbpasswd = '';

$c = new PDO('mysql:host=localhost;dbname='.$dbname, $dbuser, $dbpasswd);
if( !$c ){
  echo 'mysql not found: '.__FILE__; exit;
}

if($charset == 'windows-1251'){

  $c->prepare('SET NAMES cp1251')->execute();
  setlocale(LC_ALL, 'ru_RU.cp1251');
  mb_internal_encoding("cp1251");

}else{

  $c->prepare('SET NAMES UTF8')->execute();
  setlocale(LC_ALL, 'ru_RU.UTF-8');
  mb_internal_encoding("UTF-8");

}

$a = array(1 => array('title' => 'перфоратор бош', 'id' => 1, 'my_comment' => '', 'group_id' => 0, 'content' => ''));

$s = '';
$answers = 0;
$answers_all = 0;

foreach($a as $documents){

  $prepare = sphinxQL::i()->prepare($documents['title'], $documents['id'], $a);

  if(!$prepare){
    echo 'empty prepare:'.$documents['title'].'<br>';
    continue;
  }

  $config = array(
    'search' => $prepare,
    'limit_start' => 0,
    'limit_end' => 10,
    'indexName' => 'indexMakesCategories',
    'indexNameSoundex' => 'indexMakesCategoriesSoundex',
    'full' => true,
    'first' => true,
    'order' => 'WEIGHT() DESC, l ASC'// сортировка по длинне поисковой фразы, чтобы 'Makita Перфораторы' возвращались первее 'Makita Перфораторы с патроном SDS-plus'
  );

  $search = sphinxQL::i()->search($config);

  $qvi = 0;
  $substr = '';

  if($search['count'] > 0){

    $answers_all += $search['count'];

    $answers++;

    $in = array();

    foreach($search['answers'] as $id => $true){

      $in[] = $id;

    }

    $qv = $c->prepare("SELECT g.id, CONCAT(m.name, ' ', g.name) As t
            FROM vseins_goods AS g
            LEFT JOIN vseins_makes AS m ON m.id = g.make_id
            WHERE g.id IN (".implode(',', $in).") LIMIT 10");
    $qv->execute();
    while($r = $qv->fetch() ){
      $qvi++;
      $substr .= '<tr class="substr"><td colspan="3">'.$r['id'].':'.$search['answers'][ $r['id'] ]['weight'].':'.$r['t'].'</td></tr>';

    }
  }

  $s .= '<tr><td colspan="3">&nbsp;<!--  --></td></tr>
        <tr title="'.htmlspecialchars($documents['my_comment']).'">
            <td style="color:#ff0000;">'.($substr?'':'=======>').'
                '.htmlspecialchars($documents['title'], ENT_COMPAT | ENT_HTML401, $charset).'
            </td>
            <td>'.$documents['group_id'].'</td>
            <td>'.$qvi.' из '.$search['count'].'</td>
        </tr>
        <tr><!-- style="'.( (count($a) > 1)? 'display:none' : '').'" -->
            <td colspan="3">
                <p>количество слов удаленных справа, чтобы получить результат: '.$search['minus'].'</p>
                <pre>№ способов нахождения данных: '.print_r($search['matches'],1).'</pre>
                <pre>meta: '.print_r($search['meta'],1).'</pre>
            </td>
        </tr>
        <tr>
            <td colspan="3" style="color:#ccc;">'.$documents['content'].'</td>
        </tr>'.$substr;
}

echo '<div>Вопросов: '.count($a).' &nbsp; &nbsp; Ответов: '.$answers.' &nbsp; &nbsp; Строк: '.$answers_all.'</div>
<table border="1">
  <tr>
    <td><b>Что ищем</td>
    <td><b>-</td>
    <td><b>Найдено</td>
  </tr>
  '.$s.'
</table>';