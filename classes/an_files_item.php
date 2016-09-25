<?

//��� ������ ��� ������ �� ������ ��� ��������

class AnFilesItem{
	
	public $description; //�������� ��� ������� ������...
	public $_id; //������������� ��� ������� ������...
	
	
	public $file_item_instance; //��������� ��������� ������, ����� ��������: ��� ������� ��, storage_id, ��� �������� ������� ������
	public $download_filename; //��� ����� ��� ����������
	public $object_id; //������ ������� � �����. �������� ������
	public $has_rl_restrictions; //���� �� ����������� �� ����� true/false
	public $rl_object_id; //������ ����������� ������� � ������
	
	
	
	
	//������ ��������� ������������� ���������
	public $document_id_fieldname; //��� ���� ��� ��������� � ������� ������
	public $document_tablename; //��� ������� ��� ���������� � ��
	public $document_filename; //��� ����� ����� ��� ���������
	
	public $filter_id_fieldname; //��� ��������� ID � �������� �������
	
	
	//���� ��� ������������ �������� ���. ���������
	public $document_affected_fields; 
	
	//��������� ����� �������� ���. ���������
	public $document_text; 
	
	
	public $document_instance; //��������� ������ ��������� (����� ��� ��������� ����� ���������)
	
	public $file_folder_tablename; //��� ������� � �� �����
	
	
	
	//������ ��������� �����������
	//����� ������������ ��������
	//���� ������������� ��������� �� ���������� - �� ��� �����������
	//� ��� ���� � ������������ � ������������???
	//����� �������... 
	//� 
	
	const DO_NOT=0;
	const BY_PARENT_DOC=1;
	//const BY_FILE=2;
	
	public $how_to_get_org;
	
	
	public $additional_params; //������ �������������� ����������
	
	
	//��� ����� ��������:
	/*
	����� �����
	��������� �������� ������������� ���������... (�.�. ����� ���� ����������!)
	
	*/
	
	public $filter_decorator; //��������� ��� ������ �� ������� � ����������
	
	function __construct(
		$description,
		$_id,
		$file_item_instance,
		$download_filename,
		$object_id,
		$has_rl_restrictions,
		$rl_object_id,
		$additional_params,
		$document_id_fieldname,
		$document_tablename,
		$document_filename,
		$how_to_get_org=DO_NOT,
		$filter_id_fieldname,
		$document_affected_fields,
		$document_text,
		$document_instance,
		$file_folder_tablename,
		$filter_decorator=NULL
		
		
	){
		$this->description=$description;
		$this->_id=$_id;
		
		$this->file_item_instance=$file_item_instance;
		$this->download_filename=$download_filename;
		$this->object_id=$object_id;
		$this->has_rl_restrictions=$has_rl_restrictions;
		$this->rl_object_id=$rl_object_id;
		$this->additional_params=$additional_params;
		
		$this->document_id_fieldname=$document_id_fieldname;
		$this->document_filename=$document_filename;
		$this->document_tablename=$document_tablename;
		$this->how_to_get_org=$how_to_get_org;
		$this->filter_id_fieldname=$filter_id_fieldname;
		
		$this->document_affected_fields=$document_affected_fields;
		$this->document_text=$document_text;
		$this->document_instance=$document_instance;
		$this->file_folder_tablename=$file_folder_tablename;
		$this->filter_decorator=$filter_decorator;
	}
		
	
}
?>