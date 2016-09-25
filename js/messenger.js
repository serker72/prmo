/* 
"#messenger_block" - подробное окно
"#messenger_inner_block" - внутренняя часть окна для скрытия
"#messenger_header_block" - заголовок окна

*/

//показ
var messenger_semafor=false;
var messenger_partial_load_semafor=false;
var messenger_contacts_queue=new Array();
var messenger_contacts_refresh_semafor=false;

var messenger_refresh_contacts_int; //интервал обновления контактов
var messenger_refresh_chat_int; //интервал обновления чата


/*семафоры:
состояния:
0: свободно
1: начат запрос
2: начат запрос, ожидаются данные


cr_user, cr_program - семафоры обновления контакт-листа пользователем, программой
spl_user, spl_program - семафоры обновления чата
*/
var messenger_cr_user=0;
var messenger_cr_program=0;
var messenger_spl_user=0;
var messenger_spl_program=0;


/* идентификаторы ajax запросов:*/
var messenger_xhr_user_info; //запрос данных о пол-ле
var messenger_xhr_load_chat; //запрос чата
var messenger_xhr_partial_load_chat; // частичный запрос чата
var messenger_xhr_read_messages; //семафор запроса прочтения сообщений



/*идентификаторы блока отрисовки заголовка окна*/
var messenger_refresh_title_int; //интервал мигания заголовка
var messenger_title_buffer=document.title; //заголовок окна
var messenger_title_new=''; //заголовок с новыми сообщениями
var messenger_window_state='focus'; //текущее состояние окна



function MessengerShow(state,has_new_message_clear_selected_users, do_log){
	
	/*
	has_new_message_clear_selected_users:
	2 - сбрасывать все выделение
	1 - ничего не менять
	*/
	
	var has_new_message_clear_selected_users=has_new_message_clear_selected_users||1;
	
	/*
	do_log:
	2 - вносить в журнал
	1 - ничего не менять
	*/
	
	var do_log=do_log||1;
	
	//alert($("#messenger_window"));
	//если элемент существует - показать его,
	//если нет - то создать
	
	var our_state=state;
	
	if((!messenger_semafor)&&!$("#messenger_block").length){
		//$("body").append(	
		
		//alert('one');
		messenger_semafor=true;
		$.ajax({
			  async: true,
			  url: "/js/messenger.php",
			  type: "POST",
			  data:{
				  "action":"create_messenger"
			  },
			  beforeSend: function(){
				 
			  },
			  success: function(data){
				//alert(data);
				$("body").append(data);
				if(our_state==2) MessengerMinimize();
				else  MessengerRestore();
				
				if(has_new_message_clear_selected_users==2){
					//снять выделение, внести текст
					$("input[id^=messenger_who_]:checked").each(function(index, element) {
					   $(element).prop("checked", false);
					   $("#messenger_string_"+$(element).val()).removeClass("messenger_selected");
					   $("#messenger_string_"+$(element).val()).addClass("messenger_unselected");	
					   
					});
					//скроллить наверх
					$("#messenger_who").scrollTop(0);
					
					 //внести выбор в сессию, отобразить изменения
					MessengerPutToSession(); 
					text_for_empty ='<div style="font-weight:bold; color:red;  ">Внимание!<br />У Вас есть новые сообщения!<br />Пожалуйста, выберите пользователя для того, чтобы прочесть новое сообщение.</div>';
					MessengerProcessUsersSelection(text_for_empty);
					
				  }else{
					  //проводить обработку выделенных юзеров
					  MessengerProcessUsersSelection();
				  }
				  
				  
				  //внести в журнал событий сведения об открытии программы
				  if(do_log==2) $.post("/js/messenger.php",  {
						  "action":"log",
						  "mode":1
					  }
					  );
				  
				  
				  
				  
				
			  },
			  error: function(xhr, status,error){
				
			  },
			  complete: function(){
				  messenger_semafor=false;
			  } 
		  });
		  
		  
		
		  messenger_refresh_contacts_int=window.setInterval("MessengerRefreshContacts()", 15000);
		  messenger_refresh_chat_int=window.setInterval("MessengerRefreshChat()", 15000);
			
	}else{
		
		//alert(has_new_message_clear_selected_users);
		
		if(has_new_message_clear_selected_users==2){
			//снять выделение, внести текст
			$("input[id^=messenger_who_]:checked").each(function(index, element) {
			   $(element).prop("checked", false);
			   $("#messenger_string_"+$(element).val()).removeClass("messenger_selected");
			   $("#messenger_string_"+$(element).val()).addClass("messenger_unselected");	
			   
			});
			//скроллить наверх
					$("#messenger_who").scrollTop(0);
					
			
			 //внести выбор в сессию, отобразить изменения
			MessengerPutToSession(); 
			text_for_empty ='<div style="font-weight:bold; color:red;  ">Внимание!<br />У Вас есть новые сообщения!<br />Пожалуйста, выберите пользователя для того, чтобы прочесть новое сообщение.</div>';
			MessengerProcessUsersSelection(text_for_empty);
			
			
		}else{
			//проводить обработку выделенных юзеров
			//MessengerProcessUsersSelection();
		}
		
		
		MessengerRestore();
	
	}
}


