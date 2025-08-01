<?php 
/**
 * Copyright (c) 2015 wizway.co.jp Inc. All rights reserved.
 * 
 * 本プログラム一式はwizway.co.jpの資産であり、他者による無断利用を禁じます。
 * 
 */

/**
 * グローバル変数を定義(把握しやすいように、グローバル変数が必要な場合は全てここに定義する)
 *
 */
final class Globals {
	
	// サイト種別(userやadminなど、設定)
	public static $siteType = "user";
	
	// DB接続 array("link"=>$link, "allCommit"=>true, "allRollback"=>true, "allClose"=>true);
	public static $dbLinks = array();
	
	// 現在有効なDB接続名の文字列
	public static $currentDbLink;
	
	// log4phpのloggerインスタンス
	public static $logger;
	
	// エラーが発生した際にPHPの処理をdieで終了させる場合にtrue
	public static $errorDie = true;
	
	// ログの出力内容を変数へ蓄積する場合にtrue
	public static $bufferLogging = false;
	
	// 蓄積されたログ内容
	public static $bufferedLog = "";
		
	// エラーが発生するとtrueに書き換えられる
	public static $errored = false;
	
	
	// セッションが開始されている場合にtrue
	public static $session_started = false;
	
	
	// beginIsFirst()等で利用するフラグ
	public static $isFirstFlag = true;
	
	// 現在表示中のページがSSLページである場合にtrue
	public static $isSSL = false;
	
	// smartyオブジェクト
	public static $smarty;
	
	// SQL解析クラス
	public static $queryParser;
	
	// ガラケーサイトの場合にtrue
	public static $mob = false;
	
	// 処理中のコントローラー
	public static $controller;
	
	// 処理中のアクション
	public static $action;
	
	// 処理中のtpl
	public static $tpl;
	
	
}



