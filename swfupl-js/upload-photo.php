<?php
	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();
	
	
	
	  require_once('../../classes/mmenuitem.php');
	  require_once('../../classes_s/resize_applyer_nonglob.php');
	require_once('../../classes/photoitem.php');	
	  
	  $visib_path = 'userfiles/image';
	  $upload_init_path=ABSPATH.$visib_path;
	  
	  $zz='';
	  
	
	
	  require_once('../../classes/mmenuitem.php');
	  require_once('../../classes_s/resize_applyer_nonglob.php');
  
	  $visib_path = 'userfiles/images';
	  $upload_init_path=ABSPATH.$visib_path;
	  
	  $zz='';
	  
	  if (!isset($_POST["mid"])) {
		  echo iconv('windows-1251', 'utf-8', 'alert("Ошибка определения каталога загрузки!")');
		  
	  }else {
		  $mid=abs((int)$_POST["mid"]);
	  
		  $mi=new MmenuItem;
		  $path= $mi->RetrievePath($mid, $flaglost, $vloj);
		  
		  
		  $dirs=Array();
				  
		  foreach($path as $k=>$v) {
			  foreach($v as $kk=>$vv){
				  //если есть поле "путь", то пишем его, если нет - то айди раздела
				  if($vv['path']==''){
					  $dirs[]=$kk;
				  }else $dirs[]=SecurePath($vv['path']);
			  }
		  }
		  
		  
		  if(file_exists($upload_init_path)&&is_dir($upload_init_path)){
		  //создаем каталоги от корня
			foreach($dirs as $k=>$v){
				$zz.='/'.$v;
				if(file_exists($upload_init_path.$zz)&&is_dir($upload_init_path.$zz)){
					
				}else mkdir($upload_init_path.$zz);
				
			}
		  }
		  //echo 'alert("'.$zz.'")';
		  //$zz='photogals';
	  }
	  
	  //обработка файла
	  
	  $resize_main=Array(
					  'doit'=>true,
					  'do_resize'=>false,
					  'resize_kind'=>0,
					  'pre'=>'',
					  'resize_params'=>Array(
						  'w'=>1600,
						  'h'=>1600,
						  'cutit'=>false
					  )
				  );
	  
	  $resize_tn=Array(
					  'doit'=>true,
					  'do_resize'=>true,
					  'resize_kind'=>0,
					  'pre'=>'tn',
					  'resize_params'=>Array(
						  'w'=>360,
						  'h'=>360,
						  'cutit'=>false
					  )
				  );
				  
	  $resize_smalls=Array(
					  'doit'=>true,
					  'do_resize'=>true,
					  'resize_kind'=>0,
					  'pre'=>'ts',
					  'resize_params'=>Array(
						  'w'=>120,
						  'h'=>120,
						  'cutit'=>false
					  )
				  );			  
				  
	  $pa=new ResizeApplyer();
	  $addname=time();
	  
	// echo 'alert("'.$_FILES['Filedata'].'")';
	$_FILES['Filedata']['name']=SecurePath(iconv("utf-8","windows-1251",$_FILES['Filedata']['name']));
	 
	 
	  
	  $newname1=$pa->MakePhoto($_FILES['Filedata'],$resize_main,$upload_init_path.$zz,$addname);
	  //small
	  $newname2=$pa->MakePhoto($_FILES['Filedata'],$resize_tn,$upload_init_path.$zz,$addname);
	
	$newname3=$pa->MakePhoto($_FILES['Filedata'],$resize_smalls,$upload_init_path.$zz,$addname);
	
	
	  
	  echo '
	  $("#photo_small").attr("value", "'.$visib_path.$zz.'/'.basename($newname3).'");
	  $("#photo_med").attr("value", "'.$visib_path.$zz.'/'.basename($newname2).'");
	  $("#photo_big").attr("value", "'.$visib_path.$zz.'/'.basename($newname1).'");
	  
	  $("#photolink").attr("href", "/'.$visib_path.$zz.'/'.basename($newname1).'");
	  $("#photolink img:first").attr("src", "/'.$visib_path.$zz.'/'.basename($newname2).'");
	  
	  ';
	
	exit(0);

?>