<?php 

// smaryをロード
require_once(DIR_LIB."/smarty/libs/Smarty.class.php");

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
Globals::$smarty->template_dir = DIR_APP.'/';
Globals::$smarty->compile_dir  = realpath(DIR_TMP.'/smarty/templates_c/');
Globals::$smarty->cache_dir    = realpath(DIR_TMP.'/smarty/cache/');

// デフォルトでhtmlエスケープ処理
Globals::$smarty->default_modifiers = array('smarty_default_htmlspecialchars');

// Smarty4.3を使用する場合の関数、クラスの定義
Globals::$smarty->registerPlugin('modifier', 'smarty_default_htmlspecialchars', 'smarty_default_htmlspecialchars');

// Globals::$smarty->registerClass('Session', 'Session');

function smarty_default_htmlspecialchars($s) {
	return htmlspecialchars($s, ENT_QUOTES, BASE_ENCODE, true);
}
