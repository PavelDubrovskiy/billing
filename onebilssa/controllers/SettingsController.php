<?
class SettingsController extends Site{
	function __construct(){
		$this->checkRights();
		if(Funcs::$uri[2]==''){
			$this->redirect('/settings/1/');
			//$model=new Settings;
			//View::render('settings/list',array('list'=>$model->getList()));
		}elseif(is_numeric(Funcs::$uri[2])){
			View::render('settings/listElem',array('list'=>Settings::getElemList(),'parent'=>Settings::getParent()));
		}
	}
	function showadd(){
		$parent=$_GET['parent'];
		if($parent=='')$parent=0;
		View::render('settings/add',array('formpath'=>'add','parent'=>$parent));
	}
	function showedit(){
		$model=new Settings;
		$data=$model->getEdit();
		$data['formpath']='edit';
		View::render('settings/add',$data);
	}
	function add(){
		$model=new Settings;
		$model->add();
		if($_POST['parent']!=0){
			$this->redirect('/settings/'.$_POST['parent'].'/');
		}else{
			$this->redirect('/settings/');
		}
	}
	function edit(){
		$model=new Settings;
		$model->edit();
		if($_POST['parent']!=0){
			$this->redirect('/settings/'.$_POST['parent'].'/');
		}else{
			$this->redirect('/settings/');
		}
	}
	function del(){
		$model=new Settings;
		$model->del();
		if($_GET['parent']!=0){
			$this->redirect('/settings/'.$_GET['parent'].'/');
		}else{
			$this->redirect('/settings/');
		}
	}
}
?>