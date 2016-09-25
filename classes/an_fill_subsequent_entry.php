<?
require_once('an_fill_abstract_entry.php');

//вложенные поля, искать внутри записи справочника

class AnFillSubsequentEntry extends AnFillAbstractEntry{
		public $value_fieldname;
		
	function __construct( $type=AnFillAbstractEntry::SUBSEQUENT, $fieldname='',  $value_fieldname='', $caption='', $nf_value='',$fields=NULL, $is_checked=false,  $descr_text='',$descr_fieldname='', $ident_filename=''){
		$this->type=$type;
		//$this->name2=$name2;
		$this->fieldname=$fieldname;
		$this->caption=$caption;
		$this->nf_value=$nf_value;
		$this->fields=$fields;
		$this->is_checked=$is_checked;
		$this->data_source=NULL;
		$this->value_fieldname=$value_fieldname;
		
		$this->descr_text=$descr_text;
		$this->descr_fieldname=$descr_fieldname;
		$this->ident_filename=$ident_filename;	
	}
	
	
	/*public function Compare($value){
		if($this->	
		
	}*/
	
	
	
	public function FindInVauledData($fieldname, $caption, $value_fieldname,  $entry, &$value){
		$res=false;
		$value=$this->nf_value;
		
		foreach($entry as $k=>$v){
			
			if(($k===$fieldname)&&($entry[$fieldname]===$caption)){
			
			
				$value=$entry[$value_fieldname];
				$res=true;
				
				return $res;
				break;
			}
		}
		
		
		return $res;	
	}
}
?>