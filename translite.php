<?php
  /**
   *  Транслит
   *
   * @param string $str
   * @param bool $words
   * @return string
   */
  private function translite($str = '', $words = false){
    $a = array(// алфавит
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
    $str = strtr($str, $a);
    if( !$words ){// если не только буквы
      $str = preg_replace('/[^-a-z0-9\.]/sUi', '_', $str);
    }
    return $str;
  }