//развернуть
function MessengerRestore(){
	
	
	
	$("#messenger_maximise_block").hide();
	$("#messenger_close_block").show();
	$("#messenger_minimise_block").show();
	
	$("#messenger_inner_block").show();
	
	$("#messenger_block").removeClass("minimized");
	$("#messenger_block").addClass("restored");
	
	
	
	$.post( "/js/messenger.php", 
	{
			"action":"put_state",
			"state":1
		}	
		); 
	
	
	
	return false;
}


//минимизация
function MessengerMinimize(){
	$("#messenger_maximise_block").show();
	$("#messenger_close_block").show();
	$("#messenger_minimise_block").hide();
	
	$("#messenger_inner_block").hide();
	
	$("#messenger_block").removeClass("restored");
	$("#messenger_block").addClass("minimized");
	
	
	
	$.post( "/js/messenger.php", 
	{
			"action":"put_state",
			"state":2
		}	
		); 
	
	
	return false;
}



//свернуть-развернуть окно
function MessengerToggleRestore(){
	if($("#messenger_inner_block").css("display")=="none"){
		//развернуть	
		MessengerRestore();
	}else{
		//свернуть
		MessengerMinimize();
	}
		
}


//закрытие
function MessengerClose(){
	
	try{
		window.clearInterval(messenger_refresh_contacts_int);
		window.clearInterval(messenger_refresh_chat_int);
	//	window.clearInterval(messenger_refresh_title_int);
	}catch(e){}
	
	
	$("#messenger_block").remove();
	
	/*$(window).unbind("focus");
	$(window).unbind("blur");
	 
	*/
	
	$.post( "/js/messenger.php", 
	{
			"action":"put_state",
			"state":0
		}	
		); 
	//внести в журнал событий сведения об открытии программы
	$.post("/js/messenger.php",  {
						"action":"log",
						"mode":0
					}
					);
	return false;
}


//показ списка сотрудников (по нажатию пользователем кнопки показа)
function MessengerShowUsers(){
	messenger_cr_user=1;
	
	//console.log('MessengerShowUsers');
		
	var selected_userssu=new Array(); var first_selected=0;
	$("input[id^=messenger_who_]:checked").each(function(index, element) {
	   selected_userssu.push($(element).val()); 
	   if(first_selected==0) first_selected=$(element).val();
	   
	});
	 
	
	 $.ajax({
			  
			  async: true,
			  url: "/js/messenger.php",
			  type: "POST",
			 
			  data: {
				  action: "show_addresses",
				  sort_mode: $("input[name=messenger_sort_mode]:checked").val(),
				  "selected_users[]":selected_userssu,
				  "string_filter":$("#messenger_string_filter").val()
				},
			  beforeSend: function(){
				$("#messenger_who").html('<img src="/img/wait.gif" width="32" height="32" alt="подождите, пожалуйста" border="0" />');
				messenger_cr_user=2;					
			  },
			  success: function(data){
				 if(messenger_cr_user==2){
					 $("#messenger_who").html(data);
				 }
				 
			  },
			  error: function(xhr, status){
				  //$("#messenger_who").empty();
			  },
			  complete: function(){
				 messenger_cr_user=0;
			  }
		  });
					  
	return false;				  	
}	


