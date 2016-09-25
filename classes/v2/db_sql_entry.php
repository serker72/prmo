<?
require_once('db_abstract_entry.php');

class SqlEntry extends AbstractEntry{
	
	public function Deploy(){
		
		$txt='';
		if($this->action==self::BETWEEN){
			$txt=str_replace('%value1',$this->value,self::BETWEEN);
			$txt=str_replace('%value2',$this->value2,$txt);
			
			$txt=$this->name.' '.$txt;
		}elseif($this->action==self::LIKE){
			$txt=str_replace('%value',$this->value,self::LIKE);
			$txt=$this->name.' '.$txt;
		}elseif($this->action==self::IN_VALUES){
			$values=implode(', ',$this->in_values);
			$txt=str_replace('%values',$values,self::IN_VALUES);
			$txt=$this->name.' '.$txt;
		}elseif($this->action==self::NOT_IN_VALUES){
			$values=implode(', ',$this->in_values);
			$txt=str_replace('%values',$values,self::NOT_IN_VALUES);
			$txt=$this->name.' '.$txt;
		}elseif($this->action==self::SKOBKA_L){
			$txt=self::SKOBKA_L;
		}elseif($this->action==self::SKOBKA_R){
			$txt=self::SKOBKA_R;
		}elseif($this->action==self::AE_OR){
			$txt=self::AE_OR;
		}elseif($this->action==self::AE_XOR){
			$txt=self::AE_XOR;
		}elseif($this->action==self::AE_AND){
			$txt=self::AE_AND;
		}elseif($this->action==self::LIKE_SET){
			
			$txt='';
			foreach($this->in_values as $k=>$v){
				if(strlen(trim($v))>0){
				  if(strlen($txt)>0) $txt.=' OR ';
				  $txt.=$this->name.self::LIKE_SET.' "%'.trim($v).'%" ';			
				}
			}
		
		}elseif($this->action==self::E_SET){
			
			$txt='';
			foreach($this->in_values as $k=>$v){
				if(strlen(trim($v))>0){
				  if(strlen($txt)>0) $txt.=' OR ';
				  $txt.=$this->name.self::E_SET.' "'.trim($v).'" ';			
				}
			}
		
		}elseif($this->action==self::IN_SQL){
			
			$txt=$this->name.self::IN_SQL.' ('.trim($this->value).') ';
		}elseif($this->action==self::NOT_IN_SQL){
			
			$txt=$this->name.self::NOT_IN_SQL.' ('.trim($this->value).') ';
		}else{
			$txt=$this->name.$this->action.'"'.$this->value.'"';	
		}
		
		return $txt;
	}
	
}
?>