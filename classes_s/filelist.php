<?
require_once('PageNavigatorKri_.php');
class FileList{
	protected $path; //текущий путь, где смотрим файлы
	protected $base_path; //основной путь (для вычитания при генерации ссылок)
	protected $root_path; //путь от корня сервера (base_path без начала, нужен для генерации гиперссылок с превьюшек)
	
	protected $files=Array();
	
	//сколько файлов в один ряд
	protected $files_per_row=5;
	
	//имя файла-сценария
	protected $prog_name='index.php';
	
	//название сессии для хранения выделенных файлов
	protected $sessionname="photofolder";
	
	//размеры превью при уменьшении
	protected $preview_size=Array();

	//размеры оптимизируемого файла
	protected $optimize_size=Array();	
	
	//размеры процента для уменьшения
	protected $percentage=15;
	
	//иконка документа
	protected $doc_icon="/img/doc.gif";
	//иконка вставки картинки
	protected $image_icon="/img/image_16.gif";
	
	//параметры кнопок для подстановки фото в статьи
	protected $manmode=Array();
	
	//параметры открытия форм ресайза
	protected $openmode=Array(
		'0'=>true,
		'1'=>true,
		'2'=>true,
		'3'=>true,
				'20'=>true,
				'30'=>true
	);
	
	//режим работы (для передачи к функции рисования кнопок для подстановки фото в статьи)
	protected $mode=32; 
	
	
	public function __construct($path,$basepath,$rootpath){
		$this->init($path,$basepath,$rootpath);
	}
	
	protected function init($path,$basepath,$rootpath){
		$this->path=$path;
		$this->base_path=$basepath;
		$this->root_path=$rootpath;		
		$this->preview_size[0]=90;
		$this->preview_size[1]=120;
		$this->optimize_size[0]=575;
		$this->optimize_size[1]=765;		
		
		$this->optimize_size[2]=355;
		$this->optimize_size[3]=485;		
		$this->optimize_size[4]=125;
		$this->optimize_size[5]=170;	
		
	}
	
	public function __destruct(){
		unset($this->files);
	}
	
	
	//назначим имя сценария
	public function SetProgName($name){
		$this->prog_name=$name;
		return true;
	}
	
	//задание размеров превьюшки
	public function SetPreviewSize($preview_size){
		$this->preview_size=$preview_size;
		return true;
	}
	
	//задание размеров оптимизированного файла
	public function SetOptimizeSize($optimize_size){
		$this->optimize_size=$optimize_size;
		return true;
	}
	
	//задание размеров в процентах
	public function SetPercentage($percentage){
		$this->percentage=$percentage;
		return true;
	}
	
	//задание массива имен форм
	public function SetManmode($manmode){
		$this->manmode=$manmode;
		return true;
	}
	
