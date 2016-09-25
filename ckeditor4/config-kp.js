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
		['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		['Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
		 
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink','Anchor'],
		 
		['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
		 
		['Styles','Format','Font','FontSize'],
		['TextColor','BGColor'],
		['Maximize', 'ShowBlocks'],
		['Source','-']
	];
	
	CKEDITOR.config.toolbar = 'My';
	
	
	CKEDITOR.config.enterMode = CKEDITOR.ENTER_DIV ;    
	
	CKEDITOR.config.width = '800px';
	CKEDITOR.config.height = '250px';
	CKEDITOR.config.toolbarCanCollapse = true;
	CKEDITOR.config.toolbarStartupExpanded = false;
	
};
