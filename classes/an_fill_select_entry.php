<?
require_once('an_fill_abstract_entry.php');

//������� ���� ��� ������ ������������� �������

class AnFillSelectEntry extends AnFillAbstractEntry{
	
	function __construct( $type=AnFillAbstractEntry::SELECT, $fieldname='', $caption='', $nf_value='',$fields=NULL, $is_checked=false,  $descr_text='',$descr_fieldname='', $ident_filename=''){
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
	
	
	//�������� ������ �� �����������
	public function GetData($id){
		$data=$this->data_source->GetItemById($id);
		return $data['name'];
	}
}
?>