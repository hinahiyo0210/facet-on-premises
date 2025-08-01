<?php

class ErrorController extends ApiBaseController {
	
	// @Override
	public function prepare(&$form) {
	}

	public function notFoundAction(&$form) {
		throw new ApiParameterException("page", "URLに誤りがあります。");
	}
	
	
}
