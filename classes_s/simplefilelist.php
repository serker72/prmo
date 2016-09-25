<?
require_once('PageNavigatorKri.php');
require_once('filelist.php');
class SimpleFileList extends FileList{

	
	
	
	
	//отрисовка самого файла в блоке (или превью)
	protected function DrawOne($params){
		$txt='';
		
		$kbsize=ceil(filesize($this->base_path."/".$params['rel_path'])/1024);
		
		$txt.='<span style="font-size: 8pt;"><strong><nobr><img src="'.$this->doc_icon.'" alt="" border="0" align="absmiddle">'.$params['name'].'</nobr></strong>   &nbsp;&nbsp;Размер:&nbsp;'.$kbsize.'&nbsp;кбайт.</span>';
		
		
		return $txt;
	}
	
	
	
	
	//отрисовка элементов загрузки файла
	protected function DrawUploads($params){
		$txt='';
		
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
		return $txt;
	}
	
	
}
?>