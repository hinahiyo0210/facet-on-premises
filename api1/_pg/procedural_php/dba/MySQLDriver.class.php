<?php
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

class MySQLDriver extends DataBaseDriver {

	private $defaultParameters;
	
	private $lastInsertedId;
	
	private $lastSql;
	
	private $execSql;
	
	
	/**
	 * トランザクションを開始
	 */
	public function begin() {
		$this->query("begin");
	}
	
	/**
	 * コミット
	 */
	public function commit() {
		$this->query("commit");
	}
	
	
	/**
	 * ロールバック
	 */
	public function rollback() {
		$this->query("rollback");
	}
	
	
	
	/**
	 * DB接続を切断する
	 */
	public function close() {
		mysqli_close($this->link);
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
		
		$list = $this->selectArray($sql." ".$order." limit ".$pageInfo->getLimit()." offset ".$pageInfo->getOffset(), $params);
		
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
	public function connect($linkName, $host, $user, $pass, $db) {
		
		if (!$link = mysqli_connect($host, $user, $pass, $db)){
			$msg = mysqli_error($link);
			$this->doError("接続に失敗：".$msg);
		}
	
		return $link;
	}

	public function init() {
		
		mysqli_query($this->link, "SET NAMES utf8");
		mysqli_query($this->link, "set autocommit = 0");
		mysqli_query($this->link, "begin");
		mysqli_set_charset($this->link, "utf8");
		
	}
	
	/**
	 * 単一値を検索する
	 * @param string $sql SQL
	 */
	public function selectOne($sql, $params = array()) {
	
		$rs = $this->query($sql, $params);
		
		if ($row = mysqli_fetch_array($rs)) {
			return $row[0];
		}
		
		return $row;
	}
	
	/**
	 * 検索値が一件でも存在する場合にtrueを返す
	 * @param string $sql SQL
	 */
	public function exists($sql, $params = array()) {
		
		$rs = $this->query($sql, $params);
		
		if ($row = mysqli_fetch_array($rs)) {
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
		
		while ($row = mysqli_fetch_array($rs)) {
			
			$val = $row[0];
			
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
		
		$this->execSql = $sql;
		$this->lastSql = $sql;
		
		if (!($rs = mysqli_query($this->link, $sql))) {
			$msg = mysqli_error($this->link);
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
		$row = mysqli_fetch_assoc($rs);
		
		if ($row === false) {
			return null;
		}
		
		return $row;
	}
	
	/**
	 * 検索を実行し結果を配列で返す
	 * @param string $sql SQL
	 */
	public function selectArray($sql, $params = array()) {
		$ret = array();
		
		$rs = $this->query($sql, $params);
		
		while ($row = mysqli_fetch_assoc($rs)) {
			$ret[] = $row;
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
		
		if (ctype_digit($indexColumn) || ctype_digit($nameColumn)) {
			while ($row = mysqli_fetch_array($rs)) {
				$ret[$row[$indexColumn]] = $row[$nameColumn];
			}
		} else {
			while ($row = mysqli_fetch_assoc($rs)) {
				$ret[$row[$indexColumn]] = $row[$nameColumn];
			}
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
		
		if (ctype_digit($indexColumn)) {
			while ($row = mysqli_fetch_array($rs)) {
				$ret[$row[$indexColumn]] = $row;
			}
		} else {
			while ($row = mysqli_fetch_assoc($rs)) {
				$ret[$row[$indexColumn]] = $row;
			}
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
		
		while ($row = mysqli_fetch_array($rs)) {
			$ret[] = $row[0];
		}
		
		return $ret;
	}
	
	
	/**
	 * DB特殊文字をエスケープ
	 * @param string $val 対象値
	 */
	public function escape($val) {
		if ($val == null) return $val;
		return mysqli_real_escape_string($this->link, $val);
	}
	
	
	/**
	 * likeに使用する文字列のエスケープ
	 * @param string $val 対象値
	 */
	public function escapeLike($str) {
		
		// 円マーク(バックスラッシュ)をLIKE用に2重化
		$str = str_replace("\\","\\\\",$str);
		
		// LIKEで使われるワイルドカード(%)をエスケープ処理
		$str = str_replace("%", "\%", $str);
		
		// LIKEで使われるワイルドカード(_)をエスケープ処理
		$str = str_replace("_", "\_",$str);
		
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
	 */
	public function insert($sql, $params = array()) {
		$this->query($sql, $params);
		$ret = mysqli_insert_id($this->link);
		$this->lastInsertedId = $ret;
		return $ret;
	}
	
	
	
	/**
	 * 結果行数を取得
	 */
	public function updated() {
		return mysqli_affected_rows($this->link);
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
