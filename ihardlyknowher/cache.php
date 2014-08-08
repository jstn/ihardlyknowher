<?
	abstract class Cache {	
		private static $_cache;
		private static $_connected;
		private static $_down;
	
		public static function connect() {
			if(self::$_connected) {
				return true;
			} elseif(self::$_down) {
				return false;
			} else {
				if(class_exists('Memcache')) {
					self::$_cache = new Memcache();
					self::$_connected = @self::$_cache->connect('127.0.0.1',11211);
				} else {
					self::$_down = true;
				}
			
				if(!self::$_connected) self::$_down = true;
				
				return self::$_connected;
			}
		}
	
		public static function set($key, $value, $expires = 60) {
			if(!self::connect()) return false;
			else return self::$_cache->set($key, $value, MEMCACHE_COMPRESSED, $expires);
		}

		public static function get($key) {
			if(!self::connect()) return false;
			else return self::$_cache->get($key);
		}

		public static function delete($key) {
			if(!self::connect()) return false;
			else return self::$_cache->delete($key);
		}
	}
?>