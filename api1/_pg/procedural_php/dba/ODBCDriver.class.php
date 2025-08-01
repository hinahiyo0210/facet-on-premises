<?php
/**
 * 
 */
class ODBCDriver extends DataBaseDriver {

	private $defaultParameters;
	
	private $lastInsertedId;
	
	private $lastSql;
	
	private $execSql;
	
	/**
	 * トランザクションを開始
	 */
	public function begin() {
		// 正常にトランザクション開始されない。
// 		$this->query("begin transaction");
	}
	
	/**
	 * コミット
	 */
	public function commit() {
		odbc_commit($this->link);
	}
	
	
	/**
	 * ロールバック
	 */
	public function rollback() {
		odbc_rollback($this->link);
	}
	
	
	/**
	 * DB接続を切断する
	 */
	public function close() {
		odbc_close($this->link);
	}
	
	
	public function setDefaultParameters($defaultParameters) {
		$this->defaultParameters = $defaultParameters;
	}
	
	/**
	 * ページングに対応した一覧検索を行う
	 * @param PageInfo $pageInfo PageInfoオブジェクト
	 * @param string $sql SQL
	 * @param string $order "order by"からはじまるSQL
	 */
	public function selectPagerArray(&$pageInfo, $sql, $order, $params) {
	
		// 件数取得
		$count = $this->selectOne("select count(*) from (".$sql.") _____cnt ", $params);
		$pageInfo->setRowCount($count);
		
		$list = $this->selectArray($sql." ".$order." offset ".$pageInfo->getOffset()." rows fetch next ".$pageInfo->getLimit()." rows only ", $params);
		
		return $list;
	}
	
	
	/**
	 * オートナンバーの値を取得
	 */
	public function insertedId() {
		return $this->lastInsertedId;
	}
	
	/**
	 * DB接続を行う
	 */
	public function connect($linkName, $dsn, $user, $pass, $db) {
		
		if (!$link = odbc_connect($dsn, $user, $pass)) {
			$msg = odbc_error($link).":".odbc_errormsg($link);
			$this->doError("接続に失敗：".$msg);
		}
		
		return $link;
	}

	public function init() {

		odbc_autocommit($this->link, false);
		
	}

	public function enableAutocommit() {

		odbc_autocommit($this->link, true);
		
	}
	
	/**
	 * 単一値を検索する
	 * @param string $sql SQL
	 */
	public function selectOne($sql, $params = array()) {
	
		$rs = $this->query($sql, $params);
		
		$row = false;
		
		odbc_fetch_into($rs, $row);
		
		return $this->dec($row[0]);
	}
	
