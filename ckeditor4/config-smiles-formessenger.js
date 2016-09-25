/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	
	
	CKEDITOR.config.toolbar_My =
	[
		['Cut','Copy','Paste','PasteText','PasteFromWord'],
		['Undo','Redo'],
		['Bold','Italic','Underline','Strike','-', 'TextColor','BGColor', 'Smiley']
	];
	
	
		
	CKEDITOR.config.toolbar = 'My';
	config.removePlugins = 'elementspath'; 
	config.resize_enabled = false;
	
	CKEDITOR.config.enterMode = CKEDITOR.ENTER_DIV ;    
	
	CKEDITOR.config.width = '480px';
	CKEDITOR.config.height = '80px';
	
	
	

	
};