	//задание массива признаков открытия ресайзов
	public function SetOpenmode($openmode){
		$this->openmode=$openmode;
		return true;
	}
	
	
	public function GetFileList($from=0,$per_page=PHOTOS_PER_PAGE,$allowable='jpg|jpe|jpeg|gif|png|wbm',$params=Array()){
		$txt='';
		
		$this->mode=$params['mode'];
		
		$txt.=$this->DrawFormControlScript();
		
		$this->CreateFileList($allowable);
		
		$totalcount=count($this->files);
		$str='';
		foreach($params as $k=>$v){
			$str.="&$k=$v";
		}
		
		$navig = new PageNavigatorKri_($this->prog_name,$totalcount,$per_page,$from,10,$str);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		
		$txt.= $navig->GetNavigator();
		
		$cter=0; $photo_row_cter=0; $photo_cter=0;
		
		//массив для хранения имен файлов
		$filearr=Array();
		
		if($totalcount>0) $txt.='<table width="*" border="0" cellpadding="2" cellspacing="1" class="maintable">'."\n";
		
		foreach($this->files as $k=>$v){
			if(($cter>=$from)&&($cter<$totalcount)) {
				if($photo_row_cter==0) $txt.='<tr align="center" valign="top">'."\n";
				
				$txt.='<td width="'.ceil(100/PHOTOS_PER_ROW).'%">'."\n";
				
				$txt.=$this->DrawFile($v);
				
				$filearr[]=$v['rel_path'];
				
				if($photo_row_cter<PHOTOS_PER_ROW-1) $photo_row_cter++;
				else $photo_row_cter=0;
    			$photo_cter++;
				$txt.='</td>'."\n";
			}
			
			$cter++;
			if($photo_cter>=PHOTOS_PER_PAGE) break;
		}
		
		if($totalcount>0) { 
			$txt.='</table>'."\n";
			
			$txt.=$this->DrawCommonElems($filearr);
		}
		
		$txt.= $navig->GetNavigator();
		
		$p=Array(); $p=$params;
		
		$p['from']=$from;
		//$p['rel_path']=
		$txt.=$this->DrawUploads($p);		
		
		unset($navig);
		return $txt;
	}
	
	
	//создаем массив из файлов
	protected function CreateFileList($allowable='jpg|jpe|jpeg|gif|png|wbm'){
		$hand = opendir($this->path);
		$this->files=array();
		
		while(($name=readdir($hand))!==false){
			if(($name==".")||($name==".."))  continue;
			if(is_file($this->path."/".$name)){
			//выводим файлы...
				if(($allowable!=NULL)&&(eregi("^(.*)\\.(".$allowable.")$",$name,$P))) {
					$this->files[eregi_replace("//","/", $name)]=Array(
						'name' => eregi_replace("//","/",$name),
						'path' => eregi_replace("//","/",$this->path),
						'url' => eregi_replace("//","/",$this->path."/".$name),
						'rel_path'=>eregi_replace("//","/",substr($this->path.'/'.$name, strlen($this->base_path)))
					);
/*						'durl' => 'fotodrawer.php?w1='.$w1.'&h1='.$h1.'&picname='.$fullpath."/".$name*/
				}else if($allowable==NULL){
					$this->files[eregi_replace("//","/",$name)]=Array(
						'name' => eregi_replace("//","/",$name),
						'path' => eregi_replace("//","/",$this->path),
						'url' => eregi_replace("//","/",$this->path."/".$name),
						'rel_path'=>eregi_replace("//","/",substr($this->path.'/'.$name, strlen($this->base_path)))
					);
				}
			}
		}
		
		closedir($hand);	
		ksort($this->files);
	
	}
	
	
	//отрисовка фото
	protected function DrawFile($params){
		$txt='';
		
		if(isset($_SESSION[$this->sessionname][$params['rel_path']])) {
			$classname='fileitem_marked';
			$ch='checked';
		}
		else{
			$classname='fileitem';
			$ch='';
		}
		
		$txt.='<div id="'.$params['rel_path'].'" class="'.$classname.'">';
		$txt.="<a name=\"$params[rel_path]\"></a>";
		
		//$txt.=$params['name'];
		$txt.=$this->DrawOne($params);
		
		
		$txt.='<br><span class="inphoto">&nbsp;&nbsp;';
		
		$txt.="<input type=\"checkbox\" name=\"ch_$params[rel_path]\" id=\"ch_$params[rel_path]\" value=\"\" $ch onmousedown=\"".COORDFUNC." SetCoords('filerer');\" onchange=\"ct=document.getElementById('$params[rel_path]'); if(this.checked){MarkFile('$params[rel_path]');}else{UnmarkFile('$params[rel_path]');}; \">выделить <br>";
		
		$txt.=$this->DrawFormControls($params);
		
		
		$txt.="<a class=\"photolink\" href=\"#$params[rel_path]\" onmousedown=\"".COORDFUNC." SetCoords('filerer');\" onClick=\"pt=document.getElementById('filepath'); pt.value='".SecStr($params['rel_path'])."'; pt=document.getElementById('oldfilename'); pt.value='".SecStr($params['name'])."'; pt=document.getElementById('filename'); pt.value='".SecStr($params['name'])."'; pt=document.getElementById('fileaction'); pt.value='1'; r=document.getElementById('filerer'); /*coord=GetCoords(); r.style.left=coord[0]+'px'; r.style.top=coord[1]+'px';*/ r.className='renvis'; \">переименовать</a>";
		$txt.="&nbsp;&nbsp; <a class=\"photolink\" href=\"#$params[rel_path]\" onClick=\" ".COORDFUNC." ct=window.confirm('ВНИМАНИЕ!!! Вы действительно хотите удалить данный файл?'); if(ct) {RemoveFileFunc('".SecStr($params['rel_path'])."');}\">удалить</a>";	
		$txt.='</span>';
			
		$txt.='</div>';
		
		return $txt;
	}
	
	
	//служебная функция отрисовки вспомогат. элементов управления (выделение, операции над ним)
	protected function DrawCommonElems($filearr){
		$txt='';
		
		$txt.='<p><form action="">';
		
		//код со всем массивом
		$arr='arr=new Array(); ';
		for($i=0; $i<count($filearr); $i++) $arr.=" arr[$i]='$filearr[$i]';\n";
		
		$txt.="<input type=\"button\" name=\"selAll\" id=\"selAll\" value=\"Выделить все\" onclick=\"$arr SelectArr(arr);\" onmousedown=\"".COORDFUNC." SetCoords('filerer');\"> ";
		$txt.="<input type=\"button\" name=\"unSelAll\" id=\"unSelAll\" onclick=\"UnSelectArr();\" value=\"Отменить выделение\" onmousedown=\"".COORDFUNC." SetCoords('filerer');\"> ";
		
		$txt.='&nbsp;&nbsp;&nbsp;&nbsp;<strong>Выделенные файлы:</strong>';
		
		$txt.='<select name="action" id="action">
			<option value="7" SELECTED>удалить</option>
		</select>';
		
		
		$txt.="<input type=\"button\" name=\"delSel\" id=\"delSel\" onclick=\"delSelected();\" value=\"ОК\" onmousedown=\"".COORDFUNC." SetCoords('filerer');\"> ";
		
		$txt.='</form>';
		return $txt;
	}
	
	
	//отрисовка элементов загрузки файла
	protected function DrawUploads($params){
		$txt='';
		
		$txt.='<div class="dirtree" align="left" style="">';
		
		//$txt.=substr($this->path, strlen($this->base_path)); s
		
		$txt.='<form enctype="multipart/form-data" action="'.$this->prog_name.'" method="post" name="uploadfiles" id="uploadfiles">';
		$txt.='Закачать фото (в формате *.jpg, *.jpeg, *.jpe, *.gif, *.png) (максимальный суммарный размер='.ini_get('upload_max_filesize').'):<br>';
		
		//джаваскрипт для проверки корректности всего
		$txt.="
		<script type=\"text/javascript\">
		//получим значение выдел кнопки
		function GetRadioVal(radio){
			chch=-1;
			for(i=0;i<=radio.length;i++){
				if(radio[i].checked) {
					chch=radio[i].value;
					break;
				}
			}
			
			return chch;
		}
		
		
		//проверка изменений размера
		function CheckUploads(){
			isc=document.getElementById('changeSize');
			if(isc.checked){
				//выбраны пикселя
				if(GetRadioVal(document.forms['uploadfiles'].resize_kind)==0) {
					//alert('px');
					//alert('rr');
					ww=document.getElementById('new_w');
					if((isNaN(ww.value))||(ww.value<=0)){
						alert('Некорректная ширина в пикселях!');
						ww.focus();
						return false;
					}
					
					hh=document.getElementById('new_h');
					if((isNaN(hh.value))||(hh.value<=0)){
						alert('Некорректная высота в пикселях!');
						hh.focus();
						return false;
					}
				}
				
				//выбраны %
				if(GetRadioVal(document.forms['uploadfiles'].resize_kind)==1) {
					//alert('%');
					
					hh=document.getElementById('new_percent');
					if((isNaN(hh.value))||(hh.value<=0)||(hh.value>100)){
						alert('Указаны некорректные проценты!');
						hh.focus();
						return false;
					}
				}
				
			}
			
			isc=document.getElementById('makeAddFile');
			//работа с доп. файлом
			if(isc.checked){
				hh=document.getElementById('add_file_pre');
				//приставка 
				if((hh.value.length==0)||(hh.value.indexOf(':')!=-1)||(hh.value.indexOf('/')!=-1)||(hh.value.indexOf(' ')!=-1)||(hh.value.indexOf('*')!=-1)||(hh.value.indexOf('?')!=-1)||(hh.value.indexOf('.')!=-1)){
					alert('Недопустимые символы в приставке к имени дополнительного файла!');
					hh.focus();
					return false;
				}
				
				//если отмечен ресайз
				iss=document.getElementById('changeAddFile');
				if(iss.checked){
					//alert('fff');
					
					//выбраны пикселя
					if(GetRadioVal(document.forms['uploadfiles'].add_resize_kind)==0) {
						//alert('px');
						//alert('rr');
						ww=document.getElementById('add_new_w');
						if((isNaN(ww.value))||(ww.value<=0)){
							alert('Некорректная ширина в пикселях!');
							ww.focus();
							return false;
						}
						
						hh=document.getElementById('add_new_h');
						if((isNaN(hh.value))||(hh.value<=0)){
							alert('Некорректная высота в пикселях!');
							hh.focus();
							return false;
						}
					}
					
					//выбраны %
					if(GetRadioVal(document.forms['uploadfiles'].add_resize_kind)==1) {
						//alert('%');
						
						hh=document.getElementById('add_new_percent');
						if((isNaN(hh.value))||(hh.value<=0)||(hh.value>100)){
							alert('Указаны некорректные проценты!');
							hh.focus();
							return false;
						}
					}
					
				}
			}
			
			
			isc=document.getElementById('doPreview');
			//работа с превью
			if(isc.checked){			
				//alert('pre');
				ww=document.getElementById('pre_w');
				if((isNaN(ww.value))||(ww.value<=0)){
					alert('Некорректная ширина в пикселях!');
					ww.focus();
					return false;
				}
				
				hh=document.getElementById('pre_h');
				if((isNaN(hh.value))||(hh.value<=0)){
					alert('Некорректная высота в пикселях!');
					hh.focus();
					return false;
				}
			}
			return true;
		}
		</script>
		";
		
		
		
		
		
		
		
		
		$txt.='<p>';
		foreach($params as $k=>$v){
			$txt.='<input type="hidden" name="'.$k.'" id="'.$k.'" value="'.$v.'">';
		}
		
		$txt.="<div><input type=\"file\" name=\"photo_load0\" size=\"80\" style=\"color: black;\"></div>";
		$txt.="<div id=\"dop_zag\" style=\"display: block; margin-top: 0px;\"><a  href=\"javascript://\" onClick=\"document.all.dop_forms.style.display='block'; document.all.dop_zag.style.display='none';\">дополнительная загрузка...</a></div>";
		$txt.="
		<div id=\"dop_forms\" style=\"display: none;\">
	<input type=\"file\" name=\"photo_load1\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load2\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load3\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load4\" size=\"80\"><br>
	<div id=\"dop_zag2\" style=\"display: block;\"><a  href=\"javascript://\" onClick=\"document.all.dop_2.style.display='block'; document.all.dop_zag2.style.display='none';\">дополнительная загрузка...</a></div>
	<div id=\"dop_2\" style=\"display: none;\">
	<input type=\"file\" name=\"photo_load5\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load6\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load7\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load8\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load9\" size=\"80\"><br>	
	<div id=\"dop_zag3\" style=\"display: block;\"><a  href=\"javascript://\" onClick=\"document.all.dop_3.style.display='block'; document.all.dop_zag3.style.display='none';\">дополнительная загрузка...</a></div>
	<div id=\"dop_3\" style=\"display: none;\"><input type=\"file\" name=\"photo_load10\" size=\"80\"><br>	
	<input type=\"file\" name=\"photo_load11\" size=\"80\"><br>	
	<input type=\"file\" name=\"photo_load12\" size=\"80\"><br>	
	<input type=\"file\" name=\"photo_load13\" size=\"80\"><br>	
	<input type=\"file\" name=\"photo_load14\" size=\"80\"><br>	</div></div>
	</div>
		<br><p>
		";
		
		
		
		
		$txt.='<input type="submit" name="doLoad" id="doLoad" value="Отправить файлы" onclick="return CheckUploads();">&nbsp; ';
		$txt.='<input type="reset" name="doCancel" id="doCancel" value="Отмена"><p>';
		
		$txt.='<strong>Операции над файлами:</strong><br>';
		
		
		$txt.='<table width="*" cellspacing="2" cellpadding="2" border="0">
		<tr>
		    <td valign="top" width="50%">';
			//простое изменение размера
			$txt.=$this->DrawPhotoChangeSize($this->optimize_size[0], $this->optimize_size[1], $this->percentage);
			$txt.='</td>
		    <td valign="top" width="50%">';
			//создание превью
			$txt.=$this->DrawPhotoPreviewFile($this->preview_size[0],$this->preview_size[1]);
			$txt.='</td>
		</tr>
		<tr>
		    <td valign="top" width="50%">';
			//создание доп файла
			$txt.=$this->DrawPhotoAdditionalFile('','a','2','20',$this->optimize_size[2], $this->optimize_size[3]);
			$txt.='</td>
		    <td valign="top" width="50%">';
			//создание доп файла 1
			$txt.=$this->DrawPhotoAdditionalFile('1','ts','3','30',$this->optimize_size[4], $this->optimize_size[5]);
			$txt.='</td>
		</tr>
		</table>
		';
		
		
		$txt.='</form>';						
		
		
		$txt.='</div>';
		return $txt;
	}
	
	
	
