<?php
/**
 * Author: lebnik
 * ����� �������� ����� � ����������
 * ���������� ���� � ����� ��� ����� ������.
 * ���� �������� ������ $allowTypes['jpg']++; - ����� ��������� �������� ���� ������
 * ������: fileUpload::i('windows-1251')->run('uploadFile', '/userDir', 'fileName.jpg', array('jpg'=>1) );
 */

class fileUpload {
  private $charset;// ��������� ������� ����� ( ��������: windows-1251 ), ��� UTF-8 �� ����� ���������
  protected static $instance; // object instance
  private function __construct(){ /* ... @return Singleton */ }  // �������� �� �������� ����� new Singleton
  private function __clone()    { /* ... @return Singleton */ }  // �������� �� �������� ����� ������������
  private function __wakeup()   { /* ... @return Singleton */ }  // �������� �� �������� ����� unserialize
  public static function i($charset = '') { // ���������� ������������ ��������� ������. @return Singleton
    if ( is_null(self::$instance) ) {
      self::$instance = new fileUpload();
    }
    if($charset){
      self::$instance->charset = $charset;
    }
    return self::$instance;
  }

  /**
   * ��������� � ���������� $dirName ���� ��������� � ���� $fieldName
   *
   * @param string $fieldName - ��� ���� � HTML-��������
   * @param string $dirName - �����, ���� ��������� ����, ������������ ����� �����, �������� /uploads
   * @param string $fileName - �� ����������, ���� �� ������� ����� ��������� ����������� ��� � ��������� � ������ �������
   * @param array $allowTypes - ������ ��������� ����� ������������ �����
   * @return array - ������ � ������ pathToImage - � ������ ������, � ������ error - � ��������� ������
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

      $fileNameSafe = preg_replace('/[^-�-�a-z0-9\.]/sUi'.$mod, '_', $fileNameSafe);

    }

    if($_FILES && $_FILES[$fieldName]['tmp_name'] && $fileNameSafe){// �������� ����� � ��������� ����� ����������� ������

      $type = array_reverse( explode('.', $fileNameSafe) );// ������ ��� �����
      $type = strtolower($type['0']);// ������ ��� � ������ ��������

      if( $allowTypes && !isset($allowTypes[ $type ]) ){

        return array('error'=>'�������� ����� �������� ��-�� �������������� ���� �����: '.$type);

      }

      $info = $this-> path($_SERVER['DOCUMENT_ROOT'], $dirName);

      if( isset($info['error']) ){
        
        return array('error'=>$info['error']);
        
      }else{

        if( !$fileName ){

          $fileName = '';// ������� ��� �����
          $ex = explode('.', $fileNameSafe);
          array_pop($ex);
          foreach($ex as $name_part){
            if($name_part){
              $fileName .= $this-> translite($name_part).'_';
            }
          }
          $fileName .= time().'.'.$type;// ��������� ��������� � ����� ����� � �������� ��������

        }

        $dirName .= '/';

        if(move_uploaded_file($_FILES[$fieldName]['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$dirName.$fileName)){

          @chmod($_SERVER['DOCUMENT_ROOT'].$dirName.$fileName, 0664);

          return array('pathToImage'=>$dirName.$fileName);

        }else{
          
          return array('error'=>'������������ ���� ��� �������� ����� : '.$dirName.$fileName);
          
        }
      }
    }else{
      return array('error'=>'!$_FILES');
    }

  }

  /**
   * ��������� � ��� ���������� ��������� ���������� � ��������� ����������
   * � ��� �������� ���� ���������� ���������� � ������
   * ��������: $error = path('/home/user/dir/', '/uploads')
   * ��������: $error = path($_SERVER['DOCUMENT_ROOT'], 'images');
   *
   * @param string $rootDir
   * @param string $dirName
   * @return string
   */
  function path($rootDir = '', $pathDir = ''){

    if( !$rootDir ){
      return array('error' => '������� �������� ��������� $rootDir : '.__FILE__);
    }

    if( !$pathDir ){
      return array('error' => '������� �������� ��������� $dirName : '.__FILE__);
    }

    $rootDir = '/'.implode('/', array_diff( explode('/', $rootDir), array(null) ) ).'/';// �������� $rootDir-���� � ���� /home/www/

    $dirs = explode('/', $pathDir);

    $deep = 0;

    foreach($dirs as $name){

      if($name){

        if(!is_dir($rootDir.$name)){

          if(!mkdir($rootDir.$name) && !is_dir($rootDir.$name)){

            return (__FILE__.' : �� ���� ������� ���������� '.$rootDir.$name);

          }else{

            @chmod($rootDir.$name, 0775);

          }
        }
        $rootDir .= $name.'/';

        $deep++;
      }
    }
    return array('deep' => $deep);// ���������� ������� ����� �� $rootDir-����������
  }

  /**
   *  ��������
   * 
   * @param string $text
   * @param bool $i
   * @return string
   */
  public function translite($text='', $i=false){
    $s = array(// �������
      '�'=>'a',
      '�'=>'b',
      '�'=>'v',
      '�'=>'g',
      '�'=>'d',
      '�'=>'e',
      '�'=>'yo',
      '�'=>'j',
      '�'=>'z',
      '�'=>'i',
      '�'=>'y',
      '�'=>'k',
      '�'=>'l',
      '�'=>'m',
      '�'=>'n',
      '�'=>'o',
      '�'=>'p',
      '�'=>'r',
      '�'=>'s',
      '�'=>'t',
      '�'=>'u',
      '�'=>'f',
      '�'=>'h',
      '�'=>'c',
      '�'=>'ch',
      '�'=>'sh',
      '�'=>'sh',
      '�'=>'i',
      '�'=>'i',
      '�'=>'i',
      '�'=>'e',
      '�'=>'yu',
      '�'=>'ya',
      '�'=>'e',
      '�'=>'i',
      '�'=>'yi');
    $z = array(// ������������� �������
      ' '=>'-',
      "\t"=>'-',
      "\n"=>'-',
      "\r"=>'-',
      "\f"=>'-',
      '�'=>'-',
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
      '�'=>'-',
      '='=>'-',
      '('=>'-',
      ')'=>'-',
      '?'=>'-',
      '^'=>'-',
      ';'=>'-',
      '�'=>'-',
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
      '�'=>'-',
      '�'=>'-',
      '�'=>'-');
    if($i=='s'){// �������
      $t = &$s;
    }else if($i=='z'){// �����
      $t = &$z;
    }else{// ������� � �����
      $t = array_merge($s, $z);
    }
    return strtr($text, $t);
  }

  /**
   * ��������� ������ ������ �����������
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
   * ��������� ����������� ������ ����������� �� ���������� �������
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
   * ��������� ����������� ������ ����������� �� ���������� �������
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
