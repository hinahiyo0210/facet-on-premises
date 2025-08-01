<?php

/**
 * ページング処理に関する情報を保持するクラス
 * 
 *
 */
class PageInfo {
	
	// 一ページあたりの取得件数
	private $limit;

	// 取得された全体件数
	private $rowCount;
	
	// 現在のページNo
	private $pageNo;
	
	// ページ数
	private $pageCount;
	
	// ページングの際のURL
	private $listUrl;

	// ページングの際のURLの区切り文字
	private $pagerSep;
	
	// 現在の出力ページNo
	private $currentNo = 0;
	
	// ページングリンク表示の際の省略値from
	private $omissionFrom;
	
	// ページングリンク表示の際の省略値to
	private $omissionTo;	
	
	
	private $pageNoParamName;
	
	public $actionName = "";
	
	/**
	 * コンストラクタ
	 * @param $pageNo 現在のページno
	 * @param $limit 1ページあたりの行数
	 */
	public function __construct($pageNo, $limit, $omissionFrom = 3, $omissionTo = 4) {
		$pageNo = Filter::digit($pageNo);
		if ($pageNo == null) $pageNo = 1;
		
		$this->limit = -1;
		$this->rowCount = -1;
		$this->pageNo = -1;
		$this->pageCount = -1;
		$this->setPageNo($pageNo);
		$this->limit = $limit;

		$this->omissionFrom = $omissionFrom;
		$this->omissionTo = $omissionTo;
	
		$this->pageNoParamName = "pageNo";
	}
	
	
	
	public function setPageNoParamName($name) {
		$this->pageNoParamName = $name;
	}
	
	/**
	 * 次のページへ移動する
	 */
	public function nextPage() {
		$this->pageNo++;
	}
	
	
	/**
	 * 全体の取得件数を取得する
	 */
	public function getRowCount() {
		return (int) $this->rowCount;
	}
	
	/**
	 * 現在のページnoをセットしなおす
	 * @param $pageNo
	 */
	public function setPageNo($pageNo) {
		if ($pageNo <= 0) $pageNo = 1;
		
		$this->pageNo = $pageNo;
	}
	
	/**
	 * limitを取得
	 */
	public function getLimit() {
		return $this->limit;
	}
	
	/**
	 * offsetを取得
	 */
	public function getOffset() {
		if ($this->pageNo == 0) return 0;
		return ($this->pageNo - 1) * $this->limit;
	}
	
	public function isEmpty() {
		return $this->rowCount == 0;
	}
	public function isNotEmpty() {
		return $this->rowCount != 0;
	}
	
	
	/**
	 * 全体の行数をセット
	 * @param $rowCount
	 */
	public function setRowCount($rowCount) {
		$this->rowCount = $rowCount;
		
		if ($this->rowCount == 0) {
			$this->pageNo = 0;
		}
		
		if ($this->rowCount == 0 || $this->limit == 0) {
			$this->pageCount = 0;
		} else {
			$this->pageCount =
				floor($this->rowCount / $this->limit)
				+ ($this->rowCount % $this->limit != 0 ? 1 : 0);
		}
		
		$this->omissionFrom = $this->pageNo - $this->omissionFrom;
		$this->omissionTo = $this->pageNo + $this->omissionTo;
			
		while ($this->omissionFrom < 1) {
			$this->omissionFrom++;
			$this->omissionTo++;
		}
		
		if ($this->omissionTo > $this->pageCount) {
			$this->omissionTo = $this->pageCount;
		}
		
		if ($this->pageNo > $this->pageCount) {
			$this->pageNo = 1;
		}
		
	}
	
	/**
	 * 戻る系ページリンクを省略するべきである場合にtrue
	 */
	public function isPrevOmission() {
		return $this->omissionFrom > 1;
	}
	
	/**
	 * 次へ系ページリンクを省略するべきである場合にtrue
	 */
	public function isNextOmission() {
		return $this->omissionTo < $this->pageCount;
	}
	
	
	/**
	 * 行数をもとに現在のページnoをセットしなおす
	 * @param unknown_type $rowNo
	 */
	public function getPageNoByRowNo($rowNo) {
		return floor(($rowNo - 1) / $this->limit) + 1;
	}
	
	/**
	 * 次ページが存在する場合にtrue
	 */
	public function isEnableNextPage() {
		return $this->pageCount != 0 && $this->pageNo < $this->pageCount;
	}
	
	/**
	 * 前ページが存在する場合にtrue
	 */
	public function isEnablePrevPage() {
		return $this->pageCount != 0 && $this->pageNo > 1;
	}
	
