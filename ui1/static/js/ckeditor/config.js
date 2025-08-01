/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	config.language = 'ja';
	config.extraPlugins  = 'filelib,videoembed,youtube';
	config.enterMode = CKEDITOR.ENTER_BR;
	config.font_names='メイリオ;ＭＳ Ｐゴシック;ＭＳ Ｐ明朝;ＭＳ ゴシック;ＭＳ 明朝;Arial/Arial, Helvetica, sans-serif;Comic Sans MS/Comic Sans MS, cursive;Courier New/Courier New, Courier, monospace;Georgia/Georgia, serif;Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;Tahoma/Tahoma, Geneva, sans-serif;Times New Roman/Times New Roman, Times, serif;Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;Verdana/Verdana, Geneva, sans-serif';
	config.tabSpaces = 4;
	config.height = 500;

	config.allowedContent = true;
	
	config.forceEnterMode = true;	
	
	config.removePlugins = 'iframe';
	
	config.toolbar = [
				['Source','-','Templates']
				,['Bold','Italic','Underline','Strike','-','Subscript','Superscript']
				,['NumberedList','BulletedList','-','Outdent','Indent','Blockquote']
				,['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']
				,['Link','Unlink','Anchor']
				,['FileLib', 'Image','Youtube','Table','HorizontalRule','Smiley','SpecialChar','PageBreak']
				,['Styles','Format','Font','FontSize']
				,['TextColor','BGColor', 'RemoveFormat']
				,['ShowBlocks']
				
				];
					
};
