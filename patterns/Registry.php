<?php
namespace Skeleton\Pattern;
/**
 * ���������� �������� ������
 * @author KovalevAU
 */
class Registry extends Singleton {
  /**
   *
   * ��������� ��� ��������
   * @var array $entries
   */
  private $entries;

  /**
   *
   * ����� �������� �� ��������, ���� ��� ��������
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
   * ���������� �������� � �������
   * @param string $index
   * @param mixed $val
   */
  public static function set($index, $val) {
    $instance = self::instance();
    $instance->entries[$index] = $val;
  }
}