<?php
namespace Shared\Services;

use MongoDB\Driver\ReadPreference;
use Shared\Utils as Utils;
use Framework\{Registry, ArrayMethods};

class Db {
	const ID = 'MongoDB\BSON\ObjectID';
	const DATE = 'MongoDB\BSON\UTCDateTime';
	const REGEX = 'MongoDB\BSON\Regex';
	const DOCUMENT = 'MongoDB\BSON\Document';
	const REGISTRY = "MongoDB";

	const OPERATOR_IN = '$in';
	const OPERATOR_AND = '$and';
	const OPERATOR_OR = '$or';
	const OPERATOR_ELEM_MATCH = '$elemMatch';
	const OPERATOR_EQUALS = '$eq';
	const OPERATOR_NOT_EQUALS = '$ne';
	const OPERATOR_SET = '$set';
	const OPERATOR_UNSET = '$unset';
	
	/**
	 * Constants for Read Preference
	 */
	const READ_PRIMARY = ReadPreference::RP_PRIMARY;
	const READ_PRIMARY_PREFERRED = ReadPreference::RP_PRIMARY_PREFERRED;
	const READ_SECONDARY = ReadPreference::RP_SECONDARY;
	const READ_SECONDARY_PREFERRED = ReadPreference::RP_SECONDARY_PREFERRED;

	public static function connect() {
		$mongoDB = Registry::get(static::REGISTRY);
		if (!$mongoDB) {
			$configuration = Registry::get("configuration");
            try {
				$dbconf = $configuration->parse("configuration/database")->database->mongodb;
				$dbURL = sprintf("mongodb://%s:%s@%s/%s?replicaSet=%s", $dbconf->username, $dbconf->password, $dbconf->url, $dbconf->dbname, $dbconf->replica);
                $mongo = new \MongoDB\Client($dbURL, ['appname' => 'PHP-Mongo:' . gethostname(), 'retryWrites' => true]);
                $mongoDB = $mongo->selectDatabase($dbconf->dbname);
          
			} catch (\Exception $e) {
				throw new \Framework\Database\Exception("DB Error");   
			}
            Registry::set(static::REGISTRY, $mongoDB);
		}
		return $mongoDB;
	}

	/**
	 * All the read operations following this command will be done such that data is
	 * read from secondary instance whenever possible
	 */
	public static function readPreference($readPref = ReadPreference::RP_SECONDARY_PREFERRED) {
		$db = Registry::get(static::REGISTRY);
		$pref = new ReadPreference($readPref);
		$uriOpts = ['readPreference' => $pref];
		$newDb = $db->withOptions($uriOpts);
		Registry::set(static::REGISTRY, $newDb);
	}

	public static function generateId($str = true) {
		$id = new \MongoDB\BSON\ObjectID();
		if ($str) {
			$id = self::simplifyValue($id);
		}
		return $id;
	}

	public static function getCount($cursor) {
		$count = 0; $result = [];
		foreach ($cursor as $c) {
			$result[] = $c;
			$count++;
		}

		return [
			'count' => $count, 'result' => $result
		];
	}

	public static function getCacheKey($table, $query = [], $fields = []) {
		$str = sprintf("%s__%s__%s", $table, json_encode($query), json_encode($fields));
		return $table . '::' . md5($str);
	}

	public static function convertType($value, $type = 'id') {
		switch ($type) {
			case 'id':
				return Utils::mongoObjectId($value);

			case 'regex':
				return Utils::mongoRegex($value);
			
			case 'date':
			case 'datetime':
			case 'time':
				return self::time($value);
		}
		return '';
	}

	public static function getCollection($tableName) {
		return Registry::get("MongoDB")->$tableName;
	}

	public static function updateRaw($table, $find, $set, $opts = []) {
		$collection = Registry::get("MongoDB")->$table;
		
		$many = $opts['many'] ?? false;
		if ($many) {
			$collection->updateMany($find, $set);
		} else {
			$collection->updateOne($find, $set);
		}
	}

	/**
	 * Converts the Time given to MongoDB UTC DateTime
	 * @param  string|int|null $date Date that can be passed to strtotime or time in seconds
	 * @return \MongoDB\BSON\UTCDateTime
	 */
	public static function time($date = null) {
		if (is_string($date)) {
			$time = strtotime($date);
		} else if (is_numeric($date)) {
			$time = $date;
		} else {
			$time = round(microtime(true), 3);
		}

		return new \MongoDB\BSON\UTCDateTime($time * 1000);
	}

