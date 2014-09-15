<?
class ApiController extends Site{
	function __construct(){
		if(!in_array(Funcs::$uri[1],get_class_methods(__CLASS__))){
			$tree=Tree::getTreeByUrl('wide');
			Funcs::setMeta($tree);
			$tree['list']=Faq::getList($tree['id']);
			if($_POST){
				$error=Faq::ask($tree['id']);
				if($error){
					View::render('faq/list',$tree);
				}else{
					$this->redirect('/help/faq/');
				}
			}else{
				View::render('faq/list',$tree);
			}
		}
	}
	function payment(){
		DB::escapePost();
		//print '<pre>';print_r($_POST);print '</pre>';
		if($_POST){
			$token=Api::getToken();
			if($_POST['token']==$token){
				Api::payment();
				print View::getRenderEmpty('api/payment');
			}else{
				print json_encode(array('error'=>'Wrong token'));
			}
		}else{
			print json_encode(array('error'=>'Post empty'));
			//$this->redirect(Funcs::$conf['settings']['source']);
		}
	}
	function success(){
		DB::escapePost();
		$_SESSION['iuser']['token']=Api::getToken();
		Api::success();
		//print View::getRenderEmpty('api/paid',array('result'=>'true'));
		$this->redirect(Funcs::$conf['settings']['paysuccess']);
	}
	function fail(){
		DB::escapePost();
		$_SESSION['iuser']['token']=Api::getToken();
		Api::fail();
		//print View::getRenderEmpty('api/paid',array('result'=>'false'));
		$this->redirect(Funcs::$conf['settings']['payfail']);
	}
	function request(){
		DB::escapePost();
		$_SESSION['iuser']['token']=Api::getToken();
		$error=Api::request();
		print "Action REQUEST<br> Try connecting to 4myself...<BR><BR>"; 
		if($error){
			print "Error: insufficient funds"; 
		}
		
		
		/*if($_POST){
			
		}else{
			$this->redirect(Funcs::$conf['settings']['source']);
		}*/
	}
	
	function balance(){
		DB::escapePost();
		if(($_POST)&&($_POST['user_id']!='')&&(is_numeric($_POST['user_id']))){
			$token=Api::getToken();
			if($_POST['token']==$token){
				$dt=Api::balance();
				if ($dt['error']!='')
				print json_encode(array('error'=>$dt['error']));
				else
				print json_encode(array('balance'=>$dt['balance']));
				//print View::getRenderEmpty('api/payment');
			}else{
				print json_encode(array('error'=>'Wrong token'));
			}
		}else{
			print json_encode(array('error'=>'Post empty'));
			//$this->redirect(Funcs::$conf['settings']['source']);
		}
	}
	
}
?>