<?
 

// 
class StatsItem extends AbstractItem{
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='gydex_stats';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		//$this->subkeyname='mid';	
	}
	 
	
	
}
?>