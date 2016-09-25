<?
require_once('abstractgroup.php');


// абстрактная группа
class InvCalcReasGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='invcalc_reasons';
		$this->pagename='invent.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
}
?>