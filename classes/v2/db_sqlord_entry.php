<?
require_once('db_abstract_entry.php');

class SqlOrdEntry extends AbstractEntry{
	protected $name;
	
	const ASC="asc";
	const DESC="desc";
	
	function __construct($name,$value=self::ASC){
		$this->name=$name;
		$this->value=$value;
	}
	
	public function Deploy(){
		return $this->name.' '.$this->value;	
	}
	
}
?>