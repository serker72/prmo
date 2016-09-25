<?
require_once('an_fill_abstract_entry.php');

//простые пол€ дл€ отчета заполненность данными

class AnFillComplexEntry extends AnFillAbstractEntry{
	
	function __construct( $type=AnFillAbstractEntry::COMPLEX, $fieldname='', $caption='', $nf_value='',$fields=NULL, $is_checked=false,  $descr_text='',$descr_fieldname='', $ident_filename=''){
		$this->type=$type;
		//$this->name2=$name2;
		$this->fieldname=$fieldname;
		$this->caption=$caption;
		$this->nf_value=$nf_value;
		$this->fields=$fields;
		$this->is_checked=$is_checked;
		$this->data_source=NULL;
		
		$this->descr_text=$descr_text;
		$this->descr_fieldname=$descr_fieldname;
		$this->ident_filename=$ident_filename;
		
	}
	
	
	/*public function Compare($value){
		if($this->	
		
	}*/
}
?>