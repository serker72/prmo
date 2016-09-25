<?
 

//итем меню
class MmenuItemNew extends MmenuItem{

	
	//установка всех имен
	protected function init(){
		$this->tablename='allmenu';
		$this->lang_tablename='menu_lang';
		$this->item=NULL;
		$this->pagename='page.php';		
		
		$this->mid_name='mid';
		$this->lang_id_name='lang_id';
		$this->vis_name='is_shown';
	}
	 
	
	
	
	//строим навигацию админскую
	public function DrawNavigArr($id, $lang_code=1, $is_shown=0,$endtext=' правка раздела '){
		$txt='';
		$arr=$this->RetrievePath($id, $flaglost, $vloj, $lang_code, $is_shown);
		
		 
		$alls=array();
		
		/*$alls[]=Array(
			'name'=>'главная',
			'url'=>'index.php' 
			 
		);
		
		$alls[]=Array(
			'name'=>'начало',
			'url'=>'razds.php'
			 
		);*/
		
		foreach($arr as $k=>$v){
			foreach($v as $kk=>$vv){
				//$strs.= "$kk $vv<br>";
				
				$alls[]=Array(
					'name'=>stripslashes($vv['name']),
					'url'=>'razds.php?id='.$kk 
				);
			}
		}
		
	 
		
		return $alls;
	}
	
	 
}
?>