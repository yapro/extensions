<?php
namespace VI;

/**
 * паттерн Реестр http://alexmuz.ru/php-registry/
 * пример использования : $representation = \VI\Registry::get(\VI\\Entity\Represent);
 * Class Registry
 * @package VI
 */

class Registry extends \Skeleton\Pattern\Registry {

	/**
	 * Текущее представительство
	 * @var string
	 */
	const CURRENT_REPRESENT = '\\VI\\Entity\\Represent';

  /**
   *
   * Текущее подключение к базе данных
   * @var string
   */
	const CURRENT_DB = '\\VI\\DB';

	/**
	 * Текущий пользователь
	 * @var string
	 */
	const CURRENT_USER = '\\VI\\Entity\\Contragent';

	/**
	 * Глобальный экземпляр нашего шаблонизатора
	 * @var string
	 */
	const TEMPLATER = '\\VI\\Layout\\Templater';

	/**
	 * Глобальный экземпляр переводчика
	 * @var string
	 */
	const TRANSLATER = '\\Zend\\I18n\\Translator\\Translator';
	
	
	static $usages = array(
      1 => 'Для домашней мастерской',
      2 => 'Полупрофессиональный',
      3 => 'Профессиональный'
  );

  /**
   * Получить единственный экзмпляр
   * @param string $index - полное имя класса
   * @return mixed - нужный объект
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
   * Объект для текущего пользователя личного кабинета
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
   * Объект для пользователя панели администрирования
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
   * Объект для работы с кэшированием
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