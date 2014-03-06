<?php
/**
 * User: lebnik
 * Date: 3/6/14
 * Time: 1:24 PM
 * обертка над PDO, чтобы просматривать полные версии запросов
 */

class PDOTester extends PDO
{
    public $sql = '';// переменная содержит SQL-запрос, который будет отправлен в базу данных

    public function __construct($dsn = '', $username = '', $password = '', $driver_options = array())
    {
        parent::__construct($dsn, $username, $password, $driver_options);

        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('PDOStatementTester', array($this)));
    }

}

class PDOStatementTester extends PDOStatement
{

    protected $pdo;

    protected function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    function execute(array $input_parameters = null){

        $sql = parent::$this->queryString;
        if ($input_parameters) {
            foreach ($input_parameters as $key => $value) {
                $sql = str_replace(':'.$key, $this->pdo->quote($value), $sql);
            }
        }
        $this->pdo->sql = $sql;

        return parent::execute($input_parameters);

    }

    function getRow(array $input_parameters = null){

        if( $this->execute($input_parameters) ){

            return $this->fetch(PDO::FETCH_ASSOC);

        }
    }
}

/*************** Пример использования: **************/

// подключаемся к базе данных mysql
$dbname = 'test';
$dbuser = 'root';
$dbpasswd = '';
$conf = array();
try {
    $pdo = new PDOTester('mysql:host=localhost;dbname='.$dbname, $dbuser, $dbpasswd, $conf);
}catch( Exception $e ){
    echo 'mysql not found: '. $e->getMessage();
    exit;
}
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// переключаемся на основную базу
try {
    $pdo->query('USE myDatabase');
} catch ( Exception $e ) {
    echo 'USE failed: ' . $e->getMessage();
    exit;
}

$pdo->prepare('SET NAMES cp1251')->execute();


$a = $pdo->prepare('SELECT id FROM vseins_bdescrs_categories
WHERE category_id = :category_id LIMIT 1')->getRow(array('category_id'=>24));


echo $pdo->sql . PHP_EOL;