//немедленное внесение в сессию выбранных сотрудников
function MessengerPutToSession(){
	//внести данные о выделенных сотрудниках в сессию...
	var selected_users=new Array(); 
	$("input[id^=messenger_who_]:checked").each(function(index, element) {
	   selected_users.push($(element).val()); 
	   
	});
	//alert('selecting: '+selected_users);
	$.post("/js/messenger.php",
		{
			"action":"put_selected_users",
			"selected_users[]":selected_users
		}, function(){ }
		);
	
//	console.log(selected_users);
}


//обработка выбора сотрудника
function MessengertoggleSelect(user_id,e){
	
	messenger_cr_user=1;
	messenger_cr_program=1;
	
	if(user_id>0){
		//выбрали одного сотрудника ... показать его инфо, а также пометить выбранным/невыбранным
		
		
		//alert(e.ctrlKey);
		if(e.ctrlKey){
			//если с контрол - то в переписку внести предупреждение!
			//заблокировать отправку сообщений
		}else{
			//если без контрол - то снять прочие выделения
			$("input[id^=messenger_who_]").each(function(index, element) {
				current_id=$(element).attr("id").replace(/^messenger_who_/,'');
				if((current_id!=user_id)&&(current_id>0)&&$(element).prop("checked")){
					//снять выделение сотрудника
					$("#messenger_who_"+current_id).prop("checked", false);
					$("#messenger_string_"+current_id).removeClass("messenger_selected");
					$("#messenger_string_"+current_id).addClass("messenger_unselected");	
				}
			});
			
		}
		
		
		
		
		if($("#messenger_who_"+user_id).prop("checked")){
			//снять выделение сотрудника
			$("#messenger_who_"+user_id).prop("checked", false);
			$("#messenger_string_"+user_id).removeClass("messenger_selected");
			$("#messenger_string_"+user_id).addClass("messenger_unselected");
		}else{
			
			
			
			
			//установить выделение сотрудника
			$("#messenger_who_"+user_id).prop("checked", true);
			$("#messenger_string_"+user_id).removeClass("messenger_unselected");
			
			$("#messenger_string_"+user_id).addClass("messenger_selected");
			
		}
		
		 //обработаем выбор адресатов
		MessengerPutToSession(); 
		MessengerProcessUsersSelection();
		
	
	}//конец условия выбора сотрудника
	else if(user_id==0){
		//если выбираем 0 = снять все выделения,
		
		//сбросим фильтр по адресатам
		$("#messenger_string_filter").val('');
		
		
		
		//подгрузим адресатов
		$.ajax({
						  
			async: true,
			url: "/js/messenger.php",
			type: "POST",
		   
			data: {
				action: "show_addresses",
				sort_mode: $("input[name=messenger_sort_mode]:checked").val(),
				"string_filter":$("#messenger_string_filter").val()
			  },
			beforeSend: function(){
			  $("#messenger_who").html('<img src="/img/wait.gif" width="32" height="32" alt="подождите, пожалуйста" border="0" />');	
			},
			success: function(data){
			   $("#messenger_who").html(data);
			   
			  //снять выделение всех 
			  $("input[id^=messenger_who_]").each(function(index, element) {
				  current_id=$(element).attr("id").replace(/^messenger_who_/,'');
				  if((current_id>0)&&$(element).prop("checked")){
					  //снять выделение сотрудника
					  $("#messenger_who_"+current_id).prop("checked", false);
					  $("#messenger_string_"+current_id).removeClass("messenger_selected");
					  $("#messenger_string_"+current_id).addClass("messenger_unselected");	
				  }
			  });
			  $("#messenger_user_info").empty();
			  
			  //обработаем выбор адресатов
			  MessengerPutToSession();
			  MessengerProcessUsersSelection();
			   
			   
			},
			error: function(xhr, status){
				$("#messenger_who").empty();
			}	 
		});
		
		
		
		
		
		
		
			   
		
		
	}//конец условия нажатия -выберите- - сброса всего выделения
	else if(user_id==-2){
		//если выбираем -2 - то выбираем всех сотрудников
		
		//сбросим фильтр по адресатам
		$("#messenger_string_filter").val('');
	
		
		
		//подгрузим адресатов
		$.ajax({
						  
			async: true,
			url: "/js/messenger.php",
			type: "POST",
		   
			data: {
				action: "show_addresses",
				sort_mode: $("input[name=messenger_sort_mode]:checked").val(),
				"string_filter":$("#messenger_string_filter").val()
			  },
			beforeSend: function(){
			  $("#messenger_who").html('<img src="/img/wait.gif" width="32" height="32" alt="подождите, пожалуйста" border="0" />');	
			},
			success: function(data){
			   $("#messenger_who").html(data);
			   
			   //выделим всех
			   $("input[id^=messenger_who_]").each(function(index, element) {
				  current_id=$(element).attr("id").replace(/^messenger_who_/,'');
				  if(current_id>0){
					 // alert(current_id);
					  
					  //установить выделение сотрудника
					  $("#messenger_who_"+current_id).prop("checked", true);
					  $("#messenger_string_"+current_id).removeClass("messenger_unselected");
					  
					  $("#messenger_string_"+current_id).addClass("messenger_selected");
						  
				  }
			  });
			  $("#messenger_user_info").empty();
			  
			  //обработаем выбор адресатов
			  MessengerPutToSession();
			  MessengerProcessUsersSelection();
			   
			   
			},
			error: function(xhr, status){
				$("#messenger_who").empty();
			}	 
		});
		
		
		
		
		
	}
	
	
	
	
	return false;
}

