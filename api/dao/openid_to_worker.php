<?php
class DAO_OpenIDToWorker extends Cerb_ORMHelper {
	const ID = 'id';
	const OPENID_CLAIMED_ID = 'openid_claimed_id';
	const OPENID_URL = 'openid_url';
	const WORKER_ID = 'worker_id';
	
	private function __construct() {}

	static function getFields() {
		$validation = DevblocksPlatform::services()->validation();
		
		// int(10) unsigned
		$validation
			->addField(self::ID)
			->id()
			->setEditable(false)
			;
		// varchar(255)
		$validation
			->addField(self::OPENID_CLAIMED_ID)
			->string()
			->setMaxLength(255)
			;
		// varchar(255)
		$validation
			->addField(self::OPENID_URL)
			->string()
			->setMaxLength(255)
			;
		// int(10) unsigned
		$validation
			->addField(self::WORKER_ID)
			->id()
			;

		return $validation->getFields();
	}

	static function create($fields) {
		$db = DevblocksPlatform::services()->database();
		
		$sql = "INSERT INTO openid_to_worker () VALUES ()";
		$db->ExecuteMaster($sql);
		$id = $db->LastInsertId();
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'openid_to_worker', $fields);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('openid_to_worker', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_OpenIDToWorker[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::services()->database();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT id, openid_url, openid_claimed_id, worker_id ".
			"FROM openid_to_worker ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->ExecuteSlave($sql);
		
		return self::_getObjectsFromResult($rs);
	}
	
	/**
	 * @param integer $id
	 * @return Model_OpenIDToWorker	 */
	static function get($id) {
		if(empty($id))
			return null;
		
		$objects = self::getWhere(sprintf("%s = %d",
			self::ID,
			$id
		));
		
		if(isset($objects[$id]))
			return $objects[$id];
		
		return null;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_OpenIDToWorker[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		if(!($rs instanceof mysqli_result))
			return false;
		
		while($row = mysqli_fetch_assoc($rs)) {
			$object = new Model_OpenIDToWorker();
			$object->id = $row['id'];
			$object->openid_url = $row['openid_url'];
			$object->openid_claimed_id = $row['openid_claimed_id'];
			$object->worker_id = $row['worker_id'];
			$objects[$object->id] = $object;
		}
		
		mysqli_free_result($rs);
		
		return $objects;
	}
	
	static function delete($ids) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::services()->database();
		
		if(empty($ids))
			return;
		
		$ids_list = implode(',', $ids);
		
		$db->ExecuteMaster(sprintf("DELETE FROM openid_to_worker WHERE id IN (%s)", $ids_list));
		
		return true;
	}
	
	public static function deleteByWorkerIds($ids) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::services()->database();
		
		if(empty($ids))
			return;
		
		$ids_list = implode(',', $ids);
		
		$db->ExecuteMaster(sprintf("DELETE FROM openid_to_worker WHERE worker_id IN (%s)", $ids_list));
		
		return true;
	}
	
	public static function getSearchQueryComponents($columns, $params, $sortBy=null, $sortAsc=null) {
		$fields = SearchFields_OpenIDToWorker::getFields();
		
		list($tables,$wheres) = parent::_parseSearchParams($params, $columns, 'SearchFields_OpenIDToWorker', $sortBy);
		
		$select_sql = sprintf("SELECT ".
			"openid_to_worker.id as %s, ".
			"openid_to_worker.openid_url as %s, ".
			"openid_to_worker.openid_claimed_id as %s, ".
			"openid_to_worker.worker_id as %s ",
				SearchFields_OpenIDToWorker::ID,
				SearchFields_OpenIDToWorker::OPENID_URL,
				SearchFields_OpenIDToWorker::OPENID_CLAIMED_ID,
				SearchFields_OpenIDToWorker::WORKER_ID
			);
			
		$join_sql = "FROM openid_to_worker ";
		
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = self::_buildSortClause($sortBy, $sortAsc, $fields, $select_sql, 'SearchFields_OpenIDToWorker');
		
		$result = array(
			'primary_table' => 'openid_to_worker',
			'select' => $select_sql,
			'join' => $join_sql,
			'where' => $where_sql,
			'sort' => $sort_sql,
		);
		
		return $result;
	}
	
