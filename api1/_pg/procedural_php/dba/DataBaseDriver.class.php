<?php
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

abstract class DataBaseDriver {
	
	protected $link;
	
	protected $connectionName;
	
	protected $queryParser;
	
	public function setLink($link) {
		$this->link = $link;
	}
	
	public function setConnectionName($name) {
		$this->connectionName = $name;
	}
	
	public function setQueryParser($queryParser) {
		$this->queryParser = $queryParser;
	}
	
	protected function doError($msg) {
		
		$throwMsg =
			"DBエラー　connectionName<".$this->connectionName.">\n".
			$msg;

		throw new \Exception($throwMsg);
		
	}
	
	public abstract function init();
	
	public abstract function parseQuery($sql, $params);
	public abstract function setDefaultParameters($defaultParameters);
	public abstract function begin();
	public abstract function commit();
	public abstract function rollback();
	public abstract function close();
	public abstract function selectPagerArray(&$pageInfo, $sql, $order, $params);
	public abstract function insertedId();
	public abstract function connect($linkName, $hos, $user, $pass, $db);
	public abstract function selectOne($sql, $params = array());
	public abstract function exists($sql, $params = array());
	public abstract function selectConcatArray($sql, $params = array(), $delim = ", ", $options = false);
	public abstract function query($sql, $params = array());
	public abstract function selectRow($sql, $params = array());
	public abstract function selectArray($sql, $params = array());
	public abstract function selectKeyValue($sql, $params = array(), $indexColumn = 0, $nameColumn = 1);
	public abstract function selectKeyRow($sql, $params = array(), $indexColumn = 0);
	public abstract function selectOneArray($sql, $params = array());
	public abstract function escape($val);
	public abstract function escapeLike($str);
	public abstract function delete($sql, $params = array());
	public abstract function update($sql, $params = array());
	public abstract function insert($sql, $params = array());
	public abstract function updated();
		
		
		

	
}
