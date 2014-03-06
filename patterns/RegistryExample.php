<?php
namespace VI;

/**
 * ������� ������ http://alexmuz.ru/php-registry/
 * ������ ������������� : $representation = \VI\Registry::get(\VI\\Entity\Represent);
 * Class Registry
 * @package VI
 */

class Registry extends \Skeleton\Pattern\Registry {

	/**
	 * ������� �����������������
	 * @var string
	 */
	const CURRENT_REPRESENT = '\\VI\\Entity\\Represent';

  /**
   *
   * ������� ����������� � ���� ������
   * @var string
   */
	const CURRENT_DB = '\\VI\\DB';

	/**
	 * ������� ������������
	 * @var string
	 */
	const CURRENT_USER = '\\VI\\Entity\\Contragent';

	/**
	 * ���������� ��������� ������ �������������
	 * @var string
	 */
	const TEMPLATER = '\\VI\\Layout\\Templater';

	/**
	 * ���������� ��������� �����������
	 * @var string
	 */
	const TRANSLATER = '\\Zend\\I18n\\Translator\\Translator';
	
	
	static $usages = array(
      1 => '��� �������� ����������',
      2 => '��������������������',
      3 => '����������������'
  );

  /**
   * �������� ������������ ��������
   * @param string $index - ������ ��� ������
   * @return mixed - ������ ������
   */
	public static function get($index) {
  	$return = parent::get($index);
  	if (!$return) {
   	 $return = new $index;
      parent::set($index, $return);
  	}
  	return $return;
  }

  /**
   * ������ ��� �������� ������������ ������� ��������
   * @return \VI\Entity\Contragent
   */
  public static function getContragent() {
  	$return = parent::get('contragent');
  	if (!$return) {
  		$return = new \VI\Entity\Contragent();
  		parent::set('contragent', $return);
  	}
  	return $return;
  }

  /**
   * ������ ��� ������������ ������ �����������������
   * @return \VI\Entity\UserAdmin\Authorized|\VI\Entity\UserAdmin\UnAuthorized
   */
  public static function getUserAdminCurrent() {
    $return = parent::get('userAdminCurrent');
    if (!$return) {
      $return = \VI\Entity\UserAdmin\Factory::create();
      parent::set('userAdminCurrent', $return);
    }
    return $return;
  }

  /**
   * ������ ��� ������ � ������������
   * @return \VI\Memcache
   */
  public static function getCacher() {
    $config = \VI\Config::instance();
    $config instanceof \VI\Config;
  	$return = parent::get('cacher');
  	if (!$return) {
  		if(class_exists('\Memcache') && $config->getCacheEnabled()) {
  		  $return = new \VI\Common\Memcache();
  		} else {
  		  $return = new \VI\Common\NoMemcache();
  		}
  	  parent::set('cacher', $return);
  	}
  	return $return;
  }
}