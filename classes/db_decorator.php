<?
require_once('db_uri_entry.php');
require_once('db_uri_arr_entry.php');
require_once('db_sql_entry.php');
require_once('db_sqlord_entry.php');

//генерация sql и uri для сложных фильтров
class DBDecorator{
	protected $entries=array();
	
	
	public function AddEntry(AbstractEntry $entry){
		$this->entries[]=$entry;	
	}
	
	
	//получить кусок запроса по фильтрации
	public function GenFltSql($mode=' and '){
		$txt='';
		$arr=array(); $to_skip=0;
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlEntry) {
				
				if($to_skip>0){
					$to_skip--;
					continue;	
				}
			
				
				if($v->GetAction()==SqlEntry::SKOBKA_L){
					//$_t_arr=array();
					$_t='';
					$_t.=''.$v->Deploy().'';
					$to_skip=0;
					foreach($this->entries as $kk=>$vv){
						if(($vv instanceof SqlEntry)&&($kk>$k)) {
							$to_skip++;
							
							$_t.=''.$vv->Deploy().'';
							
							if($vv->GetAction()==SqlEntry::SKOBKA_R){
								break;	
							}
						}
					}
					$arr[]=''.$_t.'';
				}else{
					$to_skip=0;
				
					$arr[]='('.$v->Deploy().')';
				}
			}
		}
		$txt=implode($mode,$arr);
		return $txt;
	}
	
	
	//получить кусок uri со значениями
	public function GenFltUri($mode='&', $prefix='', $prefix_exceptions=NULL){
		$txt='';
		$arr=array();
		foreach($this->entries as $k=>$v){
			if(($v instanceof UriEntry)||($v instanceof UriArrEntry)) $arr[]=$v->Deploy($prefix, $prefix_exceptions);
		}
		$txt=implode($mode,$arr);
		return $txt;
	}
	
	public function GetUris(){
	
		$arr=array();
		foreach($this->entries as $k=>$v){
			if(($v instanceof UriEntry)||($v instanceof UriArrEntry)) $arr[]=$v;

		}
		
		return $arr;
	}
	
	//получить строку сортировки
	public function GenFltOrd($mode=', '){
		$txt='';
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlOrdEntry) $arr[]=$v->Deploy();
		}
		$txt=implode($mode,$arr);
		return $txt;
	}
	
	public function GenFltOrdArr(){
		 
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlOrdEntry) $arr[]=$v;
		}
	 
		return $arr;
	}
	
	
	
	
	
	//получить список элементов SQL
	public function GetSqls(){
		$arr=array();
		foreach($this->entries as $k=>$v){
			if($v instanceof SqlEntry) $arr[]=$v; //$arr[]='('.$v->Deploy().')';
		}
		return $arr;
	}

}
?>