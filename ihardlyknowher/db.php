<?
	final class DB {
		private static $_dblink;
		private static $_dbselected;

		public static $_validOperators = array('<=>', '=', '>=', '>', 'IS NOT', 'IS', '<=', '<', 'LIKE', 'NOT LIKE');

		public static function init() {
			$host = MYSQL_HOST . (defined('MYSQL_PORT') ? ':'.MYSQL_PORT : '');
			self::$_dblink = mysql_connect($host, MYSQL_USER, MYSQL_PASS);

			if (self::$_dblink) {
				self::$_dbselected = mysql_select_db(MYSQL_DB, self::$_dblink);
				if(self::$_dbselected) {
					self::_query('SET NAMES utf8 COLLATE utf8_general_ci');
				} else {
					throw new Exception(mysql_error());
				}
			} else {
				throw new Exception(mysql_error());
			}
		}

		public static function shutdown() {
			if (self::$_dblink) {
				mysql_close(self::$_dblink);
			}
		}

		public static function select($options = array()) {
			//FROM
			$sql = "SELECT " . self::_escape($options['what']) . " FROM " . self::_escape($options['from']);

			//MATCHES
			if (!empty($options['matches'])) {
				$i = 0;
				$match_count = count($options['matches']);
				$sql .= " WHERE";

				foreach ($options['matches'] as $match) {
					if (count($match) != 3) throw new Exception("Matches array requires 3 parameters.");
					if (! in_array($match[1], self::$_validOperators)) throw new Exception("Invalid matches operator.");

					$sql .= (" ".self::_escape($match[0]).$match[1]."'". self::_escape($match[2])."'");
					if(++$i < $match_count) {
						$sql .= " AND";
					}
				}
			}

			//ORDER
			if (!empty($options['order'])) {
				$order = ($options['order'] === 'random' ? 'RAND()' : $options['order']);
				$sql .= (" ORDER BY " . self::_escape($order));
			}

			//DIRECTION
			if (!empty($options['direction'])) {
				if(
					$options['direction'] === 'desc' ||
					$options['direction'] === 'DESC' ||
					$options['direction'] === 'asc' ||
					$options['direction'] === 'ASC'
				) {
					$dir = self::_escape(strtoupper($options['direction']));
					$sql .= (" ".$dir);
				}
			}

			//LIMIT
			if (!empty($options['limit'])) {
				$sql .= " LIMIT ".self::_escape($options['limit']);
			}

			//CACHE_TIME & KEY
			if(!empty($options['key'])) { //only use the cache if a key has been specified
				if (empty($options['cache_time'])) {
					return self::fetchCached($options['key'],$sql);
				} else {
					return self::fetchCached($options['key'],$sql,$options['cache_time']);
				}
			} else {
				return self::fetch($sql);
			}
		}

		// options: table, what
		// returns new id or false
		public static function insert($options = array()) {
			if (empty($options['table'])) return false;
			if (empty($options['what'])) return false;

			$sql = "INSERT INTO " . self::_escape($options['table']);

			//WHAT
			$keys = array();
			$values = array();
			foreach ($options['what'] as $k=>$v) {
				$keys[] = ("`" . self::_escape($k) . "`");
				
				if (is_null($v)) {
					$values[] = 'NULL';
				} elseif($v === 'NOW()') {
					$values[] = $v;
				} else {
					$values[] = ("'" . self::_escape($v) . "'");
				}
			}

			$sql .= " (";
			$sql .= implode(", ", $keys);
			$sql .= ") VALUES (";
			$sql .= implode(", ", $values);
			$sql .= ")";

			if (self::_query($sql)) {
				return mysql_insert_id();
			} else {
				return false;
			}
		}

		public static function update($options = array()) {
			if (empty($options['table'])) return false;
			if (empty($options['what'])) return false;
			if (empty($options['what']['id'])) return false;

			$sql = "UPDATE " . self::_escape($options['table']);

 			//WHAT
 			$clauses = array();
			foreach($options['what'] as $k=>$v) {
				if($k != 'id') {
					if(is_null($v)) {
						$clauses[] = ("`" . self::_escape($k) . "` = NULL");
					} elseif($v === 'NOW()') {
						$clauses[] = ("`" . self::_escape($k) . "` = NOW()");
					} else { 
						$clauses[] = ("`" . self::_escape($k) . "` = '" . self::_escape($v) . "'");
					}
				}
			}
			$sql .= " SET ";
			$sql .= implode(", ", $clauses);
			$sql .= (" WHERE " . "`id` = " . self::_escape($options["what"]["id"]));

			return self::_query($sql);
		}

		public static function destroy($options = array()) {
			if (empty($options['table'])) return false;
			if (empty($options['what'])) return false;
			if (empty($options['what']['id'])) return false;

			$sql = "DELETE FROM ";
			$sql .= self::_escape($options['table']);
			$sql .= " WHERE `id` = ";
			$sql .= self::_escape($options["what"]["id"]);

			return self::_query($sql);
		}

		private static function _query($sql) {
			if (!self::$_dblink) {
				self::init();
			}
			
			$result = mysql_query($sql,self::$_dblink);

			if (mysql_errno(self::$_dblink) != 0) {
				throw new Exception(mysql_error(),mysql_errno());
			}

			return $result;
		}

		private static function _escape($string) {
			return (self::$_dblink ? mysql_real_escape_string($string,self::$_dblink) : mysql_escape_string($string));
		}	

		public static function fetchCached($key, $sql, $expires = 0) {
			$rows = Cache::get($key);
			if ($rows === false) {
				$rows = self::fetch($sql);
				if ($expires) {
					Cache::set($key, $rows, $expires);
				}
			}
			return $rows;
		}

		public static function fetch($sql) {
			$result = self::_query($sql);
			if ($result) {
				while($row = mysql_fetch_assoc($result)) {
					$rows[] = $row;
				}
				mysql_free_result($result);
				return (empty($rows) ? false : $rows);
			} else {
				 return false;
			}
		}
	}
?>