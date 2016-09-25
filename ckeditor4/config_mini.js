/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	
	CKEDITOR.config.toolbar_My =
	[
		['Cut','Copy','Paste','PasteText','PasteFromWord'],
		['Undo','Redo'],
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript', 'TextColor','BGColor']
	];
	
	CKEDITOR.config.toolbar = 'My';
	
	
	CKEDITOR.config.enterMode = CKEDITOR.ENTER_DIV ;    
	
	CKEDITOR.config.width = '600px';
	CKEDITOR.config.height = '250px';
	
};