	//отрисовка самого файла в блоке (или превью)
	protected function DrawOne($params){
		$txt='';
		
		//$txt.=$params['rel_path'];
//		$txt.=$this->base_path;
		
		//находим ширину и высоту фотки для показа
		$sz = @GetImageSize($this->base_path."/".$params['rel_path']);
		
		if($sz){
			$w=$sz[0];
			$h=$sz[1];	
			$kbsize=ceil(filesize($this->base_path."/".$params['rel_path'])/1024);
			
			$rat = $sz[0]/$sz[1];
				
			if($rat>=1){
			//горизонтальная - ширина=120 80
				if($sz[0]<$this->preview_size[0]) $w1=$sz[0];
				else $w1 = $this->preview_size[0];
				$h1 = ceil($w1*$sz[1]/$sz[0]);
			}else{
			//вертикальная - высота = 90 60
				if($sz[1]<$this->preview_size[1]) $h1=$sz[1];
				else $h1 = $this->preview_size[1];
				$w1 = ceil($sz[0]*$h1/$sz[1]);
			}		
			
			$durl = 'photodrawer.php?w1='.$w1.'&h1='.$h1.'&picname='.$params['rel_path'];
			
			$txt.='<table width="*" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr align="left" valign="top">
			<td width="1"><img src="../img/01.gif" alt="" width="1" height="'.$this->preview_size[1].'" border="0"></td>
			<td width="*" align="center" valign="middle">';
			$txt.="<a href=\"$this->root_path$params[rel_path]\" target=\"_blank\"><img src=\"$durl\" alt=\"\" width=\"$w1\" height=\"$h1\" border=\"0\"></a>";
			$txt.='</td>
			</tr>
			</table>';
			
			$txt.="<span style=\"font-size: 8pt;\"><strong>".basename($params['name'])."</strong><br>$w*$h&nbsp;пикс.&nbsp;&nbsp;$kbsize&nbsp;кбайт.</span>";
			
		}
		
		return $txt;
	}
	
	
	//отрисовка самого файла в блоке (или превью)
	protected function DrawFormControls($params){
		$txt='';
		
		if($this->mode!=32){
		
			//echo $this->root_path.$params['rel_path'].'<br>';
			//формируем код для работы
			//особые случаи - 3(2) и 9(3)
			
			$prist=eregi_replace("^/","",$this->root_path);
			
			
			if($this->mode==3){
				$tnname=substr($params['rel_path'], 0, (strlen($params['rel_path'])-strlen(basename($params['rel_path']))) ).'tn'.basename($params['rel_path']);
				$txt.="
				<a href=\"javascript://\" title=\"выбрать фото\" onClick=\"smilie('".$prist.$params['rel_path']."', '".$prist.$tnname."'); window.close();\"><img src=\"$this->image_icon\" alt=\"\" width=\"16\" height=\"16\" border=\"0\" align=\"middle\"></a>&nbsp;&nbsp;&nbsp;
				";
				
				
			}else if($this->mode==9){
				$tnname=substr($params['rel_path'], 0, (strlen($params['rel_path'])-strlen(basename($params['rel_path']))) ).'tn'.basename($params['rel_path']);
				
				$tsname=substr($params['rel_path'], 0, (strlen($params['rel_path'])-strlen(basename($params['rel_path']))) ).'ts'.basename($params['rel_path']);
				
	
				$taname=substr($params['rel_path'], 0, (strlen($params['rel_path'])-strlen(basename($params['rel_path']))) ).'a'.basename($params['rel_path']);
				
				$txt.="
				
				<a href=\"javascript://\" title=\"выбрать фото\" onClick=\"smilie('".$prist.$params['rel_path']."', '".$prist.$tnname."', '".$prist.$tsname."','".$prist.$taname."'); window.close();\"><img src=\"$this->image_icon\" alt=\"\" width=\"16\" height=\"16\" border=\"0\" align=\"middle\"></a>&nbsp;&nbsp;&nbsp;
				
				";
			
			}else $txt.="
			<a href=\"javascript://\" title=\"выбрать фото\" onClick=\"smilie('".$prist.$params['rel_path']."'); window.close();\"><img src=\"$this->image_icon\" alt=\"\" width=\"16\" height=\"16\" border=\"0\" align=\"middle\"></a>&nbsp;&nbsp;&nbsp;
			
			";
			
		}
		
		return $txt;
	}
	
	
	protected function DrawFormControlScript(){
		$txt='';
		
		if($this->mode!=32){
			//ФОРМИРУЕМ джаваскрипт
			$txt.="
			<script language=\"JavaScript\" type=\"text/javascript\">
				<!--
					if (navigator.userAgent.indexOf('Opera') != -1) 
					{advanced_code = 0;} else {
					if (navigator.appName && navigator.appName.indexOf(\"Microsoft\") != -1 &&  
					navigator.userAgent.indexOf(\"Windows\") != -1 && navigator.userAgent.indexOf(\"Windows 3.1\") == -1) {
					 advanced_code = 1; // use IE 4+ specific createRange functionality
					} else {
					 advanced_code = 0;
					}
					}";
					
					if($this->mode==9){
						$txt.="
						function smilie(sm1, sm2,sm3,sm4) {
					if (advanced_code) {
					      opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname'].".focus(); 
					        sel = opener.document.selection.createRange(); 
					        sel.collapse();
					        sel.text= \"\"+sm1+\"\";
							
							 opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname2'].".focus(); 
							 opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname2'].".value=\"\"; 
					        sel = opener.document.selection.createRange(); 
					        sel.collapse();
					        sel.text= \"\"+sm2+\"\";   
							
							 opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname3'].".focus(); 
							opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname3'].".value=\"\";
					        sel = opener.document.selection.createRange(); 
					        sel.collapse();
					        sel.text= \"\"+sm3+\"\";  
							
					        sel = opener.document.selection.createRange(); 
					        sel.collapse();
					        sel.text= \"\"+sm4+\"\";  
							
							
							
					} else {
					    opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname'].".value = sm1+\"\";
						  opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname2'].".value = sm2+\"\";
						  opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname3'].".value = sm3+\"\";
						
					}	
					}	
					
						";
					}else if($this->mode==3){
						$txt.="
						function smilie(smilietext, sm2) {
					if (advanced_code) {
					        opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname'].".focus(); 
					        sel = opener.document.selection.createRange(); 
					        sel.collapse();
					        sel.text= \"\"+smilietext+\"\";
							
							 opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname2'].".focus(); 
							opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname2'].".value=\"\";
					        sel = opener.document.selection.createRange(); 
					        sel.collapse();
					        sel.text= sm2;
					} else {
					   opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname'].".value = smilietext+\"\";
						opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname2'].".value = sm2+\"\";
					}	}	
						";
						
					}else $txt.="
					function smilie(smilietext) {
					if (advanced_code) {
					        opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname'].".focus(); 
					        sel = opener.document.selection.createRange(); 
					        sel.collapse();
					        sel.text= \"\"+smilietext+\"\";
					} else 
					    opener.document.".$this->manmode[$this->mode]['formname']."
.".$this->manmode[$this->mode]['inpname'].".value = smilietext+\"\";
					}				
					";
				
				
				
				$txt.="//-->
				</script>
			";
		}
		return $txt;
	
	}
	
	
	//служебная функция вывода формы изменения размера
	protected function DrawPhotoChangeSize($w,$h,$percent){
		$txt='';
		
		if($this->openmode[0]) $txt.="<input type=\"checkbox\" name=\"changeSize\" id=\"changeSize\" value=\"\" onclick=\"ss=document.getElementById('inisize'); if(this.checked){ ss.style.display='block';}else{ss.style.display='none';}\" checked><strong>Изменить размер</strong>";
		else $txt.="<input type=\"checkbox\" name=\"changeSize\" id=\"changeSize\" value=\"\" onclick=\"ss=document.getElementById('inisize'); if(this.checked){ ss.style.display='block';}else{ss.style.display='none';}\"><strong>Изменить размер</strong>";
			
		
		if($this->openmode[0]) $txt.='<div id="inisize" class="div_tools" style="display: block; margin-left: 10px; ">';
		else $txt.='<div id="inisize" class="div_tools" style="display: none; margin-left: 10px; ">';
		
		$txt.='<table cellspacing="0" cellpadding="10" border="0">
			<tr>
			    <td valign="top">';
				$txt.="<input type=\"radio\" name=\"resize_kind\" value=\"0\" checked onclick=\"pp=document.getElementById('resize_basic_pixel'); pr=document.getElementById('resize_basic_percent'); pp.style.color='black'; pr.style.color='silver';\"><em>по пикселям</em>";
	
				$txt.="<div id=\"resize_basic_pixel\"><input type=\"text\" name=\"new_w\" id=\"new_w\" value=\"".$w."\" size=\"3\" maxlength=\"4\"> на <input type=\"text\" name=\"new_h\" id=\"new_h\" value=\"".$h."\" size=\"3\" maxlength=\"4\">   <input type=\"checkbox\" name=\"do_cut\" id=\"do_cut\" value=\"\">деформация</div>";
	
	
				$txt.='</td>
	    		<td valign="top">';
	
				$txt.="<input type=\"radio\" name=\"resize_kind\" value=\"1\" onclick=\"pp=document.getElementById('resize_basic_pixel'); pr=document.getElementById('resize_basic_percent'); pp.style.color='silver'; pr.style.color='black';\"><em>по %</em>";		
				$txt.="<div id=\"resize_basic_percent\" style=\"color: silver;\"><input type=\"text\" name=\"new_percent\" id=\"new_percent\" value=\"".$percent."\" size=\"2\" maxlength=\"3\">%</div>";
		
			
			
				$txt.='</td>
			</tr>
		</table>';
		$txt.="</div>";
		return $txt;
	}
	
	
	protected function DrawPhotoAdditionalFile($no='',$prist='__',$indexmain='2',$indexsub='20',$w,$h){
		$txt='';
		
		if($this->openmode[$indexmain]){
			$txt.="<input type=\"checkbox\" name=\"makeAdd".$no."File\" id=\"makeAdd".$no."File\" value=\"\" onclick=\"ss=document.getElementById('add".$no."_file'); if(this.checked){ ss.style.display='block';}else{ss.style.display='none';}\" checked><strong>Создавать дополнительный файл</strong>";
			
			$txt.="<div id=\"add".$no."_file\" class=\"div_tools\" style=\"display: block; margin-left: 10px;\">";
		}else{
			$txt.="<input type=\"checkbox\" name=\"makeAdd".$no."File\" id=\"makeAdd".$no."File\" value=\"\" onclick=\"ss=document.getElementById('add".$no."_file'); if(this.checked){ ss.style.display='block';}else{ss.style.display='none';}\"><strong>Создавать дополнительный файл</strong>";
			
			$txt.="<div id=\"add".$no."_file\" class=\"div_tools\" style=\"display: none; margin-left: 10px;\">";
		}
		
		$txt.='Приставка к имени файла: <input type="text" name="add'.$no.'_file_pre" id=add'.$no.'_file_pre" value="'.$prist.'" size="2" maxlength="5"><br>';
		
		
		if($this->openmode[$indexsub]){
			$txt.="<input type=\"checkbox\" name=\"changeAdd".$no."File\" id=\"changeAdd".$no."File\" value=\"\" onclick=\"ss=document.getElementById('add".$no."size'); if(this.checked){ ss.style.display='block';}else{ss.style.display='none';}\" checked><strong>Менять размер файла</strong>";
			
			$txt.='<div id="add'.$no.'size" class="div_tools" style="display: block; margin-left: 10px; ">';
		}else{
			$txt.="<input type=\"checkbox\" name=\"changeAdd".$no."File\" id=\"changeAdd".$no."File\" value=\"\" onclick=\"ss=document.getElementById('add".$no."size'); if(this.checked){ ss.style.display='block';}else{ss.style.display='none';}\"><strong>Менять размер файла</strong>";
			
			$txt.='<div id="add'.$no.'size" class="div_tools" style="display: none; margin-left: 10px; ">';		
		}
		
		$txt.='<table cellspacing="2" cellpadding="10" border="0">
		<tr>
		    <td valign="top">';
	
	
				$txt.="<input type=\"radio\" name=\"add".$no."_resize_kind\" value=\"0\" checked onclick=\"pp=document.getElementById('add".$no."_resize_basic_pixel'); pr=document.getElementById('add".$no."_resize_basic_percent'); pp.style.color='black'; pr.style.color='silver';\"><em>по пикселям</em>";
			
				$txt.="<div id=\"add".$no."_resize_basic_pixel\"><input type=\"text\" name=\"add".$no."_new_w\" id=\"add".$no."_new_w\" value=\"".$w."\" size=\"3\" maxlength=\"4\"> на <input type=\"text\" name=\"add".$no."_new_h\" id=\"add".$no."_new_h\" value=\"".$h."\" size=\"3\" maxlength=\"4\">   <input type=\"checkbox\" name=\"add".$no."_do_cut\" id=\"add".$no."_do_cut\" value=\"\">деформация</div>";
		
		
				$txt.='</td>
	    		<td valign="top">';
		
		
				$txt.="<input type=\"radio\" name=\"add".$no."_resize_kind\" value=\"1\" onclick=\"pp=document.getElementById('add".$no."_resize_basic_pixel'); pr=document.getElementById('add".$no."_resize_basic_percent'); pp.style.color='silver'; pr.style.color='black';\"><em>по %</em>";		
				$txt.="<div id=\"add".$no."_resize_basic_percent\" style=\"color: silver;\"><input type=\"text\" name=\"add".$no."_new_percent\" id=\"add".$no."_new_percent\" value=\"15\" size=\"2\" maxlength=\"3\">%</div>";
		
		
		$txt.='</td>
		</tr>
		</table>';
			
		$txt.="</div>";
			
		$txt.="</div>";
		return $txt;
	}
	
	
	//служебная функция вывода формы для превью
	protected function DrawPhotoPreviewFile($w,$h){
		$txt='';
		
		if($this->openmode[1]){
			$txt.="<input type=\"checkbox\" name=\"doPreview\" id=\"doPreview\" value=\"\" onclick=\"ss=document.getElementById('presize'); if(this.checked){ ss.style.display='block';}else{ss.style.display='none';}\" checked><strong>Создавать превью</strong>";
			$txt.='<div id="presize" class="div_tools" style="display: block; margin-left: 10px;">';
		}else{
			$txt.="<input type=\"checkbox\" name=\"doPreview\" id=\"doPreview\" value=\"\" onclick=\"ss=document.getElementById('presize'); if(this.checked){ ss.style.display='block';}else{ss.style.display='none';}\"><strong>Создавать превью</strong>";
			$txt.='<div id="presize" class="div_tools" style="display: none; margin-left: 10px;">';
		}
		
		$txt.="<em>Размер превью:</em> <input type=\"text\" name=\"pre_w\" id=\"pre_w\" value=\"".$w."\" size=\"3\" maxlength=\"4\"> на <input type=\"text\" name=\"pre_h\" id=\"pre_h\" value=\"".$h."\" size=\"3\" maxlength=\"4\"> пикселей   <input type=\"checkbox\" name=\"pre_do_cut\" id=\"pre_do_cut\" value=\"\">деформация";
		
		$txt.="</div>";
		return $txt;
	}
	
	
	//служебная функция сопоставления выделенных файлов сессии с имеющимся списком
	protected function CheckSessionFiles($somefiles){
		//зачистка сессии при переходе
		//(сброс выделения файлов)
		foreach($_SESSION[$this->sessionname] as $k=>$v){
			if(!isset($somefiles[$k])) unset($_SESSION['photofolder'][$k]);
		}
		
	}
}
?>