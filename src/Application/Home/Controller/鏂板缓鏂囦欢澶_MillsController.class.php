<?php

namespace Home\Controller;

class MillsController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","view","buy","manager","run","getMill","kjfh","kjfx");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}
	}
	
	public function __construct(){
		parent::__construct();
		if (C('shop_login')) {
			if (!userid()) {
				redirect('/Login/index');
			}
		}
	}

	public function index()
	{
		
		$where['status'] = 0;
		if( intval($_GET['type']) != 0 ){
			$where['level'] = intval($_GET['type']);

			// 过滤非法字符----------------S

			if (checkstr($_GET['type'])) {
				$this->error('您输入的信息有误！');
			}

			// 过滤非法字符----------------E


		}


		$shop = M('Mill');
		$count = $shop->where($where)->count();
		$Page = new \Think\Page($count, 20);
	
		$show = $Page->show();
		$list = $shop->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$coin_list = D('Coin')->get_all_name_list();
		$this->assign('coin_list',$coin_list);
		$mills_type_list = C('MILL_TYPE');
		$this->assign('Mills_type_list', $mills_type_list);
		$this->display();
	}

	public function view(){
		$id = intval($_GET['id']);

		// 过滤非法字符----------------S

		if (checkstr($_GET['id'])) {
			$this->error('您输入的信息有误！');
		}

		// 过滤非法字符----------------E


		if( $id == 0 ){
			$this->error('参数错误');
		}

		$info = M('Mill')->find($id);
		if( $info == NULL ){
			$this->error('非法操作');
		}



		$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();
		
		// 用户推荐
		$user = M('User')->where(array('id' => userid()))->find();

		if (!$user['invit']) {
			for (; true; ) {
				$tradeno = tradenoa();

				if (!M('User')->where(array('invit' => $tradeno))->find()) {
					break;
				}
			}

			M('User')->where(array('id' => userid()))->save(array('invit' => $tradeno));
			$user = M('User')->where(array('id' => userid()))->find();
		}
                // 货币分类
		$coin_list = D('Coin')->get_all_name_list();
                //dump($coin_list);
                $this->assign('coin_list',$coin_list);
		$this->assign('user', $user);
		$this->assign('cny',round($UserCoin['cny'],2)*1);
		$this->assign('coin',round($UserCoin[$info['type']],2)*1);
		$this->assign('id',$id);
		$this->assign('info',$info);
		$this->display();
	}

	public function buy(){

		$userid = userid();

		if (!$userid ) {
			$this->error('请先登录！');
		}

		$input = $_POST;

		$user = M('User')->find($userid);

		if (md5($input['pwdtrade']) != $user['paypassword']) {
			$this->error('交易密码错误！');
		}

		$id = intval($input['id']);

		$shop = M('Mill')->find($id);

		if( !$shop ){
			$this->error('商品未开放购买');
		}

		

		$number   = intval($input['num']);

		$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();

		$userBtc  = round($UserCoin[$shop['type']],2)*1;

		$userCny  = round($UserCoin['cny'],2)*1;

		$btcTotal = round($shop['coin_price'],6)*1*$number;

		$cnyTotal = round($shop['cny_price'],2)*1*$number;

		$paymentType = "";

		$totalMoney = 0;

        if( floatval($shop['coin_price']) == 0 ) $input['paytype'] = 1;


		if( intval($input['paytype']) == 1 ){
			if( $cnyTotal > $userCny ){
				$this->error('余额不足，请充值后重试');
			}
			$paymentType = "cny";
			$totalMoney  = $cnyTotal; 
		}else{
			if( $btcTotal > $userBtc ){
				$this->error('余额不足，请充值后重试');
			}
			$paymentType = "pcc";
			$totalMoney  = $btcTotal;
		}

		$shopNumber = M('MillLog')->where(array('userid'=>userid(),'mill_id'=>$id))->sum('num')+0;
		//	echo $shopNumber;exit();
		if( $number+$shopNumber > $shop['limit'] && $shop['limit'] !=0 ){
			$this->error('每人限购'.$shop["limit"].'台！');
		}

		if( $number > $shop['total']-$shop['num'] ){
			$ok = $shop["total"]-$shop["num"];
			$this->error('库存不足，您还可以购买('.$ok.')台');
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables  tw_mill_log  write ,tw_user_coin write,tw_mill write,tw_mill_config write,tw_mill_fenxiao write,tw_user write');
		$rs = array();
		//$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $user['id']))->setDec($paymentType, $totalMoney);
		// widuu
        if( $shop['level'] != 1 ){

        	$invit =  array($user['invit_1'],$user['invit_2'],$user['invit_3']);

			if( $user['invit_3'] != 0 ){
				$invit_user = $mo->table('tw_user')->field('invit_1,invit_2')->find($user['invit_3']);
				array_push($invit, $invit_user['invit_1'],$invit_user['invit_2']);
			}else{
				array_push($invit,0,0);
			}
			
			$mill_dist    = $mo->table('tw_mill_config')->find(1);

			$dist_config  = unserialize($mill_dist['config']);
			
			$dist_paytype = $dist_config['type'];

			//$dist_rate    = explode( ',', $dist_config['mill_'.$shop['level']] ); 

			$mill_type = C('MILL_TYPE');

			foreach ($invit as $k => $v) {
				if( $v != 0 ){

					//$mill_price = $mo->table('tw_mill_log')->where(array('userid'=>$v))->max('mill_price');
					
					$mill_level =  $mo->table('tw_mill_log')->where(array('userid'=>$v))->max('level');
					$dist_rate    = explode( ',', $dist_config['mill_'.$mill_level] );

					if( $mill_level != 0 ){
						$lilv = $dist_rate[$k] / 100;
						//$dist_price = $mill_price * $lilv;
						$dist_price = round($shop['cny_price'],2) * 1 * $lilv;

						if( $dist_price != 0 ){
							$rs[] = $mo->table('tw_user_coin')->where(array('userid'=>$v))->setInc($dist_paytype,$dist_price);
							
							$rs[] = $mo->table('tw_mill_fenxiao')->add(
								array(
									'userid' => $v,
									'username' => $user['username'],
									'money'    => $dist_price,
									'level'	   => $k+1,
									'type'	   => $dist_paytype,
									'coinname' => $mill_type[$shop['level']],
									'addtime'  => time(),
									'total'    => $totalMoney,
									'number'   => $number
								)
							);
						}
					}
				}
			}
        }
        // end widuu
		//$this->error('到这里了'. $paymentType . ($UserCoin[$paymentType]-$totalMoney) . $user['id']);
        if( $totalMoney != 0 ){
		    $rs[] = $mo->table('tw_user_coin')->where(array('userid' => $user['id']))->save(array($paymentType => ($UserCoin[$paymentType]-$totalMoney)));
  		}
		
		$rs[] = $mo->table('tw_mill_log')->add(array('userid' => $user['id'], 'mill_id' => $shop['id'], 'coinname' => $shop['name'], 'level' => $shop['level'],  'num' => intval($input['num']), 'price' =>  $totalMoney, 'type' => $shop['type'],  'addtime' => time(), 'status' => 0,'paytype'=>$paymentType,'profit'=>$shop['profit'],'overtime'=>$shop['day']*86400+time(),'mill_price'=>$shop['cny_price']));
		$rs[] = $mo->table('tw_mill')->where(array('id' => $shop['id']))->setInc('num', $number);
		
		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('购买成功！');
			
		}
		else {
			$mo->execute('rollback');
			$this->error('购买失败！');
		}

	}


	public function manager(){
		

		$shop = M('MillLog');
		$condition['overtime'] = array('elt',time());
		$condition['status']   = array('eq',1);
		$overProduct = $shop->where($condition)->select();

		if( $overProduct ){
			foreach ($overProduct as $key => $value) {
				$id = intval($value['id']);
				// 最后一次收取时间
				if( empty($value['lasttime']) ){
					$lasttime = $value['runtime'];
				}else{
					$lasttime = $value['lasttime'];
				}

				$runtime = $value['overtime'] - $lasttime;

				$total = round($value['profit']*$value['num']/86400,8)*1*$runtime;
				$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();

				$mo = M();
				$mo->execute('set autocommit=0');
				$mo->execute('lock tables  tw_mill_log  write ,tw_user_coin write');
				$rs = array();

				$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->save(array($value['type']=>$UserCoin[$value['type']]+$total));
				$rs[] = $mo->table('tw_mill_log')->where(array('id'=>$id))->save(
						array(
							'total'	   =>$value['total']+$total,
							'lasttime' =>$value['overtime'],
							'status'   => 2
						)
					);
		
				$mo->execute('commit');
				$mo->execute('unlock tables');
				
			}
		}
		$userid = userid();
		$where['userid'] = $userid;
		$count = $shop->where($where)->count();
		$Page = new \Think\Page($count, 20);
	
		$show = $Page->show();
		$list = $shop->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function run(){
		if(IS_AJAX){
			$id = intval($_GET['id']);
			if( $id == 0 ){
				$this->error('参数错误');
			}

			$mill = M('MillLog');

			if( !$mill->find($id) ){
				$this->error('矿机不存在');
			}

			$update['status']  = 1;
			$update['runtime'] = time();

			$result = $mill->where(array('id'=>$id))->save($update);
			if( $result ){
				$this->success('矿机启动成功');
			}else{
				$this->error('矿机启动失败');
			}
		}
	}


	public function getMill(){
		if(IS_AJAX){
			$id = intval($_GET['id']);
			if( $id == 0 ){
				$this->error('参数错误');
			}

			$mill = M('MillLog');

			$shop = $mill->find($id);

			if( !$shop ){
				$this->error('矿机不存在');
			}


			// 最后一次收取时间
			if( empty($shop['lasttime']) ){
				$lasttime = $shop['runtime'];
			}else{
				$lasttime = $shop['lasttime'];
			}

			$runtime = time() - $lasttime;

			$total = round($shop['profit']*$shop['num']/86400,8)*1*$runtime;
			$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables  tw_mill_log  write ,tw_user_coin write');
			$rs = array();

			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->save(array($shop['type']=>$UserCoin[$shop['type']]+$total));
			$rs[] = $mo->table('tw_mill_log')->where(array('id'=>$id))->save(
					array(
						'total'	   =>$shop['total']+$total,
						'lasttime' => time()
					)
				);
			
			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success('收矿成功，本次收矿'.$total.strtoupper($shop['type']).'！');
			}
			else {
				$mo->execute('rollback');
				$this->error('收矿失败，稍后重试！');
			}
		}
	}

	public function kjfh(){
		$this->assign('prompt_text', D('Text')->get_content('finance_myjp'));
		check_server();
		$where['userid'] = userid();
		$Model = M('MillFenhong');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function kjfx(){
		$this->assign('prompt_text', D('Text')->get_content('finance_myjp'));
		check_server();
		$where['userid'] = userid();
		$Model = M('MillFenxiao');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

}
?>