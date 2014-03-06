<?php
namespace Skeleton\Pattern;
/**
 * Реализация паттерна singleton
 * @author KovalevAU
 *
 */
abstract class Singleton {
  /**
   * Массив синглтонов
   *
   * @var Singleton[]
   */
  private static $instances = array();

  /**
   * Создает или возвращает только один эземпляр класса
   * @return Singleton
   */
  final public static function instance() {
    $calledClassName = get_called_class();
    if(!isset( self::$instances[$calledClassName])) {
      self::$instances[$calledClassName] = new $calledClassName();
    }
    return self::$instances[$calledClassName];
  }

  /**
   * Запрещаем копирование
   */
  final public function __clone() {

  }

  /**
   *
   * Запрещаем sleep
   */
  final public function __sleep() {

  }

  /**
   *
   * Запрещаем wakeup
   */
  final public function __wakeup(){

  }

  /**
   *
   * Запрещаем конструктор
   */
  protected function __construct() {
    $this->initObject();
  }

  /**
   * Инициализация объекта (вместо конструктора)
   * @return void
   */
  protected function initObject() {

  }
}