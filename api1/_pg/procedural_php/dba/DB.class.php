<?php 
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

require_once(dirname(__FILE__)."/DataBaseDriver.class.php");
require_once(dirname(__FILE__)."/DataBaseQueryParser.class.php");
require_once(dirname(__FILE__)."/MySQLDriver.class.php");

class DB {
	
	private static $currentConnectionName;
	
	private static $currentConnection;
	
	private static $connectionInfos = array();
	
	private static $connections = array();
	
	private static $defaltRegistPatameters = array();

	private static $setupAutoClose = false;
	
	public static function init($driverName, $host, $user, $pass, $db, $connectionName = "___default") {

		self::$connectionInfos[$connectionName] = array(
			"driverName" =>$driverName
			, "host"	 =>$host
			, "user"	 =>$user
			, "pass"	 =>$pass
			, "db"	  	 =>$db
		); 
		
		self::$currentConnectionName = $connectionName;
	}
	
	public static function connect() {
		
		$info = self::$connectionInfos[self::$currentConnectionName];
		$driverName = $info["driverName"];
		$host       = $info["host"];
		$user		= $info["user"];
		$pass       = $info["pass"];
		$db			= $info["db"];
		$connectionName = self::$currentConnectionName;
	
		$driver = null;
		
		if ($driverName == "mysql") {
			$driver = new MySQLDriver();
		} else 	if ($driverName == "odbc") {
			$driver = new ODBCDriver();
		} else {
			throw new Exception("driver not supported [{$driverName}]");
		}
		
		$link = $driver->connect($connectionName, $host, $user, $pass, $db);
		
		$driver->setQueryParser(new DataBaseQueryParser());
		$driver->setLink($link);
		$driver->setConnectionName($connectionName);
		$driver->setDefaultParameters(self::$defaltRegistPatameters);
		
		$driver->init();
		
		self::$connections[$connectionName] = $driver;
		self::setCurrentConnection($connectionName);

		if (!self::$setupAutoClose) {
		 	// DBの切断を予約
	 		register_shutdown_function("DB::autoAllClose");
			
	 		self::$setupAutoClose = true;
		}
	}
	
	public static function autoAllClose() {

		if (Globals::$errored) {
			self::rollbackAll();
		} else {
			self::commitAll();
		}
		
		self::closeAll();	
	}
	
	
	// 利用するリンク名を設定する
	public static function setCurrentConnection($name) {
		DB::$currentConnectionName = $name;
		DB::$currentConnection = DB::$connections[$name];
	}
	
	public static function getCurrentConnection() {
		return $this->currentConnectionName;
	}
	
	public static function commitAll() {
		foreach (DB::$connections as $connectionName=>$driver) {
			$driver->commit();
		}
	}
	
	public static function rollbackAll() {
		foreach (DB::$connections as $connectionName=>$driver) {
			$driver->rollback();
		}
	}
	
	public static function closeAll() {
		foreach (DB::$connections as $connectionName=>$driver) {
			$driver->close();
		}
	}
	
	// ---------------------- 下記、委譲メソッド
	public static function debug($sql, $params = array()) {
		$sql = self::parseQuery($sql, $params);
		debugLog($sql);
		ddie($sql);
	}
	public static function getLastSql() {
		return DB::$currentConnection->getLastSql();
	}
	public static function parseQuery($sql, $params) {
		return DB::$currentConnection->parseQuery($sql, $params);
	}
	public static function setDefaultParameters($defaultParameters) {
		self::$defaltRegistPatameters = $defaultParameters;
	}
	public static function begin() {
		DB::$currentConnection->begin();
	}
	public static function commit() {
		DB::$currentConnection->commit();
	}
	public static function rollback() {
		DB::$currentConnection->rollback();
	}
	public static function close() {
		DB::$currentConnection->close();
		unset(DB::$connections[DB::$currentConnectionName]);
		DB::$currentConnection = null;
	}
	public static function selectPagerArray(&$pageInfo, $sql, $order, $params) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->selectPagerArray($pageInfo, $sql, $order, $params);
	}
	public static function insertedId() {
		return DB::$currentConnection->insertedId();
	}
	public static function selectOne($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->selectOne($sql, $params);
	}
	public static function exists($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->exists($sql, $params);
	}
	public static function selectConcatArray($sql, $params = array(), $delim = ", ", $options = false) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->selectConcatArray($sql, $params, $delim, $options);
	}
	public static function query($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->query($sql, $params);
	}
	public static function selectRow($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->selectRow($sql, $params);
	}
	public static function selectArray($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->selectArray($sql, $params);
	}
	public static function selectKeyValue($sql, $params = array(), $indexColumn = 0, $nameColumn = 1) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->selectKeyValue($sql, $params, $indexColumn, $nameColumn);
	}
	public static function selectKeyRow($sql, $params = array(), $indexColumn = 0) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->selectKeyRow($sql, $params, $indexColumn);
	}
	public static function selectOneArray($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->selectOneArray($sql, $params);
	}
	public static function escape($val) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->escape($val);
	}
	public static function escapeLike($str) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->escapeLike($str);
	}
	public static function delete($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->delete($sql, $params);
	}
	public static function update($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->update($sql, $params);
	}
	public static function insert($sql, $params = array()) {
		if (self::$currentConnection == null) self::connect();
		return DB::$currentConnection->insert($sql, $params);
	}
	public static function updated() {
		return DB::$currentConnection->updated();
	}
	public static function getExecSql() {
		if (self::$currentConnection == null) return null;
		return DB::$currentConnection->getExecSql();
	}
	public static function enableAutocommit() {
		return DB::$currentConnection->enableAutocommit();
	}
	
}
