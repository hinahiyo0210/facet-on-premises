<?php 

// smaryをロード
require_once(DIR_LIB."/smarty/libs/Smarty.class.php");
require_once(DIR_LIB."/smarty/libs/Autoloader.php");
Smarty_Autoloader::register();

/**
 * smartyの拡張クラス。共通的な処理がある場合はここでオーバーライド。
 *
 */
class Smarty_Ex extends Smarty {
	
	private $bufferDisplay = false;
	
	private $bufferedDisplay = "";
	
	public $autoTemplateDir = true;
	
	public function getIncludeDisplay($inc) {
		$this->bufferDisplay = true;
		
		include($inc);
		
		$this->bufferDisplay = false;
		
		return $this->bufferedDisplay;
		
	}
	
	/**
	 * displayメソッドのオーバーライド
	 */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
    	if ($this->bufferDisplay) {
    			
			$html = $this->fetch($template, $cache_id, $compile_id, $parent);
    		$this->bufferedDisplay = $html;
			
    	} else {
			parent::display($this->getDirTemplate($template), $cache_id, $compile_id, $parent);
    		
    	}

    }
    
    public function template_exists($template = null) {
    	$template = $this->getDirTemplate($template);
    	return parent::template_exists($template);
    }
    
	/**
	 * fetchメソッドのオーバーライド
     */
    public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false) {
    	
		return parent::fetch($this->getDirTemplate($template), $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
    }

    private function getDirTemplate($template) {
    	
    	// ディレクトリ指定が無い場合は、phpファイルと同一ディレクトリを対象にする
		if ($this->autoTemplateDir && !exists($template, "/")) {
			$path = pathinfo($_SERVER["SCRIPT_FILENAME"]);
			$dir = $path["dirname"];
			
			$template = $dir."/".$template;
		}
    	
		return $template;
		
    }
    
}

// smartyをセットアップ
Globals::$smarty = new Smarty_Ex();
// Globals::$smarty->template_dir = DIR_APP.'/';
Globals::$smarty->setTemplateDir(DIR_APP.'/');
// Globals::$smarty->compile_dir  = realpath(DIR_TMP.'/smarty/templates_c/');
Globals::$smarty->setCompileDir(DIR_TMP.'/smarty/templates_c/');
// Globals::$smarty->cache_dir    = realpath(DIR_TMP.'/smarty/cache/');
Globals::$smarty->setCacheDir(DIR_TMP.'/smarty/cache/');

// デフォルトでhtmlエスケープ処理
Globals::$smarty->default_modifiers = array('smarty_default_htmlspecialchars');

// Smarty4.3を使用する場合の関数、クラスの定義
Globals::$smarty->registerPlugin('modifier', 'smarty_default_htmlspecialchars', 'smarty_default_htmlspecialchars');
Globals::$smarty->registerPlugin('modifier', 'filemtime', 'filemtime');
Globals::$smarty->registerPlugin('modifier', 'strtolower', 'strtolower');
Globals::$smarty->registerPlugin('modifier', 'req', 'req');
Globals::$smarty->registerPlugin('modifier', 'hjs', 'hjs');
Globals::$smarty->registerPlugin('modifier', 'seqId', 'seqId');
Globals::$smarty->registerPlugin('modifier', 'date', 'date');
Globals::$smarty->registerPlugin('modifier', 'formatNumber', 'formatNumber');
Globals::$smarty->registerPlugin('modifier', 'nval', 'nval');
Globals::$smarty->registerPlugin('modifier', 'exists', 'exists');
Globals::$smarty->registerPlugin('modifier', 'array_merge', 'array_merge');
Globals::$smarty->registerPlugin('modifier', 'array_keys', 'array_keys');
Globals::$smarty->registerPlugin('modifier', 'replaceUrl', 'replaceUrl');
Globals::$smarty->registerPlugin('modifier', 'concat', 'concat');
Globals::$smarty->registerPlugin('modifier', 'strtotime', 'strtotime');
Globals::$smarty->registerPlugin('modifier', 'array_search', 'array_search');
Globals::$smarty->registerPlugin('modifier', 'formatDate', 'formatDate');
Globals::$smarty->registerPlugin('modifier', 'count', 'count');
Globals::$smarty->registerPlugin('modifier', 'base_convert', 'base_convert');
Globals::$smarty->registerPlugin('modifier', 'sprintf', 'sprintf');
Globals::$smarty->registerPlugin('modifier', 'var_dump', 'var_dump');
Globals::$smarty->registerPlugin('modifier', 'startsWith', 'startsWith');
Globals::$smarty->registerPlugin('modifier', 'formatTime', 'formatTime');
Globals::$smarty->registerPlugin('modifier', 'explode', 'explode');
Globals::$smarty->registerPlugin('modifier', 'is_null', 'is_null');
Globals::$smarty->registerPlugin('modifier', 'h', 'h');

Globals::$smarty->registerClass('Session', 'Session');
Globals::$smarty->registerClass('Errors', 'Errors');
Globals::$smarty->registerClass('Validator', 'Validator');
Globals::$smarty->registerClass('Filter', 'Filter');
Globals::$smarty->registerClass('Enums', 'Enums');
Globals::$smarty->registerClass('SimpleEnums', 'SimpleEnums');
Globals::$smarty->registerClass('UiRecogLogService', 'UiRecogLogService');

function smarty_default_htmlspecialchars($s) {
  $s = ($s !== null) ? $s : "";
	return htmlspecialchars($s, ENT_QUOTES, BASE_ENCODE, true);
}
