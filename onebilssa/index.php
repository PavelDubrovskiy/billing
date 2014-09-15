<?php
$startMemory=memory_get_usage();
$user_agent = $_SERVER ['HTTP_USER_AGENT']; // ie6 must die 
if (stripos ( $user_agent, 'MSIE 6.0' ) !== false && stripos ( $user_agent, 'IEMobile' ) === false) {
	header ( "Location: /ie6/ie6.html" );die();
}
if((isset($_GET['clear']))&&($_GET['clear']=='cookies')){
	$_COOKIE[$key]=array();
	$_SESSION=array();
	echo '<script>document.location.href="/";</script>';
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/redirects.php')){
	require_once $_SERVER['DOCUMENT_ROOT'].'/redirects.php';
}
$start = microtime(true);
require_once $_SERVER['DOCUMENT_ROOT'].'/settings.php';
session_start();
$dir=explode('/',$_SERVER['PHP_SELF']);
$current_dir=$dir[1];
if($current_dir!=ONESSA_DIR){
	$current_dir='';
}else{
	$current_dir='/'.$current_dir;
}
if(INSTALL==0){
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/DB'.BDCONNECTION.'.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/Funcs.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/View.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/Email.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/Extra.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/Site.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/Cache.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/Fields.php';
	new DB;
	new Funcs;
}elseif(INSTALL==1){
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/class/DBMySQL.php';
	new DB;
	require_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/install.php';
	new Install;
	print 'Installation complete';die;
}
$dir=opendir($_SERVER['DOCUMENT_ROOT'].$current_dir.'/widgets/');
while ($class = readdir($dir)){
	if (strpos($class,'.php')){
		require_once $_SERVER['DOCUMENT_ROOT'].$current_dir.'/widgets/'.$class;
	}
}
$dir=scandir($_SERVER['DOCUMENT_ROOT'].$current_dir.'/models/');
foreach($dir as $class){
	if (strpos($class,'.php') && substr($class,0,1)!='_'){
		require_once $_SERVER['DOCUMENT_ROOT'].$current_dir.'/models/'.$class;
	}
}
$controllers=array();
if($current_dir){
	$dir=opendir($_SERVER['DOCUMENT_ROOT'].PLUGINS_DIR);
	while ($pluginDir = readdir($dir)){
		$pdir = opendir($_SERVER['DOCUMENT_ROOT'].PLUGINS_DIR.$pluginDir.'/');
		if($pluginDir != '.' && $pluginDir != '..' && substr($pluginDir,0,1)!='_'){
			while($plugin = readdir($pdir)){
				$pdir = opendir($_SERVER['DOCUMENT_ROOT'].PLUGINS_DIR.$pluginDir.'/controllers/');
				while ($plugin = readdir($pdir)){
					if (strpos($plugin,'Controller.php')){
						$controller = strtolower(substr($plugin,0,strlen($plugin)-14));
						$controllers[$controller]=array('name'=>$controller, 'path'=> PLUGINS_DIR.$pluginDir.'/controllers/');
					}
				}
				$pdir=opendir($_SERVER['DOCUMENT_ROOT'].PLUGINS_DIR.$pluginDir.'/models/');
				while ($plugin = readdir($pdir)){
					if (strpos($plugin,'.php')){
						require_once $_SERVER['DOCUMENT_ROOT'].PLUGINS_DIR.$pluginDir.'/models/'.$plugin;
					}
				}
			}		
		}
	}
}else{
	if(!empty(OneSSA::$includePlugins)){
		foreach(OneSSA::$includePlugins as $plugin){
			$dir=opendir($_SERVER['DOCUMENT_ROOT'].PLUGINS_DIR.$plugin.'/models/');
			while ($class = readdir($dir)){
				if (strpos($class,'.php')){
					require_once $_SERVER['DOCUMENT_ROOT'].PLUGINS_DIR.$plugin.'/models/'.$class;
				}
			}
		}
	}
}
if($current_dir){
	$dir=opendir($_SERVER['DOCUMENT_ROOT'].$current_dir.'/controllers/');
	while ($class = readdir($dir)){
		if (strpos($class,'Controller.php')){
			$controller = strtolower(substr($class,0,strlen($class)-14));
			$controllers[$controller]=array('name'=>$controller, 'path'=>$current_dir.'/controllers/');
		}
	}
	if(array_key_exists(Funcs::$uri[1],$controllers)){
		$class=Funcs::$uri[1];
		$class=strtoupper(substr($class,0,1)).substr($class,1,strlen($class));
		$key = strtolower($class);
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$controllers[$key]['path'].$class.'Controller.php')){
			require_once $_SERVER['DOCUMENT_ROOT'].$controllers[$key]['path'].$class.'Controller.php';
		}
		eval('$mod = new '.$class.'Controller;');
		if(Funcs::$uri[2]!='' && !is_numeric(Funcs::$uri[2])){
			if(method_exists($class.'Controller',Funcs::$uri[2].'')){
				eval('$mod->'.Funcs::$uri[2].'();');
			}
		}
	}
	else { // unknown module
		require_once $_SERVER['DOCUMENT_ROOT'].'/'.$current_dir.'/controllers/IndexController.php';
		$mod = new IndexController;
	}
}else{
	$controllers=array();
	$dir=opendir($_SERVER['DOCUMENT_ROOT'].'/controllers/');
	while($class = readdir($dir)) if(strpos($class,'Controller.php')) $controllers[]=strtolower(substr($class,0,strlen($class)-14));
	$suburi=Funcs::$uri;
	if(OneSSA::$routes){
		foreach(OneSSA::$routes as $from=>$to){
			if(strpos($_SERVER['REQUEST_URI'],str_replace('*','',$from))!==false){
				if(strpos($from,'*')!==false){
					$from=str_replace('*','',$from);
					$uri=substr($_SERVER['REQUEST_URI'],strpos($_SERVER['REQUEST_URI'],$from),strlen($_SERVER['REQUEST_URI']));
				}else{
					$_REQUEST_URI=str_replace($from,$to,$_SERVER['REQUEST_URI']);
					if(strpos($_REQUEST_URI,'?')!==false)	$uri=substr($_REQUEST_URI,0,strpos($_REQUEST_URI,'?'));
					else $uri=$_REQUEST_URI;
				}
				$suburi=explode('/',substr(str_replace('.html','',$uri),1,strlen($uri)));
				if($suburi[count($suburi)-1]=='')unset($suburi[count($suburi)-1]);
				
			}
		}
	}
	if(in_array($suburi[0],$controllers)){
		$class=$suburi[0];
		$class=strtoupper(substr($class,0,1)).substr($class,1,strlen($class));
		require_once $_SERVER['DOCUMENT_ROOT'].'/controllers/'.$class.'Controller.php';
		eval('$mod = new '.$class.'Controller;');
		if($suburi[1]!='' && !is_numeric($suburi[1])){
			if(method_exists($class.'Controller',$suburi[1].'')){
				eval('$mod->'.$suburi[1].'();');
			}
		}
	}else { // unknown module
		require_once $_SERVER['DOCUMENT_ROOT'].'/controllers/IndexController.php';
		$mod = new IndexController;
	}
}
if((DEBUG==3 && $current_dir) || (DEBUG==2 && !$current_dir)){
	echo 'Время выполнения скрипта '.(microtime(true) - $start).'<br />';
	echo 'Количество SQL-запросов '.DB::$count.'<br />';
	echo 'Количество потребляемой памяти '.number_format((memory_get_usage() - $startMemory),0,'',' ').' bytes<br />';
	echo 'COOKIE: <br /><pre>';
	print_r($_COOKIE);
	echo '</pre><br />SESSION: <br /><pre>';
	print_r($_SESSION);
	echo '</pre><br /><br /><a href="?clear=cookies">Сбросить куки и сессии</a><br /><br />';
}