<?

class AbstractEntry{
	protected $name;
	//protected $name2;
	protected $value;
	protected $value2;
	protected $action;
	protected $in_values;
	
	const E="=";
	const L="<";
	const LE="<=";
	const G=">";
	const GE=">=";
	const NE="<>";
	const BETWEEN='between "%value1" and "%value2"';
	const LIKE=' LIKE "%%value%"';
	const IN_VALUES=' IN (%values)';
	
	const NOT_IN_VALUES=' NOT IN (%values)';
	const SKOBKA_L= '( ';
	const SKOBKA_R= ' ) ';
	const AE_OR= ' OR ';
	const AE_XOR= ' XOR ';
	const AE_AND= ' AND ';
	
	const LIKE_SET=' LIKE '; //переопределить в реализации
	const IN_SQL=' IN '; //переопределить в реализации
	const NOT_IN_SQL=' NOT IN '; //переопределить в реализации
	
	const IS_NULL=' is NULL '; 
	
	const IS_NOT_NULL='is NOT NULL '; 
	
	
	function __construct( $name, $value, $action, $value2=NULL,$in_values=NULL){
		$this->name=$name;
		//$this->name2=$name2;
		$this->value=$value;
		$this->value2=$value2;
		$this->action=$action;
		$this->in_values=$in_values;
		
	}
		
	public function Deploy(){}
	
	public function GetName(){
		return $this->name;	
	}
	public function GetValue(){
		return $this->value;	
	}
	
	public function GetAction(){
		return $this->action;	
	}
	
	
	public function GetValue2(){
		return $this->value2;	
	}
}
?>