//обработка выделения пользователей
function MessengerProcessUsersSelection(text_for_empty){
	var text_for_empty=text_for_empty||"";
	
	
	messenger_cr_user=1;
	messenger_cr_program=1;
	
    //console.log('MessengerProcessUsersSelection');
	
	var selected_users=new Array(); 
	$("input[id^=messenger_who_]:checked").each(function(index, element) {
	   selected_users.push($(element).val()); 
	   
	});
		 
	
	//alert('chz'+selected_users);
	
	/* обработка выбора адресата:
	-ни одного: пустой ввод, недоступна кнопка отпр сообщение, редактор
	-один: подгрузить чат за сегодня, доступен редактор, сообщение
	-несколько: "Внимание! Вы выбрали нескольких адресатов: ... ! Сообщение будет разослано всем выбранным адресатам" в окне чата, доступен редактор, сообщение
	*/
	
	//попытаемся остановить предыдущий запрос
	try{
		messenger_xhr_load_chat.abort();
	}catch(e){}
	try{
		messenger_xhr_user_info.abort();
	}catch(e){}
	
	switch(selected_users.length){
		case 0:
			//-ни одного: пустой ввод, недоступна кнопка отпр сообщение, редактор - доступен
			$("#messenger_chat").empty();
			
			//$("#messenger_chat").html('<span style="color:red;">Пожалуйста, выберите пользователей для отправки сообщения!</span>');
			
			
			if(text_for_empty.length>0) $("#messenger_chat").html(text_for_empty);
			$("#messenger_send_button").prop("disabled", true);
			$("#messenger_send_text").prop("disabled", true);
			
			$("#messenger_user_info").html('');
			
			/*try{
				CKEDITOR.instances.messenger_send_text.setReadOnly(true);
			}catch(e){}
			*/
			messenger_cr_user=0;
			messenger_cr_program=0;
			
			
		break;
		case 1:
			//-один: подгрузить чат за сегодня, доступен редактор, сообщение
			//данные о сотруднике
			
			
				
			messenger_xhr_user_info=$.ajax({
				  
				  async: true,
				  url: "/js/messages.php",
				  type: "POST",
				 
				  data: {
					  action: "show_user_data",
					  id: selected_users[0]
					
					},
				  beforeSend: function(){
					$("#messenger_user_info").html('<img src="/img/wait.gif" width="32" height="32" alt="подождите, пожалуйста" border="0" />');
					messenger_cr_user=2;	
				  },
				  success: function(data){
					// if(messenger_cr_user==2){ 
					 	$("#messenger_user_info").html(data);	 
					// }
				  },
				  error: function(xhr, status){
					  $("#messenger_user_info").empty();	
				  },
				  complete: function(){
						messenger_cr_user=0; 
						messenger_cr_program=0; 
				  }
			  });
			
			MessengerLoadChat($("#messenger_selected_days").val());
			
			$("#messenger_send_button").prop("disabled", false);
			$("#messenger_send_text").prop("disabled", false);
			try{
				CKEDITOR.instances.messenger_send_text.setReadOnly(false);
			}catch(e){}
			
			//messenger_cr_user=0;
			
		break;
		
		default:
			//alert('def');
			
			//-несколько: "Внимание! Вы выбрали нескольких адресатов: ... ! Сообщение будет разослано всем выбранным адресатам" в окне чата, доступен редактор, сообщение
			var descr=new Array();
			$("input[id^=messenger_who_]:checked").each(function(index, element) {
			   //descr.push($(element).val()); 
			   descr.push($("#messenger_select_descr_"+$(element).val()).text());
			});
			
			
			/*$("#messenger_chat").html('<div style="font-weight:bold; color:red; font-size:12px;">Внимание!<br />Вы выбрали нескольких адресатов: '+descr.join(', ')+'!<br />Сообщение будет разослано всем выбранным адресатам.<br />История переписки будет доступна при выборе одного адресата.</div>');*/
			MessengerLoadChat(2);
			
			$("#messenger_send_button").prop("disabled", false);
			$("#messenger_send_text").prop("disabled", false);
			try{
				CKEDITOR.instances.messenger_send_text.setReadOnly(false);
			}catch(e){}
			
			$("#messenger_user_info").html('<span style="color:red;">Внимание!<br />Вы выбрали нескольких адресатов.</span>');
			
			messenger_cr_user=0;
			messenger_cr_program=0;
			
		break;	
		
	}
	
	 
	
}


