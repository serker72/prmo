﻿/**
 * jQuery.FormNavigate.js
 * jQuery Form onChange Navigate Confirmation plugin
 * Browser Compatibility : IE 6.0, 7.0, 8.0; Firefox 2.0+;  Safari 3+; Opera 9+; Chrome 1+;
 *
 * Copyright (c) 2009 Law Ding Yong
 * 
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * See the file license.txt for copying permission.
 */
 
 /**
  * Documentation :
  * ==================
  *
  * How to Use:
  * $("YourForm").FormNavigate("YourMessage");
  *  -- "YourForm" as Your Form ID $("#form") or any method to grab your form
  *  -- "YourMessage" as Your onBeforeUnload Prompt Message Here
  *
  * This plugin handles onchange of input type of text, textarea, password, radio, checkbox and file to toggle on and off of window.onbeforeunload event.
  * Users are able to configure the custom onBeforeUnload message.
  */
var global_formNavigate = true;		// Js Global Variable for onChange Flag
(function($){
    $.fn.FormNavigate = function(message) {
        window.onbeforeunload = confirmExit;  
        function confirmExit() {  
            if (global_formNavigate == true) {  event.cancelBubble = true;  }  else  { return message;  }
        }
        $(this+ ":input[type=text], :input[type='textarea'], :input[type='password'], :input[type='radio'], :input[type='checkbox'], :input[type='file']").change(function(){
            global_formNavigate = false;
        });
        $(this+ ":submit").click(function(){
            global_formNavigate = true;
        });
    }
})(jQuery);