	/**
	 *
	 * @param array $columns
	 * @param DevblocksSearchCriteria[] $params
	 * @param integer $limit
	 * @param integer $page
	 * @param string $sortBy
	 * @param boolean $sortAsc
	 * @param boolean $withCounts
	 * @return array
	 */
	static function search($columns, $params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::services()->database();

		// Build search queries
		$query_parts = self::getSearchQueryComponents($columns,$params,$sortBy,$sortAsc);

		$select_sql = $query_parts['select'];
		$join_sql = $query_parts['join'];
		$where_sql = $query_parts['where'];
		$sort_sql = $query_parts['sort'];
		
		$sql =
			$select_sql.
			$join_sql.
			$where_sql.
			$sort_sql;
			
		// [TODO] Could push the select logic down a level too
		if($limit > 0) {
			if(false == ($rs = $db->SelectLimit($sql,$limit,$page*$limit)))
				return false;
		} else {
			if(false == ($rs = $db->ExecuteSlave($sql)))
				return false;
			$total = mysqli_num_rows($rs);
		}
		
		if(!($rs instanceof mysqli_result))
			return false;
		
		$results = array();
		
		while($row = mysqli_fetch_assoc($rs)) {
			$object_id = intval($row[SearchFields_OpenIDToWorker::ID]);
			$results[$object_id] = $row;
		}

		$total = count($results);
		
		if($withCounts) {
			// We can skip counting if we have a less-than-full single page
			if(!(0 == $page && $total < $limit)) {
				$count_sql =
					"SELECT COUNT(openid_to_worker.id) ".
					$join_sql.
					$where_sql;
				$total = $db->GetOneSlave($count_sql);
			}
		}
		
		mysqli_free_result($rs);
		
		return array($results,$total);
	}

};

class SearchFields_OpenIDToWorker extends DevblocksSearchFields {
	const ID = 'o_id';
	const OPENID_URL = 'o_openid_url';
	const OPENID_CLAIMED_ID = 'o_openid_claimed_id';
	const WORKER_ID = 'o_worker_id';
	
	static private $_fields = null;
	
	static function getPrimaryKey() {
		return 'openid_to_worker.id';
	}
	
	static function getCustomFieldContextKeys() {
		return array(
			'' => new DevblocksSearchFieldContextKeys('openid_to_worker.id', self::ID),
		);
	}
	
	static function getWhereSQL(DevblocksSearchCriteria $param) {
		if('cf_' == substr($param->field, 0, 3)) {
			return self::_getWhereSQLFromCustomFields($param);
		} else {
			return $param->getWhereSQL(self::getFields(), self::getPrimaryKey());
		}
	}
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		if(is_null(self::$_fields))
			self::$_fields = self::_getFields();
		
		return self::$_fields;
	}
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function _getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'openid_to_worker', 'id', $translate->_('dao.openid_to_worker.id'), null, true),
			self::OPENID_URL => new DevblocksSearchField(self::OPENID_URL, 'openid_to_worker', 'openid_url', $translate->_('dao.openid_to_worker.openid_url'), null, true),
			self::OPENID_CLAIMED_ID => new DevblocksSearchField(self::OPENID_CLAIMED_ID, 'openid_to_worker', 'openid_claimed_id', $translate->_('dao.openid_to_worker.openid_claimed_id'), null, true),
			self::WORKER_ID => new DevblocksSearchField(self::WORKER_ID, 'openid_to_worker', 'worker_id', $translate->_('dao.openid_to_worker.worker_id'), null, true),
		);
		
		// Sort by label (translation-conscious)
		DevblocksPlatform::sortObjects($columns, 'db_label');

		return $columns;
	}
};

class Model_OpenIDToWorker {
	public $id;
	public $openid_url;
	public $openid_claimed_id;
	public $worker_id;
};

class View_OpenIDToWorker extends C4_AbstractView {
	const DEFAULT_ID = 'openidtoworker';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		$this->name = $translate->_('OpenIDs');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_OpenIDToWorker::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_OpenIDToWorker::ID,
			SearchFields_OpenIDToWorker::OPENID_URL,
			SearchFields_OpenIDToWorker::OPENID_CLAIMED_ID,
			SearchFields_OpenIDToWorker::WORKER_ID,
		);
		
		$this->addColumnsHidden(array(
		));
		
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_OpenIDToWorker::search(
			$this->view_columns,
			$this->getParams(),
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc,
			$this->renderTotal
		);
		
		$this->_lazyLoadCustomFieldsIntoObjects($objects, 'SearchFields_OpenIDToWorker');
		
		return $objects;
	}

	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::services()->template();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		// Custom fields
		//$custom_fields = DAO_CustomField::getByContext(CerberusContexts::XXX);
		//$tpl->assign('custom_fields', $custom_fields);

		// [TODO] Set your template path
		$tpl->display('devblocks:/path/to/view.tpl');
	}

	function renderCriteriaParam($param) {
		$field = $param->field;
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	function getFields() {
		return SearchFields_OpenIDToWorker::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			case SearchFields_OpenIDToWorker::ID:
			case SearchFields_OpenIDToWorker::OPENID_URL:
			case SearchFields_OpenIDToWorker::OPENID_CLAIMED_ID:
			case SearchFields_OpenIDToWorker::WORKER_ID:
				$criteria = $this->_doSetCriteriaString($field, $oper, $value);
				break;
				
			case 'placeholder_number':
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
			case 'placeholder_date':
				$criteria = $this->_doSetCriteriaDate($field, $oper);
				break;
				
			case 'placeholder_bool':
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
				
			/*
			default:
				// Custom Fields
				if(substr($field,0,3)=='cf_') {
					$criteria = $this->_doSetCriteriaCustomField($field, substr($field,3));
				}
				break;
			*/
		}

		if(!empty($criteria)) {
			$this->addParam($criteria, $field);
			$this->renderPage = 0;
		}
	}
};

