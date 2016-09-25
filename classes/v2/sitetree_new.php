<?
 

//класс - дерево сайта
class SiteTreeNew extends SiteTree{
	 
	
	//установка всех имен
	protected function init($lang_id){
		$this->lang_id=$lang_id;
		$this->rf=new ResFile(ABSPATH.'cnf/resources.txt');
		
		//УСТАНОВКА имен нетабличных разделов
		$this->non_tabs[]=Array(
			'url' => '/',
			'name' => $this->rf->GetValue('hmenu.php','main_caption',$this->lang_id),
			'before_all' => true
		);
		
		/*$this->non_tabs[]=Array(
			'url' => '/search.php',
			'name' => $this->rf->GetValue('hmenu.php','search_caption',$this->lang_id),
			'before_all' => false
		);
		
		if(HAS_BASKET){
		$this->non_tabs[]=Array(
			'url' => '/profile.php',
			'name' => $this->rf->GetValue('hmenu.php','profile_caption',$this->lang_id),
			'before_all' => false
		);
		}
		
		$this->non_tabs[]=Array(
			'url' => '/map.php',
			'name' => $this->rf->GetValue('hmenu.php','tree_caption',$this->lang_id),
			'before_all' => false
		);*/
		
	}
	 

//*************************************** АДМИНСКИЕ ФУНКЦИИ *****************************************
	//админское дерево сайта
	public function DrawTreeAdm(){
		$txt='';
		$lg=new LangGroup();
		$this->langs=$lg->GetLangsList();
		//вызываем служебную функцию для нулевого уровня вложенности
		$previous_path='/';
		$txt.=$this->GetTreeAdm(0, $previous_path);
		
		return $txt;
	}
	//админские отрисовки
	//рекурсивная функция отрисовки уровня дерева админская
	protected function GetTreeAdm($id, $previous_path, &$data){
		$txt='';
		$sql=' select *
		from allmenu as t
		where  t.parent_id="'.$id.'"
		order by t.is_hor desc, t.ord desc, t.id
		';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$sm=new SmartyAdm;
		$sm->debug=DEBUG_INFO;
		
		$mi=new MmenuItem();
		
		$alls=array();
		$data=array();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			$path=$previous_path.stripslashes($f['path']).'/';
			
			//выводим этот подраздел
			$defs='';
		
			if(HAS_PRICE&&($f['is_price']==1)) $defs.='<a href="viewpriceitems.php?id='.$f[0].'" target="_blank" title="Содержит каталог товаров"><img src="/img/newdis/modules/price18.png" alt="" width="18" height="18" hspace="2" vspace="0" border="0" alt="Содержит каталог товаров" ></a>';
			
			if(HAS_BASKET&&($f['is_basket']==1)) $defs.='<a href="viewpriceitems.php?id='.$f[0].'" target="_blank" title="Можно заказывать товар"><img src="/img/newdis/modules/basket18.png" alt="" width="18" height="18" hspace="2" vspace="0" border="0" alt="Можно заказывать товар" ></a>';
			
			if(HAS_NEWS&&($f['is_news']==1)) $defs.='<a href="viewnews.php?id='.$f[0].'" target="_blank" title="Содержит новости"><img src="/img/newdis/modules/news18.png" alt="" width="18" height="18" hspace="2" vspace="0" border="0" alt="Содержит новости" ></a>';
			
			if(HAS_LINKS&&($f['is_links']==1)) $defs.='<a href="viewlinks.php?id='.$f[0].'" target="_blank" title="Содержит каталог ссылок"><img src="/img/newdis/modules/links18.png" alt="" width="18" height="18" hspace="2" vspace="0" border="0" alt="Содержит каталог ссылок" ></a>';
			
			if(HAS_PAPERS&&($f['is_papers']==1)) $defs.='<a href="viewpapers.php?id='.$f[0].'" target="_blank" title="Содержит статьи"><img src="/img/newdis/modules/papers18.png" alt="" width="18" height="18" hspace="2" vspace="0" border="0" alt="Содержит статьи" ></a>';
	
			if(HAS_GALLERY&&($f['is_gallery']==1)) $defs.='<a href="viewphotos.php?id='.$f[0].'" target="_blank" title="Содержит фотогалерею"><img src="/img/newdis/modules/photos18.png" alt="" width="18" height="18" hspace="2" vspace="0" border="0" alt="Содержит фотогалерею" ></a>';
	
			if(HAS_FEEDBACK_FORMS&&($f['is_feedback_forms']==1)) $defs.='<img src="/img/newdis/modules/feedback18.png" alt="" width="18" height="18" hspace="2" vspace="0" border="0" alt="Содержит формы обратной связи" >';
				
			//языки
			$langs=Array();
			$obst_name=$path;
			foreach($this->langs as $k=>$v){
				
				$mm=$mi->GetItemById($f['id'],$v['id']);
				if($mm!=false){
					//if(!HAS_URLS&&($v['id']==LANG_CODE)) $obst_name=stripslashes($mm['name']);
					
					
					$langs[]=Array('url'=>'ed_razd.php?action=1&id='.$f[0].'&doLang=1&lang_id='.$v['id'],
					'lang_flag'=>stripslashes($v['lang_flag']),
					'id'=>$v['id'],
					 'name'=>stripslashes($mm['name']));
				}
			}
			
			//выводим все подразделы этого раздела (рекурсия)
			$subs=$this->GetTreeAdm($f['id'], $path, $data);
			
			$obst_name=$mi->ConstructPath($f[0],LANG_CODE);
			
			$alls[]=array('subs'=>$subs, 'sub_count'=>count($data), 'obst_name'=>$obst_name, 'defs'=>$defs, 'url_into'=>'razds.php?id='.$f[0], 'langs'=>$langs);
			
			//foreach($data as $k=>$v) $alls[]=$v;
			
			
		}
		
		$sm->assign('LANG_CODE', $this->lang_id);
		$sm->assign('in_new', (int)$this->is_new_pages);
		$sm->assign('items',$alls);
		
		$data=$alls;
		
		$txt=$sm->fetch($this->templates['level']);
		return $txt;
	}
	
	
	
	
	
	
	
//**********************************ENDOF АДМИНСКИЕ ФУНКЦИИ *****************************************	
	
	
	
	
	
	
	//инициализация шаблонов и признака открытия ссылок из дерева в новых окнах
	public function SetTemplates($templates){
		$this->templates=$templates;
	}
	
	public function SetNewPages($is_new_pages){
		$this->is_new_pages=$is_new_pages;
	}
}

?>