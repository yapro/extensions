<?php
/**
 * Author: lebnik
 * класс загрузки файла в директорию
 * возвращает путь к файлу или текст ошибки.
 * если объявить массив $allowTypes['jpg']++; - можно применять проверку типа файлов
 * Пример: fileUpload::i('windows-1251')->run('uploadFile', '/userDir', 'fileName.jpg', array('jpg'=>1) );
 */

class fileUpload {
  private $charset;// кодировка данного файла ( например: windows-1251 ), для UTF-8 не нужно указывать
  protected static $instance; // object instance
  private function __construct(){ /* ... @return Singleton */ }  // Защищаем от создания через new Singleton
  private function __clone()    { /* ... @return Singleton */ }  // Защищаем от создания через клонирование
  private function __wakeup()   { /* ... @return Singleton */ }  // Защищаем от создания через unserialize
  public static function i($charset = '') { // Возвращает единственный экземпляр класса. @return Singleton
    if ( is_null(self::$instance) ) {
      self::$instance = new fileUpload();
    }
    if($charset){
      self::$instance->charset = $charset;
    }
    return self::$instance;
  }

  /**
   * загружает в директорию $dirName файл указанный в поле $fieldName
   *
   * @param string $fieldName - имя поля в HTML-разметке
   * @param string $dirName - папка, куда сохранять файл, относительно корня сайта, например /uploads
   * @param string $fileName - не обязателно, если не указать будет сохранено собственное имя в транслите с меткой времени
   * @param array $allowTypes - массив возможных типов загружаемого файла
   * @return array - массив с ключом pathToImage - в случае успеха, и ключом error - в противном случае
   */
  function run($fieldName = '', $dirName = '', $fileName = '', $allowTypes = array() ){

    if(!$fieldName){
      return array('error'=>'!$fieldName');
    }

    if(!$dirName){
      return array('error'=>'!$dirName');
    }

    $fileNameSafe = $_FILES[$fieldName]['name'];

    if($fileNameSafe){

      $mod = ($this->charset && $this->charset != 'UTF-8')? '' : 'u';

      $fileNameSafe = preg_replace('/[^-а-яa-z0-9\.]/sUi'.$mod, '_', $fileNameSafe);

    }

    if($_FILES && $_FILES[$fieldName]['tmp_name'] && $fileNameSafe){// загружаю файлы и возвращаю имена загруженных файлов

      $type = array_reverse( explode('.', $fileNameSafe) );// узнаем тип файла
      $type = strtolower($type['0']);// делаем тип в нижнем регистре

      if( $allowTypes && !isset($allowTypes[ $type ]) ){

        return array('error'=>'Загрузка файла отменена из-за неразрешенного типа файла: '.$type);

      }

      $info = $this-> path($_SERVER['DOCUMENT_ROOT'], $dirName);

      if( isset($info['error']) ){
        
        return array('error'=>$info['error']);
        
      }else{

        if( !$fileName ){

          $fileName = '';// находим имя файла
          $ex = explode('.', $fileNameSafe);
          array_pop($ex);
          foreach($ex as $name_part){
            if($name_part){
              $fileName .= $this-> translite($name_part).'_';
            }
          }
          $fileName .= time().'.'.$type;// добавляем окончание к имени файла с временем загрузки

        }

        $dirName .= '/';

        if(move_uploaded_file($_FILES[$fieldName]['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$dirName.$fileName)){

          @chmod($_SERVER['DOCUMENT_ROOT'].$dirName.$fileName, 0664);

          return array('pathToImage'=>$dirName.$fileName);

        }else{
          
          return array('error'=>'Недостаточно прав для загрузки файла : '.$dirName.$fileName);
          
        }
      }
    }else{
      return array('error'=>'!$_FILES');
    }

  }

  /**
   * проверяет и при отсутствии досоздает директории в указанной директории
   * а при нехватке прав возвращает информацию о ошибке
   * Например: $error = path('/home/user/dir/', '/uploads')
   * Например: $error = path($_SERVER['DOCUMENT_ROOT'], 'images');
   *
   * @param string $rootDir
   * @param string $dirName
   * @return string
   */
  function path($rootDir = '', $pathDir = ''){

    if( !$rootDir ){
      return array('error' => 'пустное значение переменой $rootDir : '.__FILE__);
    }

    if( !$pathDir ){
      return array('error' => 'пустное значение переменой $dirName : '.__FILE__);
    }

    $rootDir = '/'.implode('/', array_diff( explode('/', $rootDir), array(null) ) ).'/';// приводим $rootDir-путь к виду /home/www/

    $dirs = explode('/', $pathDir);

    $deep = 0;

    foreach($dirs as $name){

      if($name){

        if(!is_dir($rootDir.$name)){

          if(!mkdir($rootDir.$name) && !is_dir($rootDir.$name)){

            return (__FILE__.' : Не могу создать директорию '.$rootDir.$name);

          }else{

            @chmod($rootDir.$name, 0775);

          }
        }
        $rootDir .= $name.'/';

        $deep++;
      }
    }
    return array('deep' => $deep);// возвращаем глубину файла от $rootDir-директории
  }

  /**
   *  Транслит
   * 
   * @param string $text
   * @param bool $i
   * @return string
   */
  public function translite($text='', $i=false){
    $s = array(// алфавит
      'а'=>'a',
      'б'=>'b',
      'в'=>'v',
      'г'=>'g',
      'д'=>'d',
      'е'=>'e',
      'ё'=>'yo',
      'ж'=>'j',
      'з'=>'z',
      'и'=>'i',
      'й'=>'y',
      'к'=>'k',
      'л'=>'l',
      'м'=>'m',
      'н'=>'n',
      'о'=>'o',
      'п'=>'p',
      'р'=>'r',
      'с'=>'s',
      'т'=>'t',
      'у'=>'u',
      'ф'=>'f',
      'х'=>'h',
      'ц'=>'c',
      'ч'=>'ch',
      'ш'=>'sh',
      'щ'=>'sh',
      'ъ'=>'i',
      'ы'=>'i',
      'ь'=>'i',
      'э'=>'e',
      'ю'=>'yu',
      'я'=>'ya',
      'є'=>'e',
      'і'=>'i',
      'ї'=>'yi');
    $z = array(// доплнительные символы
      ' '=>'-',
      "\t"=>'-',
      "\n"=>'-',
      "\r"=>'-',
      "\f"=>'-',
      '–'=>'-',
      '*'=>'-',
      '+'=>'-',
      '&'=>'-',
      '%'=>'-',
      '$'=>'-',
      '#'=>'-',
      '@'=>'-',
      '!'=>'-',
      '~'=>'-',
      '`'=>'-',
      '“'=>'-',
      '='=>'-',
      '('=>'-',
      ')'=>'-',
      '?'=>'-',
      '^'=>'-',
      ';'=>'-',
      '№'=>'-',
      '"'=>'-',
      '\''=>'-',
      '>'=>'-',
      '<'=>'-',
      ':'=>'-',
      '/'=>'-',
      '|'=>'-',
      '.'=>'-',
      ','=>'-',
      ']'=>'-',
      '\\'=>'-',
      '['=>'-',
      '«'=>'-',
      '»'=>'-',
      '—'=>'-');
    if($i=='s'){// символы
      $t = &$s;
    }else if($i=='z'){// знаки
      $t = &$z;
    }else{// символы и знаки
      $t = array_merge($s, $z);
    }
    return strtr($text, $t);
  }

  /**
   * проверяет точный размер изображения
   *
   * @param string $fieldName
   * @param int $width
   * @param int $height
   * @return array
   */
  public function imageSize($fieldName = '', $width = 0, $height = 0){

    if(!$fieldName){
      return array('error' => '!$fieldName');
    }

    if(!$width){
      return array('error' => '!$width');
    }

    if(!$height){
      return array('error' => '!$height');
    }

    if( !$_FILES || !isset($_FILES[$fieldName]) || !isset($_FILES[$fieldName]['tmp_name']) ){
      return array('error' => '!$_FILES');
    }

    $info = getimagesize($_FILES[$fieldName]['tmp_name']);

    if( !$info || !isset($info['0']) || !isset($info['1']) ){
      return array('error' => 'it`s not a picture');
    }

    if( $info['0'] != $width || $info['1'] != $height ){
      return array('error' => 'bad size');
    }

    return array('allRight' => true);

  }

  /**
   * проверяет соотношение сторон изображения по наименьшей стороне
   *
   * @param string $fieldName
   * @param int $min
   * @return array
   */
  public function imageMinSize($fieldName = '', $min = 0){

    if(!$fieldName){
      return array('error' => '!$fieldName');
    }

    if(!$min){
      return array('error' => '!$min');
    }

    if( !$_FILES || !isset($_FILES[$fieldName]) || !isset($_FILES[$fieldName]['tmp_name']) ){
      return array('error' => '!$_FILES');
    }

    $info = getimagesize($_FILES[$fieldName]['tmp_name']);

    if( !$info || !isset($info['0']) || !isset($info['1']) ){
      return array('error' => 'it`s not a picture');
    }

    if( $info['0'] < $min || $info['1'] < $min ){
      return array('error' => 'small image');
    }

    return array('allRight' => true);

  }

  /**
   * проверяет соотношение сторон изображения по наименьшей стороне
   *
   * @param string $fieldName
   * @param int $min
   * @return bool
   */
  public function isImageMinSize($fieldName = '', $min = 0){

    $info = $this-> imageMinSize($fieldName, $min);

    //print_r($info); exit;

    return isset($info['allRight']) ? true : false;

  }
}
?>
