<?
require_once('PageNavigatorKri_.php');
require_once('filelist.php');
require_once('classes/resoursefile.php');

//переведено на Смарти
class FileList_S extends FileList{
	protected $restricted_files='.htaccess';
	
	public function GetFileList($from=0,$per_page=PHOTOS_PER_PAGE,$allowable='jpg|jpe|jpeg|gif|png|wbm',$params=Array()){
		$txt='';
		
		$res=Array(); $rows=Array();
		
		$this->mode=$params['mode'];
		
		$this->CreateFileList($allowable);
		
		$totalcount=count($this->files);
		$str='';
		foreach($params as $k=>$v){
			$str.="&$k=$v";
		}
		
		$navig = new PageNavigatorKri_($this->prog_name,$totalcount,$per_page,$from,10,$str);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		
		$cter=0; $photo_row_cter=0; $photo_cter=0;
		
		//массив для хранения имен файлов
		$filearr=Array();
		
		$cells=Array();
		
		foreach($this->files as $k=>$v){
			if(($cter>=$from)&&($cter<$totalcount)) {
				if($photo_row_cter==0){
					 $cells=Array();
				}
				
				$filearr[]=$v['rel_path'];
				
				$size=filesize($this->base_path."/".$v['rel_path']);
				if($size<1024){
					$size=$size.'&nbsp;B';
				}else if($size>=1024){
					$size=ceil(filesize($this->base_path."/".$v['rel_path'])/1024).'&nbsp;kB';
				}else if($size>=1024*1024){
					$size=ceil(filesize($this->base_path."/".$v['rel_path'])/(1024*1024)).'&nbsp;MB';
				}else{
					$size=ceil(filesize($this->base_path."/".$v['rel_path'])/(1024*1024*1024)).'&nbsp;GB';
				}
				
				
				$cells[]=Array(
					'td_width'=>ceil(100/PHOTOS_PER_ROW),
					'download_path'=> 'srv/get.php?name='.$v['rel_path'], //'#zozo',
					'pict'=>$this->doc_icon,
					'filename'=>$v['name'],
					'filesize'=>$size
				);
				
				//echo "<h1>$cter</h1>";
				if($photo_row_cter<PHOTOS_PER_ROW-1) $photo_row_cter++;
				else{ 
					$photo_row_cter=0;
					$rows[]=Array('cells'=> $cells);
					$cells=Array();
				}
    			$photo_cter++;
				$txt.='</td>'."\n";
			}
			
			$cter++;
			if($photo_cter>=$per_page) break;
		}
		//вывод оставшихся при досрочном выходе из цикла файлов
		if(isset($cells)&&(count($cells)>0)){
					 $rows[]=Array('cells'=> $cells);
		}
		
		$res=Array(
			'rows'=>$rows,
			'pages'=> $navig->GetNavigator()
		);
		return $res;
	}
	
	
	//создаем массив из файлов
	//(с вычетом запрещенных файлов)
	protected function CreateFileList($allowable='jpg|jpe|jpeg|gif|png|wbm'){
		$hand = opendir($this->path);
		$this->files=array();
		
		while(($name=readdir($hand))!==false){
			if(($name==".")||($name==".."))  continue;
			if(is_file($this->path."/".$name)){
			//выводим файлы...
				if((!eregi($name,$this->restricted_files))&&($allowable!=NULL)&&(eregi("^(.*)\\.(".$allowable.")$",$name,$P))) {
					$this->files[eregi_replace("//","/", $name)]=Array(
						'name' => eregi_replace("//","/",$name),
						'path' => eregi_replace("//","/",$this->path),
						'url' => eregi_replace("//","/",$this->path."/".$name),
						'rel_path'=>eregi_replace("//","/",substr($this->path.'/'.$name, strlen($this->base_path)))
					);
/*						'durl' => 'fotodrawer.php?w1='.$w1.'&h1='.$h1.'&picname='.$fullpath."/".$name*/
				}else if((!eregi($name,$this->restricted_files))&&($allowable==NULL)){
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

	
	
	
	
	//отрисовка самого файла в блоке (или превью)
	protected function DrawOne($params){
		$txt='';
		/*
		$kbsize=ceil(filesize($this->base_path."/".$params['rel_path'])/1024);
		
		$txt.='<span style="font-size: 8pt;"><strong><nobr><img src="'.$this->doc_icon.'" alt="" border="0" align="absmiddle">'.$params['name'].'</nobr></strong>   &nbsp;&nbsp;Размер:&nbsp;'.$kbsize.'&nbsp;кбайт.</span>';
		
		*/
		return $txt;
	}
	
	
	
	
	//отрисовка элементов загрузки файла
	protected function DrawUploads($params){
		$txt='';
		/*
		$txt.='<div align="left" style="">';
		
		//$txt.=substr($this->path, strlen($this->base_path)); s
		
		$txt.='<form enctype="multipart/form-data" action="'.$this->prog_name.'" method="post" name="uploadfiles" id="uploadfiles">';
		$txt.='Закачать файлы (максимальный суммарный размер='.ini_get('upload_max_filesize').'):<br>';
		
		
		foreach($params as $k=>$v){
			$txt.='<input type="hidden" name="'.$k.'" id="'.$k.'" value="'.$v.'">';
		}
		
		$txt.="<div><input type=\"file\" name=\"photo_load0\" size=\"80\"></div>";
		$txt.="<div id=\"dop_zag\" style=\"display: block; margin-top: 0px;\"><a href=\"javascript://\" onClick=\"document.all.dop_forms.style.display='block'; document.all.dop_zag.style.display='none';\">дополнительная загрузка...</a></div>";
		$txt.="
		<div id=\"dop_forms\" style=\"display: none;\">
	<input type=\"file\" name=\"photo_load1\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load2\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load3\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load4\" size=\"80\"><br>
	<div id=\"dop_zag2\" style=\"display: block;\"><a href=\"javascript://\" onClick=\"document.all.dop_2.style.display='block'; document.all.dop_zag2.style.display='none';\">дополнительная загрузка...</a></div>
	<div id=\"dop_2\" style=\"display: none;\">
	<input type=\"file\" name=\"photo_load5\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load6\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load7\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load8\" size=\"80\"><br>
	<input type=\"file\" name=\"photo_load9\" size=\"80\"><br>	
	<div id=\"dop_zag3\" style=\"display: block;\"><a href=\"javascript://\" onClick=\"document.all.dop_3.style.display='block'; document.all.dop_zag3.style.display='none';\">дополнительная загрузка...</a></div>
	<div id=\"dop_3\" style=\"display: none;\"><input type=\"file\" name=\"photo_load10\" size=\"80\"><br>	
	<input type=\"file\" name=\"photo_load11\" size=\"80\"><br>	
	<input type=\"file\" name=\"photo_load12\" size=\"80\"><br>	
	<input type=\"file\" name=\"photo_load13\" size=\"80\"><br>	
	<input type=\"file\" name=\"photo_load14\" size=\"80\"><br>	</div></div>
	</div>
		
		";
		
		$txt.='<input type="submit" name="doLoad" id="doLoad" value="Отправить файлы" onclick="return CheckUploads();">&nbsp; ';
		$txt.='<input type="reset" name="doCancel" id="doCancel" value="Отмена"><p>';
		$txt.='</form>';						
		
		
		$txt.='</div>';
		*/
		return $txt;
	}
}
?>