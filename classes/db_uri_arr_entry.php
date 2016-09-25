<?
require_once('db_abstract_entry.php');

class UriArrEntry extends AbstractEntry{
	protected $name;
	protected $value;
	
	function __construct($name,$value,$action=parent::E){
		$this->name=$name;
		$this->value=$value;
		$this->action=$action;
	}
	
	public function Deploy($prefix, $prefix_exceptions=NULL){
		if(is_array($prefix_exceptions)&&in_array($this->name,$prefix_exceptions)) return $this->name.$this->action.urlencode($this->value);	
		else return $this->name.$prefix.'[]'.$this->action.urlencode($this->value);	
	}
	
}
?>