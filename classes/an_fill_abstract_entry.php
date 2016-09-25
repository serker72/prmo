<?
require_once('abstractitem.php');

//абстрактное поля для отчета заполненность данными

class AnFillAbstractEntry{
	
	const SIMPLE=0;
	const COMPLEX=1;
	const SUBSEQUENT=2;
	const SELECT=3;
	
	public $type; //вид записи: поле, справочник
	public $fieldname; //имя поля в таблице БД
	public $caption; //подпись к полю текстовая
	public $nf_value; //величина, соотв. незаполненному полю
	public $fields; //набор полей для справочника
	public $is_checked; //выделено ли поле в форме отчета
	
	public $data_source; //экземпляр класса - источника данных для справочника
	
	
	public $descr_text;
	public $descr_fieldname;
	public $ident_filename;
	
	function __construct( $type=AnFillAbstractEntry::SIMPLE, $fieldname='', $caption='', $nf_value='',$fields=NULL, $is_checked=false, $descr_text='',$descr_fieldname='', $ident_filename='' ){
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
	
	//задать подчиненные поля для справочника	
	public function SetFields($fields){
		$this->fields=$fields;
	}
	
	
	//задать источник данных для справочника
	public function SetDataSource($data_source){
		$this->data_source=$data_source;
	}
	
	//получить данные из справочника
	public function GetData($id){
		return $this->data_source->GetItemsByIdArr($id);
	}
	
	//найти такое поле в подчиненных полях в записи справочника, вернуть его значение
	public function FindInData($fieldname, $entry, &$value){
		$res=false;
		$value=$this->nf_value;
		
		foreach($entry as $k=>$v){
			if($k===$fieldname){
				$value=$v;
				$res=true;
				
				return $res;
				break;
			}
		}
		
		
		return $res;	
	}
}
?>