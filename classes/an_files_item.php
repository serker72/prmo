<?

//вид файлов для отчета по файлам без описания

class AnFilesItem{
	
	public $description; //название для фильтра выбора...
	public $_id; //идентификатор для фильтра выбора...
	
	
	public $file_item_instance; //экземпляр файлового класса, можно получить: имя таблицы БД, storage_id, имя страницы реестра файлов
	public $download_filename; //имя файла для скачивания
	public $object_id; //объект доступа в соотв. файловый реестр
	public $has_rl_restrictions; //есть ли ограничения на папки true/false
	public $rl_object_id; //объект ограничения доступа к папкам
	
	
	
	
	//способ получения родительского документа
	public $document_id_fieldname; //имя поля род документа в таблице файлов
	public $document_tablename; //имя таблицы род документов в БД
	public $document_filename; //имя файла карты род документа
	
	public $filter_id_fieldname; //имя параметра ID в файловом реестре
	
	
	//поля для формирования названия род. документа
	public $document_affected_fields; 
	
	//текстовая часть названия род. документа
	public $document_text; 
	
	
	public $document_instance; //экземпляр класса документа (нужен для получения полей документа)
	
	public $file_folder_tablename; //имя таблицы в БД папок
	
	
	
	//способ получения организации
	//через родительский документ
	//если родительского документа не существует - то все организации
	//а как быть с контрагентом и организацией???
	//нужен признак... 
	//у 
	
	const DO_NOT=0;
	const BY_PARENT_DOC=1;
	//const BY_FILE=2;
	
	public $how_to_get_org;
	
	
	public $additional_params; //массив дополнительных параметров
	
	
	//еще нужно передать:
	/*
	класс папки
	параметры названия родительского документа... (т.е. какие поля подгружать!)
	
	*/
	
	public $filter_decorator; //декоратор для отбора по доступу к документам
	
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