	/**
	 * 次ページnoを取得
	 */
	public function getNextPageNo() {
		return $this->pageNo + 1;
	}
	
	/**
	 * 前ページnoを取得
	 */
	public function getPrevPageNo() {
		return $this->pageNo - 1;
	}
	
	
	/**
	 * 検索範囲となった行番号を取得：from
	 */
	public function getPageFrom() {
		if ($this->rowCount == 0) {
			return 0;
		}
		
		return $this->getOffset() + 1;
	}

	/**
	 * 検索範囲となった行番号を取得：to
	 */
	public function getPageTo() {
		if ($this->rowCount == 0) {
			return 0;
		}
		
		$page_to = $this->getOffset() + $this->getLimit();
		if ($page_to > $this->rowCount) {
			$page_to = $this->rowCount;
		}

		return $page_to;
	}
	
	/**
	 * 内包している情報を配列に変換して返す
	 */
	public function toArray() {
		$ret = array();
		
		$ret["pageNo"] = $this->pageNo;
		$ret["pageCount"] = $this->pageCount;
		$ret["enableNextPage"] = $this->isEnableNextPage();
		$ret["enablePrevPage"] = $this->isEnablePrevPage();
		$ret["nextPageNo"] = $this->getNextPageNo();
		$ret["prevPageNo"] = $this->getPrevPageNo();

		return $ret;
	}
	
	/**
	 * ページングリンクのURLを取得
	 * @param boolean $addPageNo
	 */
	public function getListUrl($addPageNo = false) {
		return $this->listUrl.($addPageNo ? $this->pagerSep.$this->pageNoParamName."=".$this->pageNo : "");
	}
	
	// 先頭ページのURLを取得
	public function getFirstUrl() {
		return $this->listUrl.$this->pagerSep.$this->pageNoParamName."=1";
	}
	
	// 最終ページのURLを取得
	public function getLastUrl() {
		return $this->listUrl.$this->pagerSep.$this->pageNoParamName."=".$this->getPageCount();
	}
	
	/**
	 * 「前へ」リンク用のURLを取得
	 */
	public function getPrevUrl() {
		
		if ($this->pageNo == 2) {
			return $this->listUrl;
		}

		return $this->listUrl.$this->pagerSep.$this->pageNoParamName."=".$this->getPrevPageNo();
	}
	
	/**
	 * 「次へ」リンク用のURLを取得
	 */
	public function getNextUrl() {
	
		return $this->listUrl.$this->pagerSep.$this->pageNoParamName."=".$this->getNextPageNo();
		
	}
	
	/**
	 * ページングリンクにおいて、現在処理中のページURLを取得
	 */
	public function getCurrentPageUrl() {
		
		if ($this->currentNo == 1) {
			return $this->listUrl;
		}
		
		return $this->listUrl.$this->pagerSep.$this->pageNoParamName.'='.$this->currentNo;
	}
	
	/**
	 * ページングリンクにおいて、現在処理中のページNoと表示中ページのNoが同一である場合はtrue
	 * @param unknown_type $echo
	 */
	public function isCurrent($echo = "") {
		
		if ($this->currentNo == $this->pageNo) {
			echo $echo;
			return true;
		}
		
		return false;
	}
	
	/**
	 * ページングリンクにおいて、現在処理中のページNoを取得
	 */
	public function getCurrentNo() {
		return $this->currentNo;
	}

	/**
	 * ページングリンクの処理を開始する際にコールする
	 */
	public function beginIterate() {
		
		$this->currentNo = $this->omissionFrom - 1;
	}
	
	/**
	 * ページングリンクの処理をイテレートする
	 */
	public function iterate() {
		$this->currentNo++;
		
		if ($this->currentNo >= $this->omissionTo + 1) {
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * ページングパラメータ用のURLを作成
	 * @param $form
	 * @param $exclude
	 */
	public function createListUrl($form, $exclude = null) {

		unset($form["pageNo"]);
		$url = getListUrl($form, true, $exclude);
		
		$reqUri = $_SERVER["REQUEST_URI"];
		
		$pos = mb_strpos($reqUri, "?");
		$reqUri = mb_substr($reqUri, 0, $pos);

		if ($reqUri == "") {
			if ($this->actionName == "index") {
				$reqUri = "./";
			} else {
				$reqUri = "./".$this->actionName;
			}
		}
		
		$this->listUrl = $reqUri.$url;
		$this->pagerSep = "?";
		if (exists($this->listUrl, "?")) $this->pagerSep = "&";
		
	}
	
	
	public function getPageNo() {
		return (int) $this->pageNo;
	}
	public function getPageCount() {
		return (int) $this->pageCount;
	}

}