//подгрузка чата
function MessengerLoadChat(days){
	//alert(days);
	
	var selected_userslc=new Array(); 
	$("input[id^=messenger_who_]:checked").each(function(index, element) {
	   selected_userslc.push($(element).val()); 
	   
	});
	
	$("#messenger_selected_days").val(days);
	$("a[id^=messenger_load_chat_]").removeClass("messenger_selected_days");
	$("#messenger_load_chat_"+days).addClass("messenger_selected_days");
	
	if((selected_userslc.length==1)&&(messenger_spl_user==0)){
		messenger_spl_user=1;
		
		//попытаемся остановить предыдущий запрос:
		try{
			messenger_xhr_load_chat.abort();
		}catch(e){
			//alert('Ошибка при остановке загрузки чата');
		}
		
		messenger_xhr_load_chat=$.ajax({
			  
			  async: true,
			  url: "/js/messenger.php",
			  type: "POST",
			 
			  data: {
				  action: "load_chat",
				  "selected_users[]": selected_userslc,
				  "days": days
				
				},
			  beforeSend: function(){
				$("#messenger_chat").html('<img src="/img/wait.gif" width="32" height="32" alt="подождите, пожалуйста" border="0" />');
				messenger_spl_user=2;
				
			  },
			  success: function(data){
				if(messenger_spl_user==2){
				  $("#messenger_chat").html(data);
				  //alert($("#messenger_chat").innerHeight());
				  
				  //$("#messenger_outer_chat").scrollTop($("#messenger_chat").innerHeight());
				   MessengerToggleReadMessages();
				}
			  },
			  error: function(xhr, status){
				$("#messenger_chat").html('');  
			  },
			  complete: function(){
				messenger_spl_user=0;
			  }
		  });
		
	}else if((selected_userslc.length>1)&&(messenger_spl_user==0)){
		
		//несколько пользователей: показывать, что отправили им всем
		
		
		var descr=new Array();
			$("input[id^=messenger_who_]:checked").each(function(index, element) {
			   //descr.push($(element).val()); 
			   descr.push($("#messenger_select_descr_"+$(element).val()).text());
			});
		
		
		$("#messenger_chat").html('<div style="font-weight:bold; color:red;  ">Внимание!<br />Вы выбрали нескольких адресатов: <span style="color:black;">'+descr.join(', ')+'</span>!<br />Сообщение будет разослано всем выбранным адресатам.<br />История переписки будет доступна при выборе одного адресата.</div>');
		
		
		
	}else if(selected_userslc.length==0){
		$("#messenger_chat").html('');
	}
	
	return false;
}