	/**
	 * Checks the Default MongoDb types
	 * @param  mixed  $value 
	 * @param  string  $type  Name of the basic type
	 * @return boolean
	 */
	public static function isType($value, $type = '') {
		switch ($type) {
			case 'id':
				return is_object($value) && is_a($value, Db::ID);

			case 'regex':
				return is_object($value) && is_a($value, Db::REGEX);

			case 'document':
				return (is_object($value) && (
					is_a($value, 'MongoDB\Model\BSONArray') ||
					is_a($value, 'MongoDB\Model\BSONDocument') ||
					is_a($value, 'stdClass')
				));
			
			case 'date':
			case 'datetime':
			case 'time':
				return is_object($value) && is_a($value, Db::DATE);

			default:
				return is_object($value) && is_a($value, Db::ID);
		}
	}

	public static function dateQuery($start = null, $end = null) {
		$changed = false;
		if ($start && $end) {
			if (self::isType($start, 'date') && self::isType($end, 'date')) {
				$dq = ['start' => $start, 'end' => $end];
				$changed = true;
			}
		}

		if (!$changed) {
			$dq = \Shared\Utils::dateQuery(['start' => $start, 'end' => $end]);	
		}

		$result = [];
		if ($start) {
			$result['$gte'] = $dq['start'];
		}

		if ($end) {
			$result['$lte'] = $dq['end'];
		}
		return $result;
	}

	/**
	 * To format a key before storing it in DB
	 * @param  string $key Key value
	 * @return string
	 */
	public static function formatKey($key, $format = true) {
		if (strlen($key) == 0) {
			$key = "Empty";
		}

		if ($format) {
			$key = str_replace(".", "-", $key);	
		} else {	// decode the key
			$key = str_replace("-", ".", $key);
		}
		return $key;
	}

	public static function opts($fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$opts = [];

		if (!empty($fields)) {
			$opts['projection'] = $fields;
		}
		
		if ($order && $direction) {
			switch ($direction) {
				case 'desc':
				case 'DESC':
					$direction = -1;
					break;
				
				case 'asc':
				case 'ASC':
					$direction = 1;
					break;

				default:
					$direction = -1;
					break;
			}
			if (is_array($order)) {
				foreach ($order as $key => $o) {
					if (is_numeric($key)) {
						$opts['sort'][$o] = $direction;
					} else {
						if (is_numeric($o)) {
							$opts['sort'][$key] = $o;
						} else {
							$opts['sort'][$key] = $direction;
						}
					}
				}
			} else {
				$opts['sort'] = [$order => $direction];
			}
		}

		if ($page) {
			$opts['skip'] = $limit * ($page - 1);
		}

		if ($limit) {
			$opts['limit'] = (int) $limit;
		}
		return $opts;
	}

	public static function collection($model) {
		$model = "\\$model";
		$m = new $model;

		return $m->getCollection();
	}

	public static function getLimitOpts($findOpts = []) {
		$order = $findOpts['order'] ?? null;
		$direction = $findOpts['direction'] ?? null;
		$limit = $findOpts['limit'] ?? null;
		$page = $findOpts['page'] ?? null;
		$maxTimeMS = $findOpts['maxTimeMS'] ?? null;
		return [$order, $direction, $limit, $page, $maxTimeMS];
	}

	public static function selectAll($model, $query, $fields, $findOpts = []) {
		$model = "\\$model";
		$m = new $model;
		$where = $m->_updateQuery($query);
		$dbfields = $m->_updateFields($fields);

		list($order, $direction, $limit, $page, $maxTimeMS) = self::getLimitOpts($findOpts);
		$opts = self::opts($dbfields, $order, $direction, $limit, $page);
		$collection = $m->getCollection();
		$maxTimeMS = $findOpts['maxTimeMS'] ?? null;
		if ($maxTimeMS) {
			$opts['maxTimeMS'] = $maxTimeMS;
		}
		$cursor = $collection->find($where, $opts);
		
		return self::_findAll($cursor, $fields);
	}

	/**
	 * Wrapper for database find which also converts the returned records to simple objects
	 * @param string $model Name of the model
	 * @param array $query Database Query
	 * @param array $fields Fields to be selected
	 * @param string|null $order Possible values -> 'asc', 'desc'
	 * @param integer|null $limit Number of records to be returned at a time
	 * @param integer|null $page Offset no of pages
	 * @return array 	Array of objects
	 */
	public static function findAll($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$cursor = self::query($model, $query, $fields, $order, $direction, $limit, $page);
		return static::_findAll($cursor, $fields);
	}

	protected static function _findAll($cursor, $fields) {
		$results = [];
		if (!is_array($fields)) $fields = [];
		foreach ($cursor as $c) {
			$newObj = self::simplifyDoc($c);
			if (count($fields) > 0) {
				$obj = [];
				foreach ($fields as $f) {
					$obj[$f] = $newObj->$f ?? null;
				}
				$obj = (object) $obj;
			} else {
				$obj = $newObj;
			}
			$id = $obj->_id ?? null;

			// to maintain backwards compatibility with SQL syntax
			if (!property_exists($obj, 'id')) {
				$obj->id = $id;
			}

			if (!is_null($id) && is_string($id)) {
				$results[$id] = $obj;
			} else {
				$results[] = $obj;
			}
		}
		return $results;
	}

