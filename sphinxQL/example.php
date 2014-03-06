<?php
$charset = 'windows-1251';// если не указывать, то по-умолчанию: UTF-8
//@header("content-type:text/html; charset=UTF-8");
@header('content-type:text/html; charset='.$charset);

/*
$search = 'привет как дела дружище';
//print_r(array_splice( explode(' ', ), 0, 4));
$e = array_unique( explode(' ', $search) );
$e_count = count($e);
for(; $e_count > 1; $e_count--){
    $e_copy = $e;
    $search = implode(' ', array_splice($e_copy, 0, $e_count));
            echo $e_count.':'.$search.'<br>';
        }
exit;
*/
require_once('sphinxQL.php');

require_once ('Text_LangCorrect-1.4.3/ReflectionTypeHint.php');
require_once ('Text_LangCorrect-1.4.3/Text/LangCorrect.php');
require_once ('Text_LangCorrect-1.4.3/UTF8.php');
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

/***********************************************************************/

//print_r( count( sphinxQL::i()->split ('zxc asd') ) ); exit;
//print_r( sphinxQL::i()->permute('FT HR 2450') ); exit;
// print_r( sphinxQL::i()->sphinx('makita', 'indexMakesCategoriesSoundex') ); exit;

$q = $c->prepare('SELECT * FROM documents ORDER BY id');
if($q && $q->execute() && $q->rowCount()>0){

    $a = array();
    while($r = $q->fetch() ){
        $a[ $r['id'] ] = $r;
    }

  // удлинитель с тремя розетками   ...   FT HR 2450 | HR 2450 FT
  //$a = array(1 => array('title' => 'удлинитель 3 розетки Светозар', 'id' => 1, 'my_comment' => '', 'group_id' => 0, 'content' => ''));
  // $a = array(1 => array('title' => 'ЮниМастер Стружкоотсосы', 'id' => 1, 'my_comment' => '', 'group_id' => 0, 'content' => ''));
  //$a = array(1 => array('title' => 'ЮниМастер Стружкоотсосы', 'id' => 1, 'my_comment' => '', 'group_id' => 0, 'content' => ''));
  //$a = array(1 => array('title' => '128R', 'id' => 1, 'my_comment' => '', 'group_id' => 0, 'content' => ''));
  //$a = array(1 => array('title' => 'Patriot Garden T 6.5/600', 'id' => 1, 'my_comment' => '', 'group_id' => 0, 'content' => ''));

    if($a){

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
              'limit_end' => 777777777,
              'indexName' => 'indexMakesCategories',
              'indexNameSoundex' => 'indexMakesCategoriesSoundex',
              'full' => true
            );

            $config = array(
              'search' => $prepare,
              'limit_start' => 0,
              'limit_end' => 777777777,
              'order' => 'WEIGHT() DESC, rating ASC'
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

    }
}