//частичная загрузка чата 
function MessengerPartialLoadChat(){
	//alert(days);
	
	var selected_userspc=new Array(); 
	$("input[id^=messenger_who_]:checked").each(function(index, element) {
	   selected_userspc.push($(element).val()); 
	   
	});
	
	
	
	if((selected_userspc.length==1)&&(messenger_spl_user==0)){
		
		messenger_spl_user=1;
		
		//попытаемся остановить предыдущий запрос:
		try{
			messenger_xhr_partial_load_chat.abort();
		}catch(e){
			//alert('Ошибка при остановке загрузки чата');
		}
		
		messenger_xhr_partial_load_chat=$.ajax({
			  
			  async: true,
			  url: "/js/messenger.php",
			  type: "POST",
			 
			  data: {
				  action: "partial_load_chat",
				  "selected_users[]": selected_userspc,
				  "days": 0,
				  "last_message_id":$("#messenger_chat input[id^=messenger_message_id_]:first").val()
				
				},
			  beforeSend: function(){
				messenger_spl_user=2;
			  },
			  success: function(data){
				if(messenger_spl_user==2){
				  $("#messenger_chat").prepend(data);
				  
				  //$("#messenger_outer_chat").scrollTop($("#messenger_chat").innerHeight());
				   MessengerToggleReadMessages();
				}
			  },
			  error: function(xhr, status){
				  
			  },
			  complete: function(){
				messenger_spl_user=0;
			  }
		  });
		
	}else if((selected_userspc.length>1)&&(messenger_spl_user==0)){
		
		//несколько пользователей: показывать, что отправили им всем
		
		var descr=new Array();
			$("input[id^=messenger_who_]:checked").each(function(index, element) {
			   //descr.push($(element).val()); 
			   descr.push($("#messenger_select_descr_"+$(element).val()).text());
			});
		
		$("#messenger_chat").html('<div style="font-weight:bold; color:red;  ">Внимание!<br />Вы выбрали нескольких адресатов: <span style="color:black;">'+descr.join(', ')+'</span>!<br />Сообщение будет разослано всем выбранным адресатам.<br />История переписки будет доступна при выборе одного адресата.</div>');
		
		
		
	}else if(selected_userspc.length==0){
		
		$("#messenger_chat").html('');
	}
	
	return false;
}




//отправка сообщения
function MessengerSendMessage(){
	message='';
		try{
			message=CKEDITOR.instances.messenger_send_text.getData();
		}catch(e){
			message=$("#messenger_send_text").val();
		}
		
	if(message.length==0) return false;
	
	
	var selected_userssm=new Array(); 
	$("input[id^=messenger_who_]:checked").each(function(index, element) {
	   selected_userssm.push($(element).val()); 
	   
	});
	
	if(selected_userssm.length>1){
		if(!window.confirm("Внимание!\nВы выбрали нескольких адресатов сообщения.\nВы действительно хотите отправить сообщение нескольким адресатам?")) return false;
		
	}
	
	
	if(selected_userssm.length>0){
		
		
		
		$.ajax({
			  
			  async: true,
			  url: "/js/messenger.php",
			  type: "POST",
			 
			  data: {
				  action: "send_message",
				  "selected_users[]": selected_userssm,
				  "message": message
				
				},
			  beforeSend: function(){
				$("#messenger_send_button").prop("disabled", true);
				
				$("#messenger_send_text").prop("disabled", true);
				try{
					CKEDITOR.instances.messenger_send_text.setReadOnly(true);
				}catch(e){}
			  },
			  success: function(data){
				// $("#messenger_user_info").html(data);	 
				try{
					CKEDITOR.instances.messenger_send_text.setData('', function() {
    CKEDITOR.instances.messenger_send_text.focus();
					});
				}catch(e){
					$("#messenger_send_text").val('');
				}
				if(selected_userssm.length==1){
					MessengerPartialLoadChat();	
				}
				
			  },
			  error: function(xhr, status){
				  
			  },
			  complete: function(xhr, status){
				  $("#messenger_send_button").prop("disabled", false);
				
				  $("#messenger_send_text").prop("disabled", false);
				  try{
					  CKEDITOR.instances.messenger_send_text.setReadOnly(false);
				  }catch(e){}
			  }
		  });
		  
		  
		  
		  
	}else{
		alert("Выберите адресата для отправки сообщения!");	
	}
}



