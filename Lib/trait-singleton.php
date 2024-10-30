<?php
namespace MonLabo\Lib;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/* trait \MonLabo\Lib\Singleton {
*/

 /**
 * trait \MonLabo\Lib\Singleton
 * Singleton trait to implements Singleton pattern in any classes where this trait is used.
 *
 * @package
 */
	trait Singleton {

		/**
		* Singleton value
		* @var object[]|null
		* @access protected
		*/
		protected static $_instance = array();

		/**
		 * Protected class constructor to prevent direct object creation.
		 * @access protected
		 */
		protected function  __construct() {}

		/**
		 * Prevent object cloning
  		 * @access private
		 */
		private function  __clone() { 
		}

		/**
		 * Singletons should not be restorable from strings.
		 */
		public function __wakeup() {
			throw new \Exception("Cannot unserialize a singleton.");
		}

		/**
		 * To return new or existing Singleton instance of the class from which it is called.
		 * As it sets to final it can't be overridden.
		 *
		 * @return self Singleton instance of the class.
		 */
		final public static function getInstance() {
			$subclass = static::class;
			if (!isset(self::$_instance[$subclass])) {
				self::$_instance[$subclass] = new self(); // @phan-suppress-current-line PhanTypeInstantiateTraitStaticOrSelf
			}
			return self::$_instance[$subclass]; // @phan-suppress-current-line PhanTypeArraySuspiciousNullable
		}

		/**
		* Méthode pour effacer l'instance
		* @return void
		*/
		public static function unsetInstance() {
			self::$_instance = null;
		}
	}

?>