	/**
	 * 検索値が一件でも存在する場合にtrueを返す
	 * @param string $sql SQL
	 */
	public function exists($sql, $params = array()) {
		
		$rs = $this->query($sql, $params);
		
		if ($row = odbc_fetch_array($rs)) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * 検索を行い、複数レコードの先頭カラムを1つの文字列に結合して返す
	 * @param string $sql SQL
	 * @param string $delim 結合時の区切り文字
	 * @param array $options コード値からラベル値に変更する場合のオプション配列
	 */
	public function selectConcatArray($sql, $params = array(), $delim = ", ", $options = false) {
		
		$ret = "";
		
		$rs = $this->query($sql, $params);
		
		while ($row = odbc_fetch_array($rs)) {
			$val = null;
			foreach ($row as $k=>$v) {
				$val = $v;
				break;
			}
			
			$val = $this->dec($val);
			
			if ($options !== false) {
				$val = arr($options, $val);
			}
			
			if ($ret != "") {
				$ret = $ret.$delim.$val;
			} else {
				$ret = $val;
			}
			
		}
		
		return $ret;
		
	}
	
	/**
	 * クエリを実行。エラーが発生した場合はログを出力
	 * @param string $sql SQL
	 */
	public function query($sql, $params = array()) {
		
		$sql = $this->parseQuery($sql, $params);
		
		if (!empty(DB_MB_CNV)) {
			$sql = mb_convert_encoding($sql, DB_MB_CNV);
		}

		$this->execSql = $sql;
		$this->lastSql = $sql;
		
		if (!($rs = odbc_exec($this->link, $sql))) {
			$msg = odbc_error($this->link).":".odbc_errormsg($this->link);
			$this->doError("\n[SQL実行エラー] -------------------- \n".$msg."\n[実行SQL] --------------------------\n".$sql."\n------------------------------------");
		}

		$this->execSql = "";
		
		return $rs;
	}
	
	/**
	 * クエリを作成。
	 * @param string $sql SQL
	 */
	public function parseQuery($sql, $params = array()) {
		
		if ($params != null && !is_array($params)) {
			$params = array("value"=>$params);
		}
				
		if (!empty($this->defaultParameters)) {
			foreach ($this->defaultParameters as $k=>$v) {
				if (!isset($params[$k])) {
					$params[$k] = $v;
				}
			}
		}
		
		$sql = $this->queryParser->parse($this, $sql, $params);
		
		return $sql;
	}
	
	/**
	 * 検索を実行し、先頭の1行を返す
	 * @param string $sql SQL
	 */
	public function selectRow($sql, $params = array()) {
		
		$rs = $this->query($sql, $params);
		$row = odbc_fetch_array($rs);
		
		if ($row === false) {
			return null;
		}
		
		$ret = [];
		foreach ($row as $k=>$v) {
			$ret[$this->dec($k)] = $this->dec($v);
		}
		
		return $ret;
	}
	
	/**
	 * 検索を実行し結果を配列で返す
	 * @param string $sql SQL
	 */
	public function selectArray($sql, $params = array()) {
		$ret = array();
		
		$rs = $this->query($sql, $params);
		
		while ($row = odbc_fetch_array($rs)) {
			
			$newRow = [];
			foreach ($row as $k=>$v) {
				$newRow[$this->dec($k)] = $this->dec($v);
			}
			$ret[] = $newRow;
		}
		
		return $ret;
	}
	
	/**
	 * 検索を実行し、結果を
	 * 
	 * $array[$indexColumn] = $row[$nameColumn];
	 * 
	 * の形式にして返す
	 * 
	 * @param string $sql SQL
	 * @param string $indexColumn 配列のキーとするカラム名
	 * @param string $nameColumn 配列の値とするカラム名
	 */
	public function selectKeyValue($sql, $params = array(), $indexColumn = 0, $nameColumn = 1) {
	
		$ret = array();
		$rs = $this->query($sql, $params);
		
		while ($row = odbc_fetch_array($rs)) {

			$newRow = [];
			foreach ($row as $k=>$v) {
				$newRow[$this->dec($k)] = $this->dec($v);
			}
			$row = $newRow;
			
			if ($indexColumn === 0) {
				foreach ($row as $k=>$v) {
					$indexColumn = $k;
					break;
				}
			}
			if ($nameColumn === 1) {
				$cnt = 0;
				foreach ($row as $k=>$v) {
					if ($cnt == 1) {
						$nameColumn = $k;
						break;
					}
					$cnt++;
				}
				
			}
			
			$ret[$row[$indexColumn]] = $row[$nameColumn];
		}
		
		return $ret;
	}
	
	
	/**
	 * 検索を実行し、結果を
	 * 
	 * $array[$indexColumn] = $row;
	 * 
	 * の形式にして返す
	 * 
	 * @param string $sql SQL
	 * @param string $indexColumn 配列のキーとするカラム名
	 */
	public function selectKeyRow($sql, $params = array(), $indexColumn = 0) {
	
		$ret = array();
		$rs = $this->query($sql, $params);
		
		while ($row = odbc_fetch_array($rs)) {
			
			$newRow = [];
			foreach ($row as $k=>$v) {
				$newRow[$this->dec($k)] = $this->dec($v);
			}
			$row = $newRow;
			
			if ($indexColumn === 0) {
				foreach ($row as $k=>$v) {
					$indexColumn = $k;
					break;
				}
			}
			$ret[$row[$indexColumn]] = $row;
		}
		
		return $ret;
	}
	
	
	/**
	 * 検索結果を単一カラムの配列で返す
	 * @param string $sql SQL
	 */
	public function selectOneArray($sql, $params = array()) {
		$ret = array();
		
		$rs = $this->query($sql, $params);
		
		$colName = false;
		while ($row = odbc_fetch_array($rs)) {
			
			if ($colName === false) {
				foreach ($row as $k=>$v) {
					$colName = $k;
					break;
				}
			}
			$ret[] = $this->enc($row[$colName]);
		}
		
		return $ret;
	}
	
	
	private function dec($val) {
		if (!empty(DB_MB_CNV)) {
			return mb_convert_encoding($val, "UTF-8", "CP932");
		}
		return $val;
	}
	
	private function enc($val) {
		return $val;
	}
	
	
	/**
	 * DB特殊文字をエスケープ
	 * @param string $val 対象値
	 */
	public function escape($val) {
		if ($val == null) return $val;
		
		$val = $this->enc($val);
		
		$val = str_replace("'", "''", $val);
		
		return $val;
	}
	
	/**
	 * likeに使用する文字列のエスケープ
	 * @param string $val 対象値
	 */
	public function escapeLike($str) {
		
		// LIKEで使われるワイルドカード(%)をエスケープ処理
		$str = mb_ereg_replace('%','\%',$str);
		
		// LIKEで使われるワイルドカード(_)をエスケープ処理
		$str = mb_ereg_replace('_','\_',$str);
		
		// 円マーク(バックスラッシュ)をLIKE用に2重化
		$str = mb_ereg_replace('\\\\','\\\\',$str);
		
		return $this->escape($str);
	}
	
	
	/**
	 * updateの実行
	 */
	public function update($sql, $params = array()) {
		$this->query($sql, $params);
		return $this->updated();
	}
	
	/**
	 * deleteの実行
	 */
	public function delete($sql, $params = array()) {
		$this->query($sql, $params);
		return $this->updated();
	}
	
	/**
	 * insertの実行
	 * 
	 * SQL Serverでinsertされたidを取得するには下記のように。
	 * DB::selectOne("insert into test2(aaa) OUTPUT INSERTED.idx values('AA')")
	 * 
	 */
	public function insert($sql, $params = array()) {
		$this->query($sql, $params);
	}
	
	
	/**
	 * 結果行数を取得
	 */
	public function updated() {
		return $this->selectOne("select @@ROWCOUNT");;
	}
	
	/**
	 * 最後に実行したSQLを取得。主にデバッグ用。
	 */
	public function getLastSql() {
		return $this->lastSql;
	}
	
	
	/**
	 */
	public function getExecSql() {
		return $this->execSql;
	}
	
	
}
