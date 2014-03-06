<?php
namespace Skeleton\Pattern;
/**
 * ���������� �������� singleton
 * @author KovalevAU
 *
 */
abstract class Singleton {
  /**
   * ������ ����������
   *
   * @var Singleton[]
   */
  private static $instances = array();

  /**
   * ������� ��� ���������� ������ ���� �������� ������
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
   * ��������� �����������
   */
  final public function __clone() {

  }

  /**
   *
   * ��������� sleep
   */
  final public function __sleep() {

  }

  /**
   *
   * ��������� wakeup
   */
  final public function __wakeup(){

  }

  /**
   *
   * ��������� �����������
   */
  protected function __construct() {
    $this->initObject();
  }

  /**
   * ������������� ������� (������ ������������)
   * @return void
   */
  protected function initObject() {

  }
}