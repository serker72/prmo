<?
require_once('abstractitem.php');

//����������� ���� ��� ������ ������������� �������

class AnFillAbstractEntry{
	
	const SIMPLE=0;
	const COMPLEX=1;
	const SUBSEQUENT=2;
	const SELECT=3;
	
	public $type; //��� ������: ����, ����������
	public $fieldname; //��� ���� � ������� ��
	public $caption; //������� � ���� ���������
	public $nf_value; //��������, �����. �������������� ����
	public $fields; //����� ����� ��� �����������
	public $is_checked; //�������� �� ���� � ����� ������
	
	public $data_source; //��������� ������ - ��������� ������ ��� �����������
	
	
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
	
	//������ ����������� ���� ��� �����������	
	public function SetFields($fields){
		$this->fields=$fields;
	}
	
	
	//������ �������� ������ ��� �����������
	public function SetDataSource($data_source){
		$this->data_source=$data_source;
	}
	
	//�������� ������ �� �����������
	public function GetData($id){
		return $this->data_source->GetItemsByIdArr($id);
	}
	
	//����� ����� ���� � ����������� ����� � ������ �����������, ������� ��� ��������
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