/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */
function fileQueued(file) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("Ожидание...");
		progress.toggleCancel(true, this);

	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("Вы пытаетесь загрузить слишком много файлов.\n" + (message === 0 ? "Достигнуто ограничение загрузки." : "Вы можете выбрать " + (message > 1 ? "до " + message + " файлов." : "один файл.")));
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus("Слишком большой размер файла.");
			this.debug("Код ошибки: Слишком большой размер файла, Имя файла: " + file.name + ", Размер файла: " + file.size + ", Сообщение: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus("Невозможно загрузить файл нулевой длины.");
			this.debug("Код ошибки: Файл нулевой длины, Имя файла: " + file.name + ", Размер файла: " + file.size + ", Сообщение: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus("Неверный тип файла.");
			this.debug("Код ошибки: Неверный тип файла, Имя файла: " + file.name + ", Размер файла: " + file.size + ", Сообщение: " + message);
			break;
		default:
			if (file !== null) {
				progress.setStatus("Неизвестная ошибка");
			}
			this.debug("Код ошибки: " + errorCode + ", Имя файла: " + file.name + ", Размер файла: " + file.size + ", Сообщение: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}


function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
			document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		}
		
		
		/* I want auto start the upload and I can do that here */
		this.startUpload();
	} catch (ex)  {
        this.debug(ex);
	}
}

function fileDialogCompleteCheck(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
			document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		}
		
		/*if($("#txt").val().length==0){
		 	alert("Обязательно заполните Описание!");
		 	$("#txt").focus();	
		 	
			return false;
		}*/
		
		/* I want auto start the upload and I can do that here */
		this.startUpload();
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	
	
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("Идет загрузка...");
		progress.toggleCancel(true, this);
	}
	catch (ex) {}
	
	return true;
}

function uploadStartCheck(file) {
	
	
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		if($("#txt").val().length==0){
		 	alert("Обязательно заполните Описание!");
		 	$("#txt").focus();	
		 	progress.setStatus("Ошибка отправки файла...");
			progress.setCancelled();
			//SWFUpload.cancelQueue();
			return false;
		}else{
		
		  progress.setStatus("Идет загрузка...");
		  progress.toggleCancel(true, this);
		}
	}
	catch (ex) {}
	
	return true;
}



function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setProgress(percent);
		progress.setStatus("Идет загрузка...");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setComplete();
		progress.setStatus("Завершено.");
		progress.toggleCancel(false);
				
		//alert(serverData);
		eval(serverData);
		
		/*выводим, что вывел скрипт upload.php с помощью банального echo...
		или не выводим, а выполняем;)
		*/

	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			progress.setStatus("Ошибка загрузки: " + message);
			this.debug("Код ошибки: HTTP Error, Имя файла: " + file.name + ", Сообщение: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			progress.setStatus("Ошибка загрузки.");
			this.debug("Код ошибки: Upload Failed, Имя файла: " + file.name + ", Размер файла: " + file.size + ", Сообщение: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			progress.setStatus("Ошибка ввода-вывода");
			this.debug("Код ошибки: Ошибка ввода-вывода, Имя файла: " + file.name + ", Сообщение: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			progress.setStatus("Ошибка безопасности");
			this.debug("Код ошибки: Ошибка безопасности, Имя файла: " + file.name + ", Сообщение: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			progress.setStatus("Превышены ограничения загрузки.");
			this.debug("Код ошибки: Превышены ограничения загрузки, Имя файла: " + file.name + ", Размер файла: " + file.size + ", Сообщение: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			progress.setStatus("Проверка файла не прошла, загрузка пропущена.");
			this.debug("Код ошибки: Проверка файла не прошла, Имя файла: " + file.name + ", Размер файла: " + file.size + ", Сообщение: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
				document.getElementById(this.customSettings.cancelButtonId).disabled = true;
			}
			progress.setStatus("Отменено");
			progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus("Остановлено");
			break;
		default:
			progress.setStatus("Неизвестная ошибка: " + errorCode);
			this.debug("Код ошибки: " + errorCode + ", Имя файла: " + file.name + ", Размер файла: " + file.size + ", Сообщение: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
		document.getElementById(this.customSettings.cancelButtonId).disabled = true;
		
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
	var status = document.getElementById("divStatus");
	status.innerHTML = numFilesUploaded + " файл" + (numFilesUploaded === 1 ? "" : "s") + " загружен.";
}

function RedrawIt(numFilesUploaded){
	queueComplete(numFilesUploaded);
	location.reload();	

}

/*
function startDialog(){
	alert("ff");
	if($("#txt").val().length==0) return false;
	else this.selectFiles();
}*/