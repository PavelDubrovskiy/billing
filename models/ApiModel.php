<?
//include_once $_SERVER['DOCUMENT_ROOT'].'/'.ONESSA_DIR.'/lib/mycurl.php';
class Api{
	public static $token='';
	public function getToken(){
		if(Funcs::$infoblock['important']['token']['description'] && date('Y-m-d H:i:m',strtotime(Funcs::$infoblock['important']['token']['edate']))>date('Y-m-d H:i:m')){
			Api::$token=Funcs::$infoblock['important']['token']['description'];
			return  Api::$token;
		}else{
			$curl=new mycurl(Funcs::$conf['settings']['source'].'/apitoken');
			$post=array(
				'client_id'=>12141,
				'client_secret'=>base64_encode('w8s2987mqz'),
				'grant_type'=>'partner_token',
			);
			$curl->setPost($post);
			$curl->createCurl();
			$token=json_decode($curl->__tostring());
			if($token->access_token){
				Api::$token=$token->access_token;
				$sql='UPDATE {{infoblock}} SET description=\''.Api::$token.'\', edate=\''.date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s').' +'.$token->expires_in.' seconds')).'\' WHERE id=2';
				DB::exec($sql);
				return  Api::$token;
			}else{
				return 'error';	
			}
		}
	}
	public function payment(){
		$_SESSION['iuser']=$_POST;
		/*$curl=new mycurl('http://www.pps.gazprombank.ru/payment/start.wsm?lang=RU&merch_id=7BF970824904C6E7F0C03AA2334367E5&back_url_s=https://merchant.ru/succeeded.jsp&back_url_f=http://merchant.ru/failed.jsp&o.order_id=28735');
		$post=array(
			'client_id'=>12141,
			'client_secret'=>base64_encode('w8s2987mqz'),
			'grant_type'=>partner_token,
		);
		$curl->setPost($post);
		$curl->createCurl();
		print_r($curl->__tostring());
		$token=json_decode($curl->__tostring());
		//$this->redirect('/api/success/');*/
	}
	public static function success(){
		$data=Api::sendPayResult(1);
		if($data->success==1){
			Api::createOrder(1);
		}
	}
	public static function fail(){
		$data=Api::sendPayResult(0);
		if($data->success==0){
			Api::createOrder(0);
		}else{
			print 'error';
		}
	}
	public static function sendPayResult($result){
		$curl=new mycurl(Funcs::$conf['settings']['payresult']);
		$post=array(
			'token'=>$_SESSION['iuser']['token'],
			'pay_id'=>$_SESSION['iuser']['pay_id'],
			'user_id'=>$_SESSION['iuser']['user_id'],
			'amount'=>$_SESSION['iuser']['amount'],
			'amount_comm'=>$_SESSION['iuser']['amount']/10,
			'currency'=>$_SESSION['iuser']['currency'],
			'payment_id'=>111,
			'pay_date'=>date('Y-m-d H:i:s'),
			'result'=>$result,
		);
		$curl->setPost($post);
		$curl->createCurl();
		$data=json_decode($curl->__tostring());
		return $data;
	}
	public function createOrder($paid=0){
		$curl=new mycurl(Funcs::$conf['settings']['source'].'/api/bill/user');
		$post=array(
			'token'=>$_SESSION['iuser']['token'],
			'user_id'=>$_SESSION['iuser']['user_id']
		);
		$curl->setPost($post);
		$curl->createCurl();
		$data=json_decode($curl->__tostring());
		$sql='SELECT id FROM {{iusers}} WHERE code=\''.$data->user_id.'\' AND author=0';
		$iuser=DB::getOne($sql);
		if(!$iuser){
			$sql='
				INSERT INTO {{iusers}} 
				SET code=\''.$data->user_id.'\',
					name=\''.$data->name.'\',
					phone=\''.$data->tel.'\'
			';
			$iuser=DB::exec($sql);
		}
		$curl=new mycurl(Funcs::$conf['settings']['source'].'/api/bill/payment');
		$post=array(
			'token'=>$_SESSION['iuser']['token'],
			'pay_id'=>$_SESSION['iuser']['pay_id']
		);
		$curl->setPost($post);
		$curl->createCurl();
		$data=json_decode($curl->__tostring());
		if(!empty($data->goods)){
			foreach($data->goods as $item){
				$sql='SELECT id FROM {{iusers}} WHERE code=\''.$item->author_id.'\' AND author=1';
				$author=DB::getOne($sql);
				if(!$author){
					$sql='
						INSERT INTO {{iusers}} 
						SET code=\''.$item->author_id.'\',
							name=\''.$item->name_author.'\',
							author=1
					';
					$author=DB::exec($sql);
				}
				$authorprice=round($item->amount-$item->amount/100*Funcs::$conf['settings']['comm'],2);
				$sql='
					INSERT INTO {{orders}}
					SET iuser='.$iuser.',
						author='.$author.',
						iuserprice='.$item->amount.',
						authorprice='.$authorprice.',
						payid='.$_SESSION['iuser']['pay_id'].',
						paymentid=\''.$_POST['paymentid'].'\',
						commision='.Funcs::$conf['settings']['comm'].',
						goodsid=\''.$item->id.'\',
						goodsname=\''.$item->name.'\',
						cdate=NOW(),
						paid='.$paid.'
				';
				$orderid=DB::exec($sql);
				if($paid==1){
					$sql='UPDATE {{iusers}} SET balance=balance+'.$authorprice.' WHERE id='.$author.'';
					DB::exec($sql);
					$sql='UPDATE {{iusers}} SET balance=balance+'.$item->amount.' WHERE id='.$iuser.'';
					DB::exec($sql);
				}
			}
		}
	}
	public function request(){
		$sql='SELECT * FROM {{iusers}} WHERE code=\''.$_POST['user_id'].'\'';
		$data=DB::getRow($sql);
		$sql='SELECT SUM(amount) FROM {{requests}} WHERE iuser='.$data['id'].' AND paid=0';
		$amount=DB::getOne($sql);
		if($data['balance']<$_POST['amount']+$amount){
			return 'error';
		}else{
			$sql='
				INSERT INTO {{requests}}
				SET request_id='.$_POST['request_id'].',
					iuser='.$data['id'].',
					amount='.$_POST['amount'].',
					cdate=NOW()
			';
			DB::exec($sql);
		}
	}
	
	public function balance(){
		$sql='SELECT * FROM {{iusers}} WHERE code=\''.$_POST['user_id'].'\'';
		$data=DB::getRow($sql);
		$error='';
		if($data['id']=='')
		$error='Wrong user';
		return array('balance'=>$data['balance'], 'error'=>$error);
	}
	
}
?>