//обновление контакт-листа - проверка новых сообщений и онлайн
function MessengerRefreshContacts(){
	
	//построить очередь
	//обрабатывать очередь
	//var messenger_contacts_queue=new Array();
	//var messenger_contacts_refresh_semafor=false;

	if((messenger_cr_program==0)&&(messenger_cr_user==0)) {
	  messenger_cr_program=1;
	
	  // alert('обновляю контакт-лист');
	   
	    var selected_userscl=new Array();  
		$("input[id^=messenger_who_]:checked").each(function(index, element) {
		   selected_userscl.push($(element).val()); 
		   
		});
	   
	  // alert('refresh: '+selected_userscl);
	   
	   $.ajax({
						  
		  async: true,
		  url: "/js/messenger.php",
		  type: "POST",
		 
		  data: {
			  action: "refresh_addresses",
			  sort_mode: $("input[name=messenger_sort_mode]:checked").val(),
			  "selected_users[]":selected_userscl,
			  "string_filter":$("#messenger_string_filter").val()
			
			},
		  beforeSend: function(){
			
			messenger_cr_program=2;	
			//$("#messenger_header_block").html('<h1 style="color:green;">gogogogogog</h1>');
			
		  },
		  success: function(data){
			 if((messenger_cr_program==2)&&(messenger_cr_user==0)){
				 $("#messenger_who").html(data);			 
				//alert(data);
				//$("#messenger_header_block").html('');
			 }
		  },
		  error: function(xhr, status){
			  //$("#messenger_who").empty();
		  },
		  complete: function(data){
			  messenger_cr_program=0;
		  }
	  });
	  
	  // messenger_cr_program=0;
	}
	
}




//обновление активного чата
function MessengerRefreshChat(){
	var selected_usersrm=new Array(); 
	$("input[id^=messenger_who_]:checked").each(function(index, element) {
	   selected_usersrm.push($(element).val()); 
	   
	});
	
	
	
	if((selected_usersrm.length==1)&&(messenger_spl_program==0)&&(messenger_spl_user==0)){
		//MessengerPartialLoadChat();
		messenger_spl_program=1;
		
		$.ajax({
			  
		  async: true,
		  url: "/js/messenger.php",
		  type: "POST",
		 
		  data: {
			  action: "partial_load_chat",
			  "selected_users[]": selected_usersrm,
			  "days": 0,
			  "last_message_id":$("#messenger_chat input[id^=messenger_message_id_]:first").val()
			
			},
		  beforeSend: function(){
			 messenger_spl_program=2;
		  },
		  success: function(data){
			if((messenger_spl_program==2)&&(messenger_spl_user==0)){
			  $("#messenger_chat").prepend(data); //.append(data);
			  
			  //$("#messenger_outer_chat").scrollTop($("#messenger_chat").innerHeight());
			  MessengerToggleReadMessages();
			}
		  },
		  error: function(xhr, status){
			  
		  },
		  complete: function(){
			messenger_spl_program=0;
		  }
	  });
	
	}
	
	return false;
}


/*пометка НОВЫХ сообщений как прочитанных при условии, что окно в фокусе*/
function MessengerToggleReadMessages(){
	if(messenger_window_state=='focus'){	
		var messenger_unread_messages=new Array();
		$("img[id^=messenger_message_newflag_]").each(function(index, element) {
            id=$(element).attr("id").replace(/^messenger_message_newflag_/,"");
			
			messenger_unread_messages.push(id);
			
        });
		
		
		if(messenger_unread_messages.length>0) {
		
			
			//попытаемся остановить предыдущий запрос:
			try{
				messenger_xhr_read_messages.abort();
			}catch(e){
				//alert('Ошибка при остановке загрузки чата');
			}
			
			messenger_xhr_read_messages=$.ajax({
				  
			  async: true,
			  url: "/js/messenger.php",
			  type: "POST",
			 
			  data: {
				   "action":"toggle_read",
							  "messages[]":messenger_unread_messages
			  },
			  success: function(){
				   $.each(messenger_unread_messages, function(k, id){ $("#messenger_message_newflag_"+id).remove(); });
			  }
			});
			  
		
		}
	
	}
}



/*var messenger_refresh_title_int; //интервал мигания заголовка
var messenger_title_buffer=document.title; //заголовок окна
var messenger_title_new=''; //заголовок с новыми сообщениями
*/

//функция мигания заголовка
function MessengerBlinkTitle(){
	if(document.title==messenger_title_buffer){
		document.title=messenger_title_new;	
	}else{
		document.title=messenger_title_buffer;
	}
}

//функция отключения мигания заголовка
function MessengerBlinkStop(){
	document.title=messenger_title_buffer;
	try{
		window.clearInterval(messenger_refresh_title_int);
	}catch(ex){} 
	
	
	
	
}

