<?php
namespace Skeleton\Pattern;
/**
 * Реализация паттерна реестр
 * @author KovalevAU
 */
class Registry extends Singleton {
  /**
   *
   * Хранилище для регистра
   * @var array $entries
   */
  private $entries;

  /**
   *
   * Берет значение из регистра, если оно записано
   * @param string $index
   * @return mixed
   */
  public static function get($index) {
    $instance = self::instance();
    if(isset($instance->entries[$index])){
      return $instance->entries[$index];
    } else {
      return false;
    }
  }

  /**
   *
   * Записывает значение в регистр
   * @param string $index
   * @param mixed $val
   */
  public static function set($index, $val) {
    $instance = self::instance();
    $instance->entries[$index] = $val;
  }
}