	/**
	 * Wrapper for database findOne which also converts the returned records to simple objects
	 * @return array 	Array of objects
	 */
	public static function first($model, $query, $fields = [], $order = null, $direction = null) {
		$cursor = self::query($model, $query, $fields, $order, $direction, 1);
		$result = null;
		foreach ($cursor as $c) {
			$obj = self::simplifyDoc($c);
			$id = $obj->_id ?? null;

			// to maintain backwards compatibility with SQL syntax
			if (!property_exists($obj, 'id')) {
				$obj->id = $id;
			}
			$result = $obj;
		}
		return $result;
	}

	public static function cacheFirst($model, $query, $fields = [], $order = null, $direction = null) {
		$cacheKey = static::getCacheKey($model, $query, $fields);
		$foundCache = Utils::getCache($cacheKey, false);

		if ($foundCache === false) {
			$foundCache = static::first($model, $query, $fields, $order, $direction);
			Utils::setCache($cacheKey, $foundCache);
		}
		return $foundCache;
	}

	/**
	 * Simplify the database document (Equivalent Term => Mysql Row) to an object
	 * containing properties and values
	 * @param  mixed $doc Document provided
	 * @return object
	 */
	public static function simplifyDoc($doc) {
		$obj = $arr = Utils::toArray($doc);
		foreach ($obj as $k => $value) {
			$arr[$k] = self::simplifyValue($value);
		}
		$obj = (object) $arr;
		return $obj;
	}

	public static function simplifyMongoArray($arr) {
		$result = [];
		foreach ($arr as $v) {
			$result[] = static::simplifyValue($v);
		}
		return $result;
	}

	/**
	 * Convert the Database objects to simple objects that can be used by the language
	 * @param  mixed $value  Mixed object generally of type -> \MongoDB\BSON\*
	 * @return mixed
	 */
	public static function simplifyValue($value) {
		if (is_object($value)) {
			if (self::isType($value, 'id')) {
				$raw = Utils::getMongoID($value);
			} else if (self::isType($value, 'date')) {
				$v = $value->toDateTime();
				$raw = $v;
			} else if (self::isType($value, 'document')) {
				$raw = Utils::toArray($value);
			} else if (self::isType($value, 'regex')) {
				$raw = $value->getPattern();
			} else {    // fallback case
				$raw = (object) $value;
			}
		} else {
			$raw = $value;
		}
		return $raw;
	}

	/**
	 * Aggregate High Level Wrapper
	 * @param  string  $model  the name of the model to be passed
	 * @param  array  $query  the query used to search records in DB
	 * @param  array  $groupBy Array of fields by which records are to be grouped
	 * @param  array $extra   Extra Params Keys => (sort, count, limit)
	 * @return array 		Array of objects (i.e. records)
	 */
	public static function aggregate($model, $query, $groupBy, $extra = []) {
		$project = ['_id' => 0]; $group = ['_id' => []];
		foreach ($groupBy as $f) {
			$project[$f] = 1;
			$group['_id'][$f] = sprintf("$%s", $f);
		}
		if (count($groupBy) === 1) {
			$group['_id'] = array_values($group['_id'])[0];
		}

		$countByField = $extra['count'] ?? false;
		if ($countByField) {	// when we need to sum by a specific field
			$project[$countByField] = 1;
			$group['count'] = ['$sum' => sprintf("$%s", $countByField)];
		} else {	// When we need to sum all the records by grouping
			$group['count'] = ['$sum' => 1];
		}
		if (isset($extra['conditionalCount'])) {
			$conditionalCount = $extra['conditionalCount'];
			foreach ($conditionalCount as $arr) {
				$project[$arr['field']] = 1;
				$group[$arr['key']] = ['$sum' => $arr['sum']];
			}
		}
		if (isset($extra['hourly'])) {
			$project['hour'] = ['$hour' => ['date' => '$created', 'timezone' => $extra['timezone']]];
			$group['_id']['hour'] = '$hour';
		}
		if (isset($extra['groupProjection'])) {
			foreach ($extra['groupProjection'] as $groupProjection) {
				$operator = $groupProjection['operator'] ?? '$sum';
				$operatorField = $groupProjection['operateOn'];
				$projectField = $groupProjection['includeFields'];
				foreach ($projectField as $pf) {
					$project[$pf] = 1;
				}

				$group[$groupProjection['key']] = [$operator => $operatorField];
			}
		}

		/***** @todo - See this grouping *******/
		$groupByDate = $extra['groupByDate'] ?? false;
		if ($groupByDate) {
			$group['_id'][$groupByDate] = ['$dateToString' => ['format' => "%Y-%m-%d", 'date' => sprintf("$%s", $groupByDate)]];
		}

		$aggQuery = [
			['$match' => $query],
			['$project' => $project],
			['$group' => $group]
		];

		// Check for sorting of records
		$sort = $extra['sort'] ?? false;
		if ($sort) {
			$aggQuery[] = ['$sort' => ['count' => -1]];
		}

		// Check if we need to limit the no of records: generally used after sorting
		$limit = $extra['limit'] ?? false;
		if ($limit) {
			$aggQuery[] = ['$limit' => (int) $limit];
		}

		if (\Framework\Registry::get("DEBUG")) {
			var_dump($aggQuery);
		}
		$adnOpts = [];
		$maxTimeMS = $extra['maxTimeMS'] ?? null;
		if ($maxTimeMS) {
			$adnOpts['maxTimeMS'] = $maxTimeMS;
		}
		return self::collection($model)->aggregate($aggQuery, $adnOpts);
	}

