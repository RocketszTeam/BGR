/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */


CKEDITOR.editorConfig = function (config) {

    // config.uiColor = '#AADC6E';  //設定背景色	
    config.language = 'zh';  //語系中文   
    //config.skin = 'v2';		//佈景主題
    config.resize_enabled = false;  //設定不能resize TEXTAREA
	/*
	width: '100%', //設定寬度
    config.height = 300;  //設定高度
	*/
    config.enterMode = CKEDITOR.ENTER_BR;     //換行使用br，不使用p tag
    config.allowedContent = true;
	config.fillEmptyBlocks = false; 
	
	//為加入額外字體  
    config.font_names = 'Arial/Arial, Helvetica, sans-serif;Comic Sans MS/Comic Sans MS, cursive;Courier New/Courier New, Courier, monospace;Georgia/Georgia, serif;Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;Tahoma/Tahoma, Geneva, sans-serif;Times New Roman/Times New Roman, Times, serif;Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;Verdana/Verdana, Geneva, sans-serif;新細明體;標楷體;微軟正黑體'; 

	
    //功能列設定	
    config.toolbar_Default =
	[
		['Source', '-', 'Templates'],
		['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'],
		['Find', 'Replace', '-', 'SelectAll'],
		['Maximize', 'ShowBlocks', '-', 'About'],
		['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'],
		['Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],		
        ['Link', 'Unlink', 'Anchor'],
		['Image', 'Flash', 'Table', 'CreateDiv', 'Iframe', 'HorizontalRule', 'Smiley', 'SpecialChar'], ['TextColor', 'BGColor'],
		['Styles', 'Format', 'Font', 'FontSize']
	];

    config.toolbar_Full =
	[
		['Source', '-', 'Save', 'NewPage', 'DocProps', 'Preview', 'Print', '-', 'Templates'],
		['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'],
		['Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'],
		['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
		['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'],
		['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', 'Iframe', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl'],
		['Link', 'Unlink', 'Anchor'],
		['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak'],
		['Styles', 'Format', 'Font', 'FontSize'],
		['TextColor', 'BGColor'],
		['Maximize', 'ShowBlocks', '-', 'About']
	];

    config.toolbar_Basic =
	[
        ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'],     
        ['Maximize', 'ShowBlocks', 'Smiley', '-', 'About'],
		['Cut', '-', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'],
        ['Styles', 'Format', 'Font', 'FontSize'],
		['TextColor', 'BGColor']
	];

    config.toolbar = 'Default';  //指定要使用哪一個功能列設定

    //檔案管理器串接(不使用將設定註解掉即可)
    //config.filebrowserImageBrowseUrl = '../filemanager/index.php';   //圖片用
	//config.filebrowserFlashBrowseUrl = '../filemanager/index.php';	 //Flash用
	//config.filebrowserBrowseUrl = '../filemanager/index.php';		 //超連結用

	config.filebrowserBrowseUrl = ASSETS_URL + '/admin/js/ckfinder/ckfinder.html';
	config.filebrowserImageBrowseUrl = ASSETS_URL + '/admin/js/ckfinder/ckfinder.html?Type=Images';
	config.filebrowserFlashBrowseUrl = ASSETS_URL + '/admin/js/ckfinder/ckfinder.html?Type=Flash';
	//config.filebrowserUploadUrl = ASSETS_URL + '/admin/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files'; //可上傳一般檔案
	config.filebrowserImageUploadUrl = ASSETS_URL + '/admin/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';//可上傳圖檔
	config.filebrowserFlashUploadUrl = ASSETS_URL + '/admin/js/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';//可上傳Flash檔案

    //更多設定請參閱http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html

};
