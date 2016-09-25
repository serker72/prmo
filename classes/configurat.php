<?
//класс-формирователь формы настроек для страницы общих настроек
class Configurat{

	protected $settings=array();

	
	//установка всех имен
	function __construct(array $settings){
		$this->settings=$settings;
	}
	
	
	//подготовка массива для формы
	public function BuildFormArr(){
		$arr=array();
		
		//$itm[]=array('caption' 'fieldtype' 'value'
		
		foreach($this->settings as $k=>$v){
			if(eregi("_CAPTION$",$k)) continue;	
			//echo " $k ";
			$inputtype='';
			switch($v['type']){
				case 'bool':
					$inputtype='checkbox';
				break;
				case 'string':
					$inputtype='text';
				break;
				case 'text':
					$inputtype='textarea';
				break;
				default:
					$inputtype='text';
				break;
			};
			
			
			$_caption=$this->FindCaption($k);
			if($_caption!==NULL)
				$caption=$this->settings[$_caption]['value'];
			else
				$caption='';
						
			$arr[]=array('caption'=>$caption, 'fieldtype'=>$inputtype, 'value'=>$v['value'], name=>$k);
		}
		
		return $arr;
	}
	
	//показ формы по шаблону
	public function BuildForm($template){
		$sm=new SmartyAdm;
		$sm->assign('items', $this->BuildFormArr());
		return $sm->fetch($template);
	}
	
	
	//служебная ф-ия поиска значения подписи к данному полю
	protected function FindCaption($name){
		$res=NULL;
		foreach($this->settings as $k=>$v){
			if(!eregi("_CAPTION$",$k)) continue;
			if($name."_CAPTION"==$k){
				$res=$k;
				break;	
			}
		}
		
		return $res;
	}
	
	
};
?>