	// query method
	public static function query($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$model = "\\$model";
		$m = new $model;
		$where = $m->_updateQuery($query);
		$fields = $m->_updateFields($fields);
		
		return self::_query($m, $where, $fields, $order, $direction, $limit, $page);
	}

	public static function queryWithOpts($model, $query, $fields = [], $findOpts = []) {
		$model = "\\$model";
		$m = new $model;
		$where = $m->_updateQuery($query);
		$fields = $m->_updateFields($fields);

		list($order, $direction, $limit, $page, $maxTimeMS) = self::getLimitOpts($findOpts);
		$opts = self::opts($fields, $order, $direction, $limit, $page);
		$collection = $m->getCollection();
		$maxTimeMS = $findOpts['maxTimeMS'] ?? null;
		if ($maxTimeMS) {
			$opts['maxTimeMS'] = $maxTimeMS;
		}
		
		return $collection->find($where, $opts);
	}

	protected static function _query($model, $where, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$collection = $model->getCollection();

		$opts = self::opts($fields, $order, $direction, $limit, $page);
		return $collection->find($where, $opts);
	}

	public static function count($model, $query) {
		$model = "\\$model";
		$m = new $model;
		$where = $m->_updateQuery($query);

		$collection = $m->getCollection();
		return $collection->count($where);
	}

	public static function findAllWithoutId($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$records = static::findAll($model, $query, $fields, $order, $direction, $limit, $page);
		$records = array_map(function ($r) {
			unset($r->id);
			return $r;
		}, $records);
		return $records;
	}

	public static function cacheAll($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		return static::cacheQuery($model, $query, $fields, $order, $direction, $limit, $page);
	}

	public static function cacheQuery($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$cacheKey = static::getCacheKey($model, $query, $fields);
		$foundCache = Utils::getCache($cacheKey, false);

		if ($foundCache === false) {
			$foundCache = static::findAll($model, $query, $fields, $order, $direction, $limit, $page);
			Utils::setCache($cacheKey, $foundCache);
		}
		return $foundCache;
	}

	/**
	 * [PUBLIC] This static method same as cacheAll but with maxTimeMS option supported.
	 */
	public static function cacheAllv2($model, $query = [], $fields = [], $findOpts = []) {
		$cacheKey = static::getCacheKey($model, $query, $fields);
		$foundCache = Utils::getCache($cacheKey, false);
		if ($foundCache === false) {
			$cursor = static::queryWithOpts($model, $query, $fields, $findOpts);
			$foundCache = static::_findAll($cursor, $fields);
			Utils::setCache($cacheKey, $foundCache);	
		}
		return $foundCache;
	}

	public static function iterateAll($model, $query, $fields, $totalRecords, $orderBy = '_id') {
		if (!is_numeric($totalRecords)) {
			throw new \Exception('Invalid argument for $totalRecords');
		}

		if (!is_array($fields)) {
			throw new \Exception("Fields should be an array!!");
		}

		$limit = 25000;
		for ($page = 1; $page <= (int)($totalRecords / $limit) + 1; $page++) {
			$results = static::findAll($model, $query, $fields, $orderBy, 'desc', $limit, $page);
			yield $results;
		}
	}

	public static function fixOidInQuery($query) {
		foreach ($query as $key => $value) {
			if (is_array($value) && isset($value['$in'])) {
				foreach ($value['$in'] as $k => $v) {
					if (is_array($v) && isset($v['$oid'])) {
						$v = $v['$oid'];
						$value['$in'][$k] = $v;
					}
				}
				$query[$key] = $value;
			}
		}
		return $query;
	}
}
