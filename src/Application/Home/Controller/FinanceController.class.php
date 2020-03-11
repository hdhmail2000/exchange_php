<?php
namespace Home\Controller;

class FinanceController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();
		/*$allow_action=array("index","mycz","myczHuikuan","myczFee","myczRes","myczChakan","myczUp","mytx","mytxUp","mytxChexiao","myzr","myzc","upmyzc","mywt","mycj","mytj","mywd","myjp","myzc_user","upmyzc_user","myyj","mydh","upmydh","invite");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}*/
		
		//获取用户信息
		$backstage = M('User')->where(array('id' => userid()))->field('backstage')->find();
		$this->assign('backstage', $backstage['backstage']);
	}

	public function index()
	{
		if (!userid()) {
			redirect('/Login/index.html');
		}
		
		//获取用户信息
		$User = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $User);
		
		$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();
		$CoinList = M('Coin')->where(array('status' => 1))->order('sort asc')->select();
		
		$Market = M('Market')->where(array('status' => 1))->select();
		foreach ($Market as $k => $v) {
			$Market[$v['name']] = $v;
		}

		$cny['zj'] = 0;
		
		foreach ($CoinList as $k => $v) {
			if ($v['name'] == Anchor_CNY) {
				$cny['ky'] = round($UserCoin[$v['name']], 2) * 1;
				$cny['dj'] = round($UserCoin[$v['name'] . 'd'], 2) * 1;
				$cny['zj'] = $cny['zj'] + $cny['ky'] + $cny['dj'];
				
				if ($Market[$v['name'].'_'.Anchor_CNY]['new_price']) {
					$jia = $Market[$v['name'].'_'.Anchor_CNY]['new_price'];
				} else {
					$jia = 0;
				}

				$coinList[$v['name']] = array('name' => $v['name'], 'img' => $v['img'], 'title' => ' ' . strtoupper($v['name']) . ' ', 'xnb' => round($UserCoin[$v['name']], 6) * 1, 'xnbd' => round($UserCoin[$v['name'] . 'd'], 6) * 1, 'xnbz' => round($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd'], 6), 'jia' => $jia * 1, 'zhehe' => round(($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia, 2), 'type' => $v['type'], 'zr_jz' => $v['zr_jz'], 'zc_jz' => $v['zc_jz']);

				$coinList[$v['name']]['xnb'] = sprintf("%.2f", $coinList[$v['name']]['xnb']);
				$coinList[$v['name']]['xnbd'] = sprintf("%.2f", $coinList[$v['name']]['xnbd']);
				$coinList[$v['name']]['xnbz'] = sprintf("%.2f", $coinList[$v['name']]['xnbz']);
				$coinList[$v['name']]['zhehe'] = sprintf("%.2f", $coinList[$v['name']]['xnbz']);
				//$coinList[$v['name']]['zhehe'] = number_format($coinList[$v['name']]['zhehe'],2);//千分位显示

				//开启市场时才显示对应的币
				if(in_array($v['name'],C('coin_on'))){
					$coinList[$v['name']] = array('name' => $v['name'], 'img' => $v['img'], 'title' => $v['title'] . '(' . strtoupper($v['name']) . ')', 'xnb' => round($UserCoin[$v['name']], 6) * 1, 'xnbd' => round($UserCoin[$v['name'] . 'd'], 6) * 1, 'xnbz' => round($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd'], 6), 'jia' => $jia * 1, 'zhehe' => round(($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia, 2), 'type' => $v['type'], 'zr_jz' => $v['zr_jz'], 'zc_jz' => $v['zc_jz']);
				}
			} else {
				if ($Market[$v['name'].'_'.Anchor_CNY]['new_price']) {
					$jia = $Market[$v['name'].'_'.Anchor_CNY]['new_price'];
				} else {
					$jia = 0;
				}

				$coinList[$v['name']] = array('name' => $v['name'], 'img' => $v['img'], 'title' => ' ' . strtoupper($v['name']) . ' ', 'xnb' => round($UserCoin[$v['name']], 6) * 1, 'xnbd' => round($UserCoin[$v['name'] . 'd'], 6) * 1, 'xnbz' => round($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd'], 6), 'jia' => $jia * 1, 'zhehe' => round(($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia, 2), 'type' => $v['type'], 'zr_jz' => $v['zr_jz'], 'zc_jz' => $v['zc_jz']);

				$coinList[$v['name']]['xnb'] = sprintf("%.5f", $coinList[$v['name']]['xnb']);
				$coinList[$v['name']]['xnbd'] = sprintf("%.5f", $coinList[$v['name']]['xnbd']);
				$coinList[$v['name']]['xnbz'] = sprintf("%.5f", $coinList[$v['name']]['xnbz']);
				$coinList[$v['name']]['zhehe'] = sprintf("%.2f", $coinList[$v['name']]['zhehe']);
				//$coinList[$v['name']]['zhehe'] = number_format($coinList[$v['name']]['zhehe'],2);//千分位显示

				//开启市场时才显示对应的币
				if(in_array($v['name'],C('coin_on'))){
					$coinList[$v['name']] = array('name' => $v['name'], 'img' => $v['img'], 'title' => $v['title'] . '(' . strtoupper($v['name']) . ')', 'xnb' => round($UserCoin[$v['name']], 6) * 1, 'xnbd' => round($UserCoin[$v['name'] . 'd'], 6) * 1, 'xnbz' => round($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd'], 6), 'jia' => $jia * 1, 'zhehe' => round(($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia, 2), 'type' => $v['type'], 'zr_jz' => $v['zr_jz'], 'zc_jz' => $v['zc_jz']);
				}
				
				
				$cny['zj'] = round($cny['zj'] + (($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia), 2) * 1;
			}
		}

		$cny['dj'] = sprintf("%.2f", $cny['dj']);
		$cny['ky'] = sprintf("%.2f", $cny['ky']);
		$cny['zj'] = sprintf("%.2f", $cny['zj']);
		//$cny['dj'] = number_format($cny['dj'],2);//千分位显示
		//$cny['ky'] = number_format($cny['ky'],2);//千分位显示
		//$cny['zj'] = number_format($cny['zj'],2);//千分位显示
		
		$this->assign('cny', $cny);
		$this->assign('coinList', $coinList);
		$this->display();
	}
	
	//生成二维码
	public function qrcode($url=NULL){
		Vendor('PHPQRcode.phpqrcode');
		//生成二维码图片
		$object = new \QRcode();
		$url = 'http://'.$_SERVER['HTTP_HOST'].'/Login/register?invit='.$url;//网址或者是文本内容
		$level = 3;
		$size = 4;
		$errorCorrectionLevel = intval($level) ;//容错级别
		$matrixPointSize = intval($size);//生成图片大小
		ob_clean();
		$object->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
	}
	
	//生成海报
	public function haibao($url=NULL,$type=0)
	{
        $imageDefault = array(
            'left'=>430,
            'top'=>633,
            'right'=>0,
            'bottom'=>0,
            'width'=>120,
            'height'=>120,
            'opacity'=>100
        );
        $textDefault = array(
            'text'=>'',
            'left'=>0,
            'top'=>0,
            'fontSize'=>32,       //字号
            'fontColor'=>'255,255,255', //字体颜色
            'angle'=>0,
        );
		
        //海报最底层得背景
		if ($type == 2) {
			$imageDefault = array(
				'left'=>240,
				'top'=>629,
				'right'=>0,
				'bottom'=>0,
				'width'=>120,
				'height'=>120,
				'opacity'=>100
			);
			$background = 'Public/Home/rh_img/haibao2.png';
		} else {
			$background = 'Public/Home/rh_img/haibao.png';
		}
		
        $config['image'][]['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/Home/Finance/qrcode/url/'.$url; //二维码
        $filename = ''; // 保存图片到服务器
		
        getbgqrcode($imageDefault,$textDefault,$background,'',$config);
    }
	
	public function upmyzc_user($coin, $num, $addr, $paypassword, $mobile_verify)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin) || checkstr($num) || checkstr($mobile_verify)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		if (!userid()) {
			$this->error(L('您没有登录请先登录！'));
		}
		
/*		if (!check($mobile_verify, 'd')) {
			$this->error(L('短信验证码格式错误！'));
		}
		if ($mobile_verify != session('myzc_verify')) {
			$this->error(L('短信验证码错误！'));
		}*/

		$num = abs($num);
		if (!check($num, 'currency')) {
			$this->error(L('数量格式错误！'));
		}
		if (!check($addr, 'dw')) {
			$this->error(L('钱包地址格式错误！'));
		}
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (!check($coin, 'n')) {
			$this->error(L('币种格式错误！'));
		}
		if (!C('coin')[$coin]) {
			$this->error(L('币种错误！'));
		}
		
		$addr_user = M('User')->where(array('username' => $addr))->find();
		$from_user = M('User')->where(array('id' => userid()))->find();
		if (!$addr_user) {
			$this->error(L('错误101:转入用户不存在！'));
		}
		if ($addr_user['id'] == $from_user['id']) {
			$this->error(L('不能转给自己！'));
		}

		$Coins = M('Coin')->where(array('name' => $coin))->find();
		if (!$Coins) {
			$this->error(L('币种错误！'));
		}

		$myzc_min = ($Coins['zc_min'] ? abs($Coins['zc_min']) : 0.0001);
		$myzc_max = ($Coins['zc_max'] ? abs($Coins['zc_max']) : 10000000);
		if ($num < $myzc_min) {
			$this->error(L('转出数量超过系统最小限制！'));
		}
		if ($myzc_max < $num) {
			$this->error(L('转出数量超过系统最大限制！'));
		}

		$user = M('User')->where(array('id' => userid()))->find();
		if (md5($paypassword) != $user['paypassword']) {
			$this->error(L('交易密码错误！'));
		}

		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		if ($user_coin[$coin] < $num) {
			$this->error(L('可用余额不足'));
		}
		if ($Coins['zc_fee']!=''||$Coins['zc_fee']!=0) {
			$fee = round(($num / 100) * $Coins['zc_fee'], 8);
		 	$mum = round($num - $fee, 8);
		} else {
			$fee = 0;
			$mum = $num;
		}
		
		$qbdz = $coin . 'b';
		$fee_user = M('UserCoin')->where(array($qbdz => $Coins['zc_user']))->find();

		$mum = $num;
		$peer = M('UserCoin')->where(array('userid' => $addr_user['id']))->find();

		if (!$peer) {
			$this->error(L('错误102:转入用户不存在！'));
		}
		try{
			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_user_coin write ,tw_myzc write ,tw_myzr write ,tw_myzc_fee write ,tw_finance_log write ,tw_user read');

			$rs = array();
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($coin, $num);
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $peer['userid']))->setInc($coin, $num);

			if ($fee) {
				if ($mo->table('tw_user_coin')->where(array($qbdz => $Coins['zc_user']))->find()) {
					$rs[] = $mo->table('tw_user_coin')->where(array($qbdz => $Coins['zc_user']))->setInc($coin, $fee);
					debug(array('msg' => '转出收取手续费' . $fee), 'fee');
				} else {
					$rs[] = $mo->table('tw_user_coin')->add(array($qbdz => $Coins['zc_user'], $coin => $fee));
					debug(array('msg' => '转出收取手续费' . $fee), 'fee');
				}
			}

			$rs[] = $mo->table('tw_myzc')->add(array('userid' => userid(), 'username' => $addr_user['username'], 'coinname' => $coin, 'txid' => md5($addr . $user_coin[$coin . 'b'] . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1, 'to_user' => 1));

			$rs[] = $mo->table('tw_myzr')->add(array('userid' => $peer['userid'], 'username' => $from_user['username'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $addr . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1, 'from_user' => 1));

			if ($fee_user) {
				$rs[] = $mo->table('tw_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coins['zc_user'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $Coins['zc_user'] . time()), 'num' => $num, 'fee' => $fee, 'type' => 1, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
			}

			// 处理资金变更日志-----------------S

			// 获取用户信息
			$user_info = $mo->table('tw_user')->where(array('id' => $peer['userid']))->find();
			$user_zj_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
			$user_peer_coin = $mo->table('tw_user_coin')->where(array('userid' => $peer['userid']))->find();

			// 转出人记录
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => session('userName'), 'adminname' => session('userName'), 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 8, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $user_coin[$coin], 'new_amount' => $user_zj_coin[$coin], 'userid' => session('userId'), 'adminid' => session('userId'),'addip'=>get_client_ip()));

			// 接受人记录
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => session('userName'), 'addtime' => time(), 'plusminus' => 1, 'amount' => $mum, 'optype' => 9, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $peer[$coin], 'new_amount' => $user_peer_coin[$coin], 'userid' => $peer['userid'], 'adminid' => session('userId'),'addip'=>get_client_ip()));

			// 处理资金变更日志-----------------E

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				session('myzc_verify', null);
				$this->success('转账成功！');
			} else {
				throw new \Think\Exception(L('转账失败,错误301！'));
			}
		}catch(\Think\Exception $e){
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			$this->error(L('转账失败,错误302!'));
		}
	}
	
	// 站内转账
	public function myzc_user($coin = NULL,$jf_type =NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index.html');
		}
		
		// 搜索实名认证信息
		$user = D('user')->where(array('userid' => userid()))->find();
		if ($user['kyc_lv'] == 1) {
			if ($user['idstate'] == 2) {
				$this->assign('idcard', 1);
			} else {
				$this->error(L('请实名认证，再进行操作！'), U('User/index'));
			}
		} else if ($user['kyc_lv'] == 2) {
			$this->assign('idcard', 1);
		}
		
		if (C('coin')[$coin]) {
			$coin = trim($coin);
		} else {
			$coin = C('xnb_mr');
		}
		// var_dump($coin);
		$this->assign('xnb', $coin);

		$Coins = M('Coin')->where(array(
			'status' => 1,
			'name'   => array('neq', Anchor_CNY)
		))->select();
		
		foreach ($Coins as $k => $v) {
			$coin_list[$v['name']] = $v;
		}
		// $coin_info = M('Coin')->where(array('name' => $coin))->find();
		if(!$coin_list){
			$this->error(L('币种不存在'));
		}
		$this->assign('coin_list', $coin_list);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[$coin] = round($user_coin[$coin], 6);
		$user_coin[$coin] = sprintf("%.4f", $user_coin[$coin]);
		$this->assign('user_coin', $user_coin);
		$Coins = M('Coin')->where(array('name' => $coin))->find();
		if($jf_type == 'jf_zr'){
			//$where['username'] = session('userName');
			$where['userid'] = userid();
			$where['coinname'] = $coin;
			$where['from_user'] = '1';
			
			$Mobile = M('Myzr');
			$count = $Mobile->where($where)->count();
			$Page = new \Think\Page($count, 10);
			$show = $Page->show();
			$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
			// foreach ($list as $k => $v) {
			// 	// $users_n = M('User')->where(array('id' => $v['userid']))->getField('username');
			// 	// $list[$k]['username'] = $users_n;
			// }
			foreach ($list as $key => $value) {
				$list[$key]['num']=sprintf("%.4f", $value['num']);
				$list[$key]['mum']=sprintf("%.4f", $value['mum']);
				$list[$key]['fee']=sprintf("%.4f", $value['fee']);
			}
		} else {
			$where['userid'] = userid();
			$where['coinname'] = $coin;
			$where['to_user'] = '1';
			
			$Mobile = M('Myzc');
			$count = $Mobile->where($where)->count();
			$Page = new \Think\Page($count, 10);
			$show = $Page->show();
			$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
			foreach ($list as $key => $value) {
				$list[$key]['num']=sprintf("%.4f", $value['num']);
				$list[$key]['mum']=sprintf("%.4f", $value['mum']);
				$list[$key]['fee']=sprintf("%.4f", $value['fee']);
			}
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	public function mydh($coin = NULL,$jf_type =NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index.html');
		}
		
		if (C('coin')[$coin]) {
			$coin = trim($coin);
		} else {
			// $coin = C('xnb_mr');
			$coin=M('Coin')->where(array(
			'change' => array('neq',0),
			'status' => 1,
			'name'   => array('neq', Anchor_CNY)
			))->getField('name');
		}
		// var_dump($coin);
		$this->assign('xnb', $coin);

		$Coins = M('Coin')->where(array(
			'change' => array('neq',0),
			'status' => 1,
			'name'   => array('neq', Anchor_CNY)
		))->select();
		
		foreach ($Coins as $k => $v) {
			$coin_list[$v['name']] = $v;
		}
		$coin_info = M('Coin')->where(array('name' => $coin))->find();

		if($coin_info['change']==1){//固定汇率
			$coin_info['bili']=1/$coin_info['huilv']*$coin_info['amount'];//乘以最小交易数量
			$coin_info['bili2']=1/$coin_info['huilv'];
		}
		if($coin_info['change']==2){//浮动汇率
			//源币种行情比例
			$map1['name']=array('like',$coin.'%');
			$price1=M('market')->where($map1)->getField('new_price');
			//目标币种行情比例
			$map2['name']=array('like',$coin_info['changecoin'].'%');
			$price2=M('market')->where($map2)->getField('new_price');
			// $coin_info['bili']=$price1/$price2*$coin_info['amount'];//乘以最小交易数量
			$coin_info['bili']=round(($price1/$price2)*$coin_info['amount'],4);
			$coin_info['bili2']=round(($price1/$price2),7);
		}
		$this->assign('coin_info', $coin_info);

		if(!$coin_list){
			$this->error(L('币种不存在'));
		}
		// var_dump($coin_list);
		$this->assign('coin_list', $coin_list);

		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[$coin] = round($user_coin[$coin], 6);
		$user_coin[$coin] = sprintf("%.4f", $user_coin[$coin]);
		$this->assign('user_coin', $user_coin);
		$Coins = M('Coin')->where(array('name' => $coin))->find();

		$where['userid'] = userid();
		
		$Mobile = M('mydh');
		$count = $Mobile->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		foreach ($list as $key => $value) {
			$list[$key]['num']=sprintf("%.4f", $value['num']);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	public function upmydh($coin, $num,  $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin) || checkstr($num) ) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (!userid()) {
			// $this->error('您没有登录请先登录！');
			redirect('/Login/index.html');
		}
		
		$num = abs($num);
		if (!check($num, 'currency')) {
			$this->error(L('数量格式错误！'));
		}
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (!check($coin, 'n')) {
			$this->error(L('币种格式错误！'));
		}
		if (!C('coin')[$coin]) {
			$this->error(L('币种错误！'));
		}
		$Coins = M('Coin')->where(array('name' => $coin))->find();
		if (!$Coins) {
			$this->error(L('币种错误！'));
		}
		if($Coins['change']==1){//固定汇率
			$cannum=1/$Coins['huilv']*$num;//乘以最小交易数量
		}
		if($Coins['change']==2){//浮动汇率
			//源币种行情比例
			$map1['name']=array('like',$coin.'%');
			$price1=M('market')->where($map1)->getField('new_price');
			//目标币种行情比例
			$map2['name']=array('like',$Coins['changecoin'].'%');
			$price2=M('market')->where($map2)->getField('new_price');
			$cannum=round(($price1/$price2)*$num,6);
		}

		$user = M('User')->where(array('id' => userid()))->find();
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		// $this->error($user_coin[$coin] );
		$myzc_min = $Coins['amount'] ;//最小可交易数量
		// $myzc_min = ($Coins['zc_min'] ? abs($Coins['zc_min']) : 0.0001);
		$myzc_max = $user_coin[$coin];//账户余额
		// $myzc_max = ($Coins['zc_max'] ? abs($Coins['zc_max']) : 10000000);

		if ($num < $myzc_min) {
			$this->error(L('数量低于系统最小限额！'));
		}
		if ($myzc_max < $num) {
			$this->error(L('您的账户余额不足！'));
		}
		if (md5($paypassword) != $user['paypassword']) {
			$this->error(L('交易密码错误！'));
		}
		if ($user_coin[$coin] < $num) {
			$this->error(L('可用余额不足'));
		}

		try{
			$mo = M();
			$mo->execute('set autocommit=0');
			// $mo->execute('lock tables  tw_user_coin write  , tw_myzc write  , tw_myzr write , tw_myzc_fee write');
			$mo->execute('lock tables  tw_user_coin write  , tw_mydh write  , tw_finance_log write,tw_user read');

			$rs = array();
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($coin, $num);
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setInc($Coins['changecoin'], $cannum);
			$rs[] = $mo->table('tw_mydh')->add(array('userid' => userid(), 'username' => $user['username'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . time()), 'num' => $num, 'amount' => $cannum, 'addtime' => time(), 'dbz' =>$Coins['changecoin']));
			// 处理资金变更日志-----------------S

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				// session('myzc_verify', null);
				$this->success(L('交易成功！'));
			} else {
				throw new \Think\Exception('交易失败,错误301！');
			}
		}catch(\Think\Exception $e){
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			$this->error('交易失败,错误302!');
		}
	}
	
	// 人民币充值 - 弃用
	public function mycz($status = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($status)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		$myczType = M('MyczType')->where(array('status' => 1))->select();

		foreach ($myczType as $k => $v) {
			$myczTypeList[$v['name']] = $v['title'];
		}

		$this->assign('myczTypeList', $myczTypeList);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[Anchor_CNY] = round($user_coin[Anchor_CNY], 2);
		$user_coin['cnyd'] = round($user_coin['cnyd'], 2);
		
		$user_coin['cny'] = sprintf("%.2f", $user_coin['cny']);
		$user_coin['cny'] = number_format($user_coin['cny'],2);//千分位显示
		$user_coin['cnyd'] = sprintf("%.2f", $user_coin['cnyd']);
		$this->assign('user_coin', $user_coin);

		if (($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)) {
			$where['status'] = $status - 1;
		}

		$this->assign('status', $status);
		$where['userid'] = userid();
		$count = M('Mycz')->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = M('Mycz')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['type'] = M('MyczType')->where(array('name' => $v['type']))->getField('title');
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
			$list[$k]['mum'] = sprintf("%.2f", $list[$k]['mum']);
			$list[$k]['num'] = sprintf("%.2f", $list[$k]['num']);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);

		$user_info=M('user')->where(array('id'=>userid()))->find();
		$this->assign('user_info', $user_info);

		$UserBankType = M('UserBankType')->where(array('status' => 1))->order('id desc')->select();
		$this->assign('UserBankType', $UserBankType);

		$this->display();
	}

	public function myczHuikuan($id = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($id)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		if (!check($id, 'd')) {
			$this->error(L('参数错误！'));
		}

		$mycz = M('Mycz')->where(array('id' => $id))->find();
		if (!$mycz) {
			$this->error(L('充值订单不存在！'));
		}
		if ($mycz['userid'] != userid()) {
			$this->error(L('非法操作！'));
		}
		if ($mycz['status'] != 0) {
			$this->error(L('订单已经处理过！'));
		}

		$rs = M('Mycz')->where(array('id' => $id))->save(array('status' => 3));
		if ($rs) {
			$this->success(L('操作成功'));
		} else {
			$this->error(L('操作失败！'));
		}
	}
	
	//获取充值手续费费率
	public function myczFee($cztype)
	{
		// 过滤非法字符----------------S
		if (checkstr($cztype)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}
		$cztype_list=M('mycz_type')->where(array('status'=>1))->select();
		$cztype_arr=array();
		foreach($cztype_list as $val){
			$cztype_arr[]=$val['name'];
		}
		if (!in_array($cztype, $cztype_arr)) {
			$this->error(L('充值类型错误！'));
		}
		$fee=M('mycz_type')->where(array('status'=>1,'name'=>$cztype))->find();
		echo json_encode(array('fee'=>$fee['fee']));
		exit;
	}

	public function myczRes($id)
	{
		// 过滤非法字符----------------S
		if (checkstr($id)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		if (!check($id, 'd')) {
			$this->error(L('参数错误！'));
		}

		$mycz = M('Mycz')->where(array('id' => $id))->find();
		if (!$mycz) {
			$this->error(L('充值订单不存在！'));
		}
		if ($mycz['userid'] != userid()) {
			$this->error(L('非法操作！'));
		}

		echo json_encode(array('status'=>$mycz['status'],'tradeno'=>$mycz['tradeno']));
		exit;
	}

	public function myczChakan($id = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($id)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		if (!check($id, 'd')) {
			$this->error(L('参数错误！'));
		}

		$mycz = M('Mycz')->where(array('id' => $id))->find();
		if (!$mycz) {
			$this->error(L('充值订单不存在！'));
		}
		if ($mycz['userid'] != userid()) {
			$this->error(L('非法操作！'));
		}
		if ($mycz['status'] != 0) {
			$this->error(L('订单已经处理过！'));
		}

		$rs = M('Mycz')->where(array('id' => $id))->save(array('status' => 3));
		if ($rs) {
			$this->success('', array('id' => $id));
		} else {
			$this->error(L('操作失败！'));
		}
	}

	public function myczUp($bankt = '', $type, $num, $mum, $truename, $aliaccount)
	{
		// 过滤非法字符----------------S
		if (checkstr($bankt) || checkstr($type) || checkstr($num) || checkstr($mum) || checkstr($truename) || checkstr($aliaccount)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		if (!check($type, 'n')) {
			$this->error(L('充值方式格式错误！'));
		}
		if (!check($num, 'cny') || !check($mum, 'cny')) {
			$this->error(L('充值金额格式错误！'));
		}
		
		$myczType = M('MyczType')->where(array('name' => $type))->find();
		if (!$myczType) {
			$this->error(L('充值方式不存在！'));
		}
		if ($myczType['status'] != 1) {
			$this->error(L('充值方式没有开通！'));
		}

		$mycz_min = ($myczType['min'] ? $myczType['min'] : 1);
		$mycz_max = ($myczType['max'] ? $myczType['max'] : 100000);
		if ($num < $mycz_min || $mum < $mycz_min) {
			$this->error(L('充值金额不能小于') . $mycz_min . L('元！'));
		}
		if ($mycz_max < $num || $mycz_max < $mum) {
			$this->error(L('充值金额不能大于') . $mycz_max . L('元！'));
		}

		for (; true; ) {
			$tradeno = tradeno();
			if (!M('Mycz')->where(array('tradeno' => $tradeno))->find()) {
				break;
			}
		}
		if ($type=='alipay') {
			if(empty($truename)){
				$this->error(L('请填写您支付宝账号认证的真实姓名！'));
			}
			if(!check($truename, 'chinese')){
				$this->error(L('真实姓名必须是汉字！'));
			}
			if(empty($aliaccount)){
				$this->error(L('请填写支付宝账号！'));
			}
			if (!check($aliaccount, 'mobile')) {
				if (!check($aliaccount, 'email')) {
					$this->error(L('支付宝账号格式错误！'));
				}
			}
		} elseif($type=='bank') {
			if(empty($bankt)){
				$this->error(L('请选择汇款银行！'));
			}
			if(empty($truename)){
				$this->error(L('请填写您银行账号认证的真实姓名！'));
			}
			if(!check($truename, 'chinese')){
				$this->error(L('真实姓名必须是汉字！'));
			}
			if(empty($aliaccount)){
				$this->error(L('请填写银行卡号！'));
			}
			if (!check($aliaccount, 'cny')) {
				$this->error(L('充值账户格式错误！'));
			}
		}

		$mycz = M('Mycz')->add(array('userid' => userid(), 'bank' => $bankt, 'num' => $num, 'mum' => $mum, 'type' => $type, 'tradeno' => $tradeno, 'addtime' => time(), 'status' => 0, 'alipay_truename'=>$truename, 'alipay_account'=>$aliaccount, 'fee'=>$myczType['fee']));

		if ($mycz) {
			if ($type!='weixin') {//微信充值
				$this->success(L('充值订单创建成功！'), array('id' => $mycz));
			} elseif($type='weixin') {
				Vendor("Pay.JSAPI","",".php");
				$wxpay_obj=new \WxPayApi;
				$wxpayorder=new \WxPayUnifiedOrder;
				$wxpayorder->SetOut_trade_no($tradeno);
				$wxpayorder->SetBody('账户充值');
				$wxpayorder->SetTotal_fee($num*100);
				$wxpayorder->SetTrade_type("NATIVE");
				$wxpayorder->SetProduct_id($mycz);
				$wxpayorder->SetNotify_url("http://xnb.huiz.net.cn/Home/Pay/mycz.html");
				$wxpayorder->SetSpbill_create_ip("120.77.221.213");
				$wxpayorder->SetFee_type("CNY");
				$wxpay=$wxpay_obj->unifiedOrder($wxpayorder);
				if (!empty($wxpay['code_url'])) {
					Vendor("RandEx.RandEx","",".php");
					$rand = new \RandEx;
					$imgname = $rand->random(30,'all',0).".png";
					Vendor("PHPQRcode.phpqrcode","",".php");
					$level = 'L';
					$size = 4;
					$url = "./Upload/ewm/wxpay/".$imgname;
					\QRcode::png($wxpay['code_url'], $url, $level, $size);
					M('Mycz')->where(array('id'=>$mycz))->save(array('ewmname'=>$imgname));
					$res=array();
					$res['cztype']="wxpay";
					$res['status']=1;
					$res['id']=$mycz;
					echo json_encode($res);
					exit;
				}
			} else {
				$this->success(L('充值订单创建成功！'), array('id' => $mycz));
			}
		} else {
			$this->error(L('订单创建失败！'));
		}
	}
	
	// 人民币接口提现 - 弃用
	public function mytx($status = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($status)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		$mobile = M('User')->where(array('id' => userid()))->getField('mobile');

		if ($mobile) {
			$mobile = substr_replace($mobile, '****', 3, 4);
		} else {
			$this->error(L('请先认证手机！'));
		}

		$this->assign('mobile', $mobile);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin['cny'] = round($user_coin['cny'], 2);
		$user_coin['cnyd'] = round($user_coin['cnyd'], 2);
		$user_coin['cny'] = sprintf("%.2f", $user_coin['cny']);
		$user_coin['cny'] = number_format($user_coin['cny'],2);//千分位显示
		$user_coin['cnyd'] = sprintf("%.2f", $user_coin['cnyd']);
		$this->assign('user_coin', $user_coin);
		$userBankList = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();

		$truenames = M('User')->where(array('id' => userid()))->getField('truename');
		foreach ($userBankList as $k => $v) {
			$userBankList[$k]['truename'] = $truenames;
		}

		$this->assign('userBankList', $userBankList);
		if (($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)) {
			$where['status'] = $status - 1;
		}

		$this->assign('status', $status);
		$where['userid'] = userid();
		$count = M('Mytx')->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		
		$list = M('Mytx')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['fee'] = (Num($v['fee']) ? Num($v['fee']) : '');
			$list[$k]['fees'] = $list[$k]['fee']/$list[$k]['num']*100;
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
			$list[$k]['names'] = $v['bank'].' '.$v['bankcard'].' '.$v['truename'];
			$list[$k]['num'] = sprintf("%.2f", $list[$k]['num']);
			$list[$k]['fee'] = sprintf("%.2f", $list[$k]['fee']);
			$list[$k]['mum'] = sprintf("%.2f", $list[$k]['mum']);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function mytxUp($mobile_verify, $num, $paypassword, $type)
	{
		// 过滤非法字符----------------S
		if (checkstr($mobile_verify) || checkstr($num) || checkstr($paypassword) || checkstr($type)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		$user_info = M('user')->where(array('id'=>userid()))->find();
		if ($user_info['mobile'] != session('chkmobile')) {
			$this->error(L('手机号码不匹配！'));
		}
		if (!check($mobile_verify, 'd')) {
		 	$this->error(L('短信验证码格式错误！'));
		}

		if (!check($num, 'd')) {
			$this->error(L('提现金额格式错误！'));
		}
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (!check($type, 'd')) {
			$this->error(L('提现方式格式错误！'));
		}
		if ($mobile_verify != session('mytx_verify')) {
		 	$this->error(L('短信验证码错误！'));
		}

		$userCoin = M('UserCoin')->where(array('userid' => userid()))->find();
		if ($userCoin['cny'] < $num) {
			$this->error(L('可用人民币余额不足！'));
		}

		$user = M('User')->where(array('id' => userid()))->find();
		if (md5($paypassword) != $user['paypassword']) {
			$this->error(L('交易密码错误！'));
		}

		$userBank = M('UserBank')->where(array('id' => $type))->find();
		if (!$userBank) {
			$this->error(L('提现地址错误！'));
		}

		$mytx_min = (C('mytx_min') ? C('mytx_min') : 2);
		$mytx_max = (C('mytx_max') ? C('mytx_max') : 50000);
		$mytx_day_max = (C('mytx_day_max') ? C('mytx_day_max') : 200000);
		$start_time = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$end_time = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$today_tx_sum=M('finance_log')->where(array('addtime'=>array('between',"$start_time,$end_time"),'optype'=>5,'userid' => session('userId')))->field('sum(amount) as ttamount')->find();
		
		$today_tx_amount=intval($today_tx_sum['ttamount']);
		if($today_tx_amount+$num>$mytx_day_max){
			$this->error(L('今天累计提现的金额超出最大值！最多还能提出：').($mytx_day_max-$today_tx_amount));
		}
		$mytx_bei = C('mytx_bei');
		$mytx_fee = C('mytx_fee');
		$mytx_fee_min = (C('mytx_fee_min') ? C('mytx_fee_min') : 0);
		if($mytx_min<=$mytx_fee_min){
			$mytx_min=$mytx_fee_min;
		}
		if ($num < $mytx_min) {
			$this->error(L('每次提现金额不能小于') . $mytx_min . L('元！'));
		}
		if ($mytx_max < $num) {
			$this->error(L('每次提现金额不能大于') . $mytx_max . L('元！'));
		}
		if ($mytx_bei) {
			if ($num % $mytx_bei != 0) {
				$this->error(L('每次提现金额必须是') . $mytx_bei . L('的整倍数！'));
			}
		}

		$fee = round(($num / 100) * $mytx_fee, 2);
		if($fee<$mytx_fee_min && $mytx_fee_min>0){
			$fee = $mytx_fee_min;
		}
		$mum = round(($num- $fee), 2);
		try{
			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_mytx write , tw_user_coin write ,tw_finance write,tw_finance_log write');
			$rs = array();
			$finance = $mo->table('tw_finance')->where(array('userid' => userid()))->order('id desc')->find();
			$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec('cny', $num);
			$rs[] = $finance_nameid = $mo->table('tw_mytx')->add(array('userid' => userid(), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'name' => $userBank['name'], 'truename' => $user['truename'], 'bank' => $userBank['bank'], 'bankprov' => $userBank['bankprov'], 'bankcity' => $userBank['bankcity'], 'bankaddr' => $userBank['bankaddr'], 'bankcard' => $userBank['bankcard'], 'addtime' => time(), 'status' => 0));
			$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
			$finance_hash = md5(userid() . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'tp3.net.cn');
			$finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

			if ($finance['mum'] < $finance_num) {
				$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
			} else {
				$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
			}

			$rs[] = $mo->table('tw_finance')->add(array('userid' => userid(), 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $num, 'type' => 2, 'name' => 'mytx', 'nameid' => $finance_nameid, 'remark' => '人民币提现-申请提现', 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));

			// 处理资金变更日志-----------------S
			// 'position' => 1前台-操作位置 optype=5 提现申请-动作类型 'cointype' => 1人民币-资金类型 'plusminus' => 0减少类型
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => session('userName'), 'adminname' => session('userName'), 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 5, 'position' => 1, 'cointype' => 1, 'old_amount' => $finance_num_user_coin['cny'], 'new_amount' => $finance_mum_user_coin['cny'], 'userid' => session('userId'), 'adminid' => session('userId'),'addip'=>get_client_ip()));
			// 处理资金变更日志-----------------E

			if (check_arr($rs)) {
				session('mytx_verify', null);
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success(L('订单创建成功！'));
			} else {
				throw new \Think\Exception('订单创建失败！');
			}
		}catch(\Think\Exception $e){
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			$this->error(L('订单创建失败！'));
		}
	}

	public function mytxChexiao($id)
	{
		// 过滤非法字符----------------S
		if (checkstr($id)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}
		if (!check($id, 'd')) {
			$this->error(L('参数错误！'));
		}

		$mytx = M('Mytx')->where(array('id' => $id))->find();
		if (!$mytx) {
			$this->error(L('提现订单不存在！'));
		}
		if ($mytx['userid'] != userid()) {
			$this->error(L('非法操作！'));
		}
		if ($mytx['status'] != 0) {
			$this->error(L('订单不能撤销！'));
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write,tw_mytx write,tw_finance write,tw_finance_log write');
		$rs = array();
		$finance = $mo->table('tw_finance')->where(array('userid' => $mytx['userid']))->order('id desc')->find();
		$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => $mytx['userid']))->find();
		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $mytx['userid']))->setInc('cny', $mytx['num']);
		$rs[] = $mo->table('tw_mytx')->where(array('id' => $mytx['id']))->setField('status', 2);
		$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => $mytx['userid']))->find();
		$finance_hash = md5($mytx['userid'] . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mytx['num'] . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'tp3.net.cn');
		$finance_num = $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'];

		if ($finance['mum'] < $finance_num) {
			$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
		} else {
			$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
		}

		$rs[] = $mo->table('tw_finance')->add(array('userid' => $mytx['userid'], 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $mytx['num'], 'type' => 1, 'name' => 'mytx', 'nameid' => $mytx['id'], 'remark' => '人民币提现-撤销提现', 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));

		// 处理资金变更日志-----------------S
		$rs[] = $mo->table('tw_finance_log')->add(array('username' => session('userName'), 'adminname' => session('userName'), 'addtime' => time(), 'plusminus' => 1, 'amount' => $mytx['num'], 'optype' => 24, 'position' => 1, 'cointype' => 1, 'old_amount' => $finance_num_user_coin['cny'], 'new_amount' => $finance_mum_user_coin['cny'], 'userid' => session('userId'), 'adminid' => session('userId'),'addip'=>get_client_ip()));
		// 处理资金变更日志-----------------E

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success(L('操作成功！'));
		} else {
			$mo->execute('rollback');
			$this->error(L('操作失败！'));
		}
	}

	// 钱包转入
	public function myzr($coin = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index.html');
		}
		
		//获取用户信息
		$User = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $User);

/*		if (C('coin')[$coin]) {
			$coin = trim($coin);
		} else {
			$coin = C('xnb_mr');
		}*/
		
		$Coins = M('Coin')->where(array(
			'status' => 1,
			'type'   => array('neq', 'ptb'),
			'name'   => array('neq', Anchor_CNY),
		))->select();

		foreach ($Coins as $k => $v) {
			$coin_list[$v['name']] = $v;
		}
		
		if(!($coin)){
			$coin = $Coins[0]['name']; //拿出数组第一个
		}
		
		$this->assign('xnb', $coin);
		$this->assign('coin_list', $coin_list);
		
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[$coin] = round($user_coin[$coin], 6);
		$user_coin[$coin] = sprintf("%.4f", $user_coin[$coin]);
		$user_coin[$coin.'d'] = round($user_coin[$coin.'d'], 6);
		$user_coin[$coin.'d'] = sprintf("%.4f", $user_coin[$coin.'d']);
		
		$this->assign('xnb_c', $user_coin[$coin]);
		$this->assign('xnbd_c', $user_coin[$coin.'d']);
		$this->assign('user_coin', $user_coin);
		
		$Coins = M('Coin')->where(array('name' => $coin))->find();
		$this->assign('zr_jz', $Coins['zr_jz']);
		// var_dump($user_coin[$qbdz]);
		
		$state_coin = 0;
		
		if (!$Coins['zr_jz']) {
			
			$qianbao = L('当前币种禁止转入！');
			$state_coin = 1;
			
		} else {
			
			$qbdz = $coin.'b';
			if (!$user_coin[$qbdz]) {
				if ($Coins['type'] == 'rgb') {
					$qianbao = md5(username() . $coin);
					$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($qbdz => $qianbao));
					if (!$rs) {
						//$this->error(L('生成钱包地址出错！'));
						$qianbao = L('生成钱包地址出错！');
						$state_coin = 1;
					}
				}

				if ($Coins['type'] == 'qbb') {
					$dj_username = $Coins['dj_yh'];
					$dj_password = $Coins['dj_mm'];
					$dj_address = $Coins['dj_zj'];
					$dj_port = $Coins['dj_dk'];
					$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
					$json = $CoinClient->getinfo();
					
					$coin_config = M('Coin')->where(array('name' => $coin))->find();
					if ($coin=='eth' || $coin_config['token_type'] == 1)  //ETH对接,FFF
					{
						$coin_select = M('Coin')->where(array('api_type' => 'eth','token_type' => 1))->select();
						$ethcoin = array('eth'); //ETH对接,FFF
						foreach ($coin_select as $k => $v) {
							$ethcoin[] = $v['name'];
						}
						/*$ethcoin = array('eth','tip','eos','grav','fff');*/

						foreach ($ethcoin as $k => $v) {
							// dump($v);
							if ($user_coin[$v.'b']) {
								$qianbao=$user_coin[$v.'b'];
								break;
							}
						}
						
						if (!$qianbao) {
							$EthClient = EthCommon($dj_address, $dj_port);
							if (!$EthClient) {
								//$this->error('钱包链接失败！');
								$qianbao = L('钱包链接失败！');
								$state_coin = 1;
							} else {
								$qianbao = $CoinClient->personal_newAccount(username());//根据用户名生成账户
								if (!$qianbao || preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$qianbao)) {
									//$this->error('生成钱包地址出错！');
									$qianbao = L('生成钱包地址出错！');
									$state_coin = 1;
								} else {
									foreach ($ethcoin as $k => $v) {
									$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($v.'b' => $qianbao));
								}
							}
						}

					} else {
						foreach ($ethcoin as $k => $v) {
							$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($v.'b' => $qianbao));
						}
					}
						
				} elseif ($coin=='etc') {
						
					$CoinClient = EthCommon($dj_address, $dj_port);
					$qianbao= $CoinClient->personal_newAccount(username());//根据用户名生成账户
					if (!$qianbao || preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$qianbao)) {
						//$this->error(L('生成钱包地址出错！'));
						$qianbao = L('生成钱包地址出错！');
						$state_coin = 1;
					}else{
						$rs = M('UserCoin')->where(array('userid' => userid()))->save(array('etcb' => $qianbao));
						// $rs = M('UserCoin')->where(array('userid' => userid()))->save(array('tatcb' => $qianbao));
					}
				
				} elseif ($coin=='zec') {

					if (!isset($json['version']) || !$json['version']) {
						//$this->error('钱包链接失败！');
						$qianbao = L('钱包链接失败！');
						$state_coin = 1;
					} else {
						$qianbao = $CoinClient->getnewaddress();
						if (!$qianbao || preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$qianbao)) {
							//$this->error(L('生成钱包地址出错！'));
							$qianbao = L('生成钱包地址出错！');
							$state_coin = 1;
						} else {
							$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($qbdz => $qianbao));
						}
					}

				} else {
						
					if (!isset($json['version']) || !$json['version']) {
						//$this->error('钱包链接失败！');
						$qianbao = L('钱包链接失败！');
						$state_coin = 1;
					} else {
						
						$qianbao_addr = $CoinClient->getaddressesbyaccount(username());
						if (!is_array($qianbao_addr)) {
							$qianbao_ad = $CoinClient->getnewaddress(username());
							if (!$qianbao_ad) {
								//$this->error(L('生成钱包地址出错！'));
								$qianbao = L('生成钱包地址出错！');
								$state_coin = 1;
							} else {
								$qianbao = $qianbao_ad;
							}
						} else {
							$qianbao = $qianbao_addr[0];
						}

						if (!$qianbao || preg_match("/[\'.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$qianbao)) {
							//$this->error(L('生成钱包地址出错！'));
							$qianbao = L('生成钱包地址出错！');
							$state_coin = 1;
						}

						$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($qbdz => $qianbao));
						if (!$rs) {
							$this->error(L('钱包地址添加出错！'));
						}
						}
					}
				}

			} else {
				$qianbao = $user_coin[$coin . 'b'];
			}
			// var_dump($qianbao);
		}

		$this->assign('qianbao', $qianbao);
		$where['userid'] = userid();
		$where['coinname'] = $coin;
		$where['from_user'] = '0';
		
		$Mobile = M('Myzr');
		$count = $Mobile->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $key => $value) {
			// $list[$key]['num']= $value['num'];
			// $list[$key]['mum']= $value['mum'];
			$list[$key]['num']=sprintf("%.4f", $value['num']);
			$list[$key]['mum']=sprintf("%.4f", $value['mum']);
			$list[$key]['fee']=sprintf("%.4f", $value['fee']);
		}
		
		$this->assign('state_coin', $state_coin);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	//钱包转出
	public function myzc($coin = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (!userid()) {
			redirect('/Login/index');
		}
		
		// 搜索实名认证信息
		$user = D('user')->where(array('userid' => userid()))->find();
		if ($user['kyc_lv'] == 2) {
			if ($user['idstate'] == 2) {
				$this->assign('idcard', 1);
			} else {
				$this->error(L('请高级实名认证，再进行操作！'), U('User/index'));
			}
		} else {
			$this->error(L('请高级实名认证，再进行操作！'), U('User/index'));
		}
		

/*		if (C('coin')[$coin]) {
			$coin = trim($coin);
		} else {
			$coin = C('xnb_mr');
		}*/
		//$this->assign('xnb', $coin);
		
		$Coins = M('Coin')->where(array(
			'status' => 1,
			'type'   => array('neq', 'ptb'),
			'name'   => array('neq', Anchor_CNY)
		))->select();

		foreach ($Coins as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		if(!($coin)){
			$coin = $Coins[0]['name']; //拿出数组第一个
		}
		
		$Coinx = M('Coin')->where(array('name' => $coin))->find();
		$myzc_min = ($Coinx['zc_min'] ? abs($Coinx['zc_min']) : 1);
		$myzc_max = ($Coinx['zc_max'] ? abs($Coinx['zc_max']) : 10000000);
		$this->assign('myzc_min', $myzc_min);
		$this->assign('myzc_max', $myzc_max);
		$this->assign('Coinx', $Coinx);
		
		$this->assign('xnb', $coin);
		$this->assign('coin_list', $coin_list);
		
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[$coin] = round($user_coin[$coin], 6);
		$user_coin[$coin] = sprintf("%.4f", $user_coin[$coin]);
		$user_coin[$coin.'d'] = round($user_coin[$coin.'d'], 6);
		$user_coin[$coin.'d'] = sprintf("%.4f", $user_coin[$coin.'d']);
		
		$this->assign('xnb_c', $user_coin[$coin]);
		$this->assign('xnbd_c', $user_coin[$coin.'d']);
		$this->assign('user_coin', $user_coin);

		if (!$coin_list[$coin]['zc_jz']) {
			$this->assign('zc_jz', L('当前币种禁止转出！'));
		} else {
			$userQianbaoList = M('UserQianbao')->where(array('userid' => userid(), 'status' => 1, 'coinname' => $coin))->order('id desc')->select();
			$this->assign('userQianbaoList', $userQianbaoList);
			$mobile = M('User')->where(array('id' => userid()))->getField('mobile');

			if ($mobile) {
				$mobile = substr_replace($mobile, '****', 3, 4);
			}
			else {
				redirect(U('Home/User/mobile'));
				exit();
			}

			$this->assign('mobile', $mobile);
		}

		$where['userid'] = userid();
		$where['coinname'] = $coin;
		$where['to_user'] = array('neq','1' );

		$Mobile = M('Myzc');
		$count = $Mobile->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		
		$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $key => $value) {
			$list[$key]['num'] = sprintf("%.4f", $value['num']);
			$list[$key]['mum'] = sprintf("%.4f", $value['mum']);
			$list[$key]['fee'] = sprintf("%.4f", $value['fee']);
		}
		
		$user = M('user')->where(array('id'=>userid()))->find();
		$this->assign('user', $user);
		
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	// 钱包转出处理
	public function upmyzc($coin, $num, $addr, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin) || checkstr($num)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (!userid()) {
			$this->error(L('您没有登录请先登录！'));
		}
		
		$user_info = M('user')->where(array('id'=>userid()))->find();
/*		if ($user_info['mobile'] != session('chkmobile')) {
			$this->error(L('验证码错误！'));
		}
		if (!check($mobile_verify, 'd')) {
			$this->error(L('验证码错误！'));
		}
		if ($mobile_verify != session('myzc_verify')) {
			$this->error(L('验证码错误！'));
		}*/

		if (!check($coin, 'n')) {
			$this->error(L('币种格式错误！'));
		}
		if (!C('coin')[$coin]) {
			$this->error(L('币种错误！'));
		}
		
		$num = abs($num);
		if (!check($num, 'currency')) {
			$this->error(L('数量格式错误！'));
		}
		if (!check($addr, 'dw')) {
			$this->error(L('钱包地址格式错误！'));
		}
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (md5($paypassword) != $user_info['paypassword']) {
			$this->error(L('交易密码错误！'));
		}

		$Coins = M('Coin')->where(array('name' => $coin))->find();
		if (!$Coins) {
			$this->error(L('币种错误！'));
		}

		$myzc_min = ($Coins['zc_min'] ? abs($Coins['zc_min']) : 0.0001);
		$myzc_max = ($Coins['zc_max'] ? abs($Coins['zc_max']) : 10000000);
		if ($num < $myzc_min) {
			$this->error(L('转出数量不能低于').$myzc_min);
		}
		if ($myzc_max < $num) {
			$this->error(L('转出数量最高限制').$myzc_max);
		}

		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		if ($user_coin[$coin] < $num) {
			$this->error(L('可用余额不足'));
		}

		$qbdz = $coin . 'b';
		$fee_user = M('UserCoin')->where(array($qbdz => $Coins['zc_user']))->find();

		if ($fee_user) {
			debug(L('手续费地址: ') . $Coins['zc_user'] . L('存在,有手续费'));
			$fee = round(($num / 100) * $Coins['zc_fee'], 8);
			$mum = round($num - $fee, 8);

			if ($mum < 0) {
				$this->error(L('转出手续费错误！'));
			}
			if ($fee < 0) {
				$this->error(L('转出手续费设置错误！'));
			}
		} else {
			debug(L('手续费地址: ') . $Coins['zc_user'] . L('不存在,无手续费'));
			$fee = 0;
			$mum = $num;
		}

		if ($Coins['type'] == 'rgb') { //认购币
			debug($Coins, L('开始转出'));
			$peer = M('UserCoin')->where(array($qbdz => $addr))->find();

			if (!$peer) {
				$this->error(L('转出地址不存在！'));
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_user_coin write ,tw_myzc write ,tw_myzr write ,tw_myzc_fee write ,tw_finance_log write ,tw_user read');

			$rs = array();
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($coin, $num);
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $peer['userid']))->setInc($coin, $mum);

			if ($fee) {
				if ($mo->table('tw_user_coin')->where(array($qbdz => $Coins['zc_user']))->find()) {
					$rs[] = $mo->table('tw_user_coin')->where(array($qbdz => $Coins['zc_user']))->setInc($coin, $fee);
					debug(array('msg' => L('转出收取手续费') . $fee), 'fee');
				} else {
					$rs[] = $mo->table('tw_user_coin')->add(array($qbdz => $Coins['zc_user'], $coin => $fee));
					debug(array('msg' => L('转出收取手续费') . $fee), 'fee');
				}
			}

			$rs[] = $mo->table('tw_myzc')->add(array('userid' => userid(), 'username' => $addr, 'coinname' => $coin, 'txid' => md5($addr . $user_coin[$coin . 'b'] . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
			$rs[] = $mo->table('tw_myzr')->add(array('userid' => $peer['userid'], 'username' => $user_coin[$coin . 'b'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $addr . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1));

			if ($fee_user) {
				$rs[] = $mo->table('tw_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coins['zc_user'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $Coins['zc_user'] . time()), 'num' => $num, 'fee' => $fee, 'type' => 1, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
			}
			
			// 处理资金变更日志-----------------S
			
			// 转出人记录
			$user_zj_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => $user_info['username'], 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 6, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $user_coin[$coin], 'new_amount' => $user_zj_coin[$coin], 'userid' => userid(), 'adminid' => userid(),'addip'=>get_client_ip()));

			// 获取用户信息
			$user_info = $mo->table('tw_user')->where(array('id' => $peer['userid']))->find();
			$user_peer_coin = $mo->table('tw_user_coin')->where(array('userid' => $peer['userid']))->find();

			// 接受人记录
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => $user_info['username'], 'addtime' => time(), 'plusminus' => 1, 'amount' => $mum, 'optype' => 7, 'position' => 1, 'cointype' => $cointype, 'old_amount' => $peer[$coin], 'new_amount' => $user_peer_coin[$coin], 'userid' => $peer['userid'], 'adminid' => userid(),'addip'=>get_client_ip()));

			// 处理资金变更日志-----------------E

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				session('myzc_verify', null);
				$this->success(L('转账成功！'));
			} else {
				$mo->execute('rollback');
				$this->error(L('转账失败!'));
			}
		}

		if ($Coins['type'] == 'qbb') { //钱包币
			$mo = M();
			if ($mo->table('tw_user_coin')->where(array($qbdz => $addr))->find()) {
				debug($Coin, "开始钱包币站内转出");
				$peer = M('UserCoin')->where(array($qbdz => $addr))->find();
				if (!$peer) {
					$this->error(L('转出地址不存在！'));
				}
				try{
					$mo = M();
					$mo->execute('set autocommit=0');
					// $mo->execute('lock tables  tw_user_coin write  , tw_myzc write  , tw_myzr write , tw_myzc_fee write');
					$mo->execute('lock tables  tw_user_coin write  , tw_myzc write  , tw_myzr write , tw_myzc_fee write,tw_finance_log write,tw_user read');

					$rs = array();
					$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($coin, $num);
					$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $peer['userid']))->setInc($coin, $mum);

					if ($fee) {
						if ($mo->table('tw_user_coin')->where(array($qbdz => $Coins['zc_user']))->find()) {
							$rs[] = $mo->table('tw_user_coin')->where(array($qbdz => $Coins['zc_user']))->setInc($coin, $fee);
						} else {
							$rs[] = $mo->table('tw_user_coin')->add(array($qbdz => $Coins['zc_user'], $coin => $fee));
						}
					}

					$rs[] = $mo->table('tw_myzc')->add(array('userid' => userid(), 'username' => $addr, 'coinname' => $coin, 'txid' => md5($addr . $user_coin[$coin . 'b'] . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
					$rs[] = $mo->table('tw_myzr')->add(array('userid' => $peer['userid'], 'username' => $user_coin[$coin . 'b'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $addr . time()), 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => 1));

					if ($fee_user) {
						$rs[] = $mo->table('tw_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coins['zc_user'], 'coinname' => $coin, 'txid' => md5($user_coin[$coin . 'b'] . $Coins['zc_user'] . time()), 'num' => $num, 'fee' => $fee, 'type' => 1, 'mum' => $mum, 'addtime' => time(), 'status' => 1));
					}
					
					// 处理资金变更日志-----------------S

					// 转出人记录
					$user_zj_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
					$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => $user_info['username'], 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 6, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $user_coin[$coin], 'new_amount' => $user_zj_coin[$coin], 'userid' => userid(), 'adminid' => userid(),'addip'=>get_client_ip()));

					// 获取用户信息
					$user_info = $mo->table('tw_user')->where(array('id' => $peer['userid']))->find();
					$user_peer_coin = $mo->table('tw_user_coin')->where(array('userid' => $peer['userid']))->find();

					// 接受人记录
					$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => $user_info['username'], 'addtime' => time(), 'plusminus' => 1, 'amount' => $mum, 'optype' => 7, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $peer[$coin], 'new_amount' => $user_peer_coin[$coin], 'userid' => $peer['userid'], 'adminid' => userid(),'addip'=>get_client_ip()));

					// 处理资金变更日志-----------------E

					if (check_arr($rs)) {
						$mo->execute('commit');
						$mo->execute('unlock tables');
						session('myzc_verify', null);
						$this->success(L('转账成功！'));
					} else {
						throw new \Think\Exception(L('转账失败!'));
					}
				} catch (\Think\Exception $e){
					$mo->execute('rollback');
					$mo->execute('unlock tables');
					$this->error(L('转账失败'));
				}
			} else {
				debug($Coin, "开始钱包币站外转出");
				$dj_username = $Coins['dj_yh'];
				$dj_password = $Coins['dj_mm'];
				$dj_address = $Coins['dj_zj'];
				$dj_port = $Coins['dj_dk'];
				
				$coin_config = M('Coin')->where(array('name' => $coin))->find();
				if ($coin_config['api_type'] == 'eth'){  //ETH对接,FFF
					$auto_status = 0;
					
/*					$EthClient = EthCommon($dj_address,$dj_port);
					$result = $EthClient->web3_clientVersion();
					if (!$result) {
						$this->error(L('钱包链接失败！'));
						exit;
					}
					
					$auto_status = ($Coins['zc_zd'] && ($num < $Coins['zc_zd']) ? 1 : 0);
					debug(array("zc_zd" => $Coin["zc_zd"], "mum" => $mum, "auto_status" => $auto_status), "是否需要审核");
					$numb = $EthClient->eth_getBalance($dj_username);//获取主账号余额
					$numb = $EthClient->fromWei($numb);//获取主账号余额
					if ($numb < $num) {
						$this->error(L('系统繁忙,请稍后再试')); //钱包余额不足
					}*/
					
				} elseif ($coin=='tatc') {
					$auto_status = 0;

/*					$EthClient = EthCommon($dj_address, $dj_port);
					$result = $EthClient->web3_clientVersion();
					if (!$result) {
						$this->error(L('钱包链接失败！'));
						exit;
					}
					
					$auto_status = ($Coins['zc_zd'] && ($num < $Coins['zc_zd']) ? 1 : 0);
					debug(array("zc_zd" => $Coin["zc_zd"], "mum" => $mum, "auto_status" => $auto_status), "是否需要审核");
					$url = 'https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress=0x09a2FE80C940a39EEE7B69E2B89aF129cf5006bd&address=0x09a2FE80C940a39EEE7B69E2B89aF129cf5006bd&tag=latest&apikey=ERXIYCNF6PP3ZNQAWICHJ6N5W7P212AHZI';
					$fanhui = file_get_contents($url);
					$fanhui= json_decode($fanhui,true);
					if ($fanhui['message']=='OK') {
						$numb = $fanhui['result'];
					}
					if ($numb < $num) {
						$this->error($numb);
						$this->error(L('系统繁忙,请稍后再试')); //钱包余额不足
					}*/
					
				} elseif ($coin_config['api_type'] == 'btc') { //比特系RPC调用
					$auto_status = 0;
					
/*					$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
					$json = $CoinClient->getinfo();
					if (!isset($json['version']) || !$json['version']) {
						$this->error(L('钱包链接失败！'));
					}
					
					$valid_res = $CoinClient->validateaddress($addr);
					if (!$valid_res['isvalid']) {
						$this->error($addr . L('不是一个有效的钱包地址！'));
					}
					
					$auto_status = ($Coins['zc_zd'] && ($num < $Coins['zc_zd']) ? 1 : 0);
					debug(array("zc_zd" => $Coin["zc_zd"], "mum" => $mum, "auto_status" => $auto_status), "是否需要审核");
					if ($json['balance'] < $num) {
						$this->error(L('系统繁忙,请稍后再试')); //钱包余额不足
					}*/

				} else {
					$auto_status = 0; //全部手动审核
					
/*					 if ($json['balance'] < $num) {
					 	$this->error(L('系统繁忙,请稍后再试'));
					 }*/
				}

				try{
					$mo = M();
					$mo->execute('set autocommit=0');
					$mo->execute('lock tables tw_user_coin write ,tw_myzc write ,tw_myzr write ,tw_myzc_fee write ,tw_finance_log write ,tw_user read');

					$rs = array();
					$rs[] = $r = $mo->table('tw_user_coin')->where(array('userid'=>userid()))->setDec($coin,$num);
					$rs[] = $aid = $mo->table('tw_myzc')->add(array('userid'=>userid() ,'username'=>$addr ,'coinname'=>$coin ,'num'=>$num ,'fee'=>$fee ,'mum'=>$mum ,'addtime'=>time() ,'status'=>$auto_status));

					if ($fee && $auto_status) {
						$rs[] = $mo->table('tw_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coins['zc_user'], 'coinname'=>$coin ,'num'=>$num ,'fee'=>$fee ,'mum'=>$mum ,'type'=> 2 ,'addtime'=>time() ,'status'=>1));

						if ($mo->table('tw_user_coin')->where(array($qbdz => $Coins['zc_user']))->find()) {
							$rs[] = $r = $mo->table('tw_user_coin')->where(array($qbdz => $Coins['zc_user']))->setInc($coin, $fee);
							debug(array('res' => $r, 'lastsql' => $mo->table('tw_user_coin')->getLastSql()), '新增费用');
						} else {
							$rs[] = $r = $mo->table('tw_user_coin')->add(array($qbdz => $Coins['zc_user'], $coin => $fee));
						}
					}
					
					// 处理资金变更日志-----------------S
					
					// 转出人记录
					$user_zj_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
					$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => $user_info['username'], 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 6, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $user_coin[$coin], 'new_amount' => $user_zj_coin[$coin], 'userid' => userid(), 'adminid' => userid(),'addip'=>get_client_ip()));

					// 处理资金变更日志-----------------E
							
					//$mum是扣除手续费后的金额
					if (check_arr($rs)) {
						if ($auto_status) {
							if ($coin=='eth' || $coin=='etc') { //以太坊20171110

								$EthClient = EthCommon($dj_address, $dj_port);
								$mum = $EthClient->toWei($mum);
								$sendrs = $EthClient->eth_sendTransaction($dj_username,$addr,$dj_password,$mum);

							} elseif ($coin='tatc') {

								$EthClient = EthCommon($dj_address, $dj_port);
								$mum = dechex ($mum*10000);//代币的位数10000
								$amounthex = sprintf("%064s",$mum);
								$addr2 = explode('0x',  $addr)[1];//接受地址
								$dataraw = '0xa9059cbb000000000000000000000000'.$addr2.$amounthex;//拼接data
								$constadd = '0x09a2fe80c940a39eee7b69e2b89af129cf5006bd';//合约地址
								$sendrs = $EthClient->eth_sendTransactionraw($dj_username,$constadd,$dj_password,$dataraw);
								//转出账户,合约地址,转出账户解锁密码,data值

							} else { //其他币20170922
								$sendrs = $CoinClient->sendtoaddress($addr, floatval($mum));
							}

							if ($sendrs) {
								$res = $mo->table('tw_myzc')->where(array('id'=>$aid))->save(array('txid'=>$sendrs));
								$mo->execute('commit');
								$mo->execute('unlock tables');
							} else{
								throw new \Think\Exception(L('转出失败!1'));
							}
						} else {
							$mo->execute('commit');
							$mo->execute('unlock tables');
							session('myzc_verify', null);
							$this->success(L('转出申请成功,请等待审核！'));
						}
					} else {
						throw new \Think\Exception(L('转出失败!2'));
					}
				}catch(\Think\Exception $e){
					$mo->execute('rollback');
					$mo->execute('unlock tables');
					$this->error(L('转出失败!3'));
				}
				
				if (!$auto_status) {
					$flag = 1;
				} else if ($auto_status && $sendrs) {
					$flag = 1;
					if ($coin=='eth' or $coin=='tatc') { //以太坊20170922
						if(!$sendrs){
							$flag = 0;
						}
					} else {
						$arr = json_decode($sendrs, true);
						if (isset($arr['status']) && ($arr['status'] == 0)) {
							$flag = 0;
						}
					}

				} else {
					$flag = 0;
				}

				if (!$flag) {
					$this->error(L('钱包服务器转出币种失败,请手动转出'));
				} else {
					$this->success(L('转出成功!'));
				}
			}
		}

	}
	
	// 委托管理
	public function mywt($market = NULL, $type = NULL, $status = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($market) || checkstr($type) || checkstr($status)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}
		
		//获取用户信息
		$User = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $User);

		check_server();
		$Coins = M('Coin')->where(array('status' => 1))->select();
		foreach ($Coins as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$Market = M('Market')->where(array('status' => 1))->select();
		foreach ($Market as $k => $v) {
			$v['xnb'] = explode('_', $v['name'])[0];
			$v['rmb'] = explode('_', $v['name'])[1];
			$market_list[$v['name']] = $v;
			// $market_list[$k]['nnn'] = strtoupper($v['xnb'].'/'.$v['rmb']);
		}
		$this->assign('market_list', $market_list);

		if (!$market_list[$market]) {
			$market = $Market[0]['name'];
		}

		$where['market'] = $market;
		if (($type == 1) || ($type == 2)) {
			$where['type'] = $type;
		}
		if (($status == 1) || ($status == 2) || ($status == 3)) {
			$where['status'] = $status - 1;
		}

		$where['userid'] = userid();
		$this->assign('market', $market);
		$this->assign('type', $type);
		$this->assign('status', $status);
		$Mobile = M('Trade');
		$count = $Mobile->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$Page->parameter .= 'type=' . $type . '&status=' . $status . '&market=' . $market . '&';
		$show = $Page->show();
		$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['num'] = $v['num'] * 1;
			$list[$k]['price'] = $v['price'] * 1;
			$list[$k]['deal'] = $v['deal'] * 1;
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function mycj($market = NULL, $type = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($market) || checkstr($type)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}
		
		//获取用户信息
		$User = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $User);

		check_server();
		$Coins = M('Coin')->where(array('status' => 1))->select();

		foreach ($Coins as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$Market = M('Market')->where(array('status' => 1))->select();

		foreach ($Market as $k => $v) {
			$v['xnb'] = explode('_', $v['name'])[0];
			$v['rmb'] = explode('_', $v['name'])[1];
			$market_list[$v['name']] = $v;
		}

		$this->assign('market_list', $market_list);

		if (!$market_list[$market]) {
			$market = $Market[0]['name'];
		}

		if ($type == 1) {
			$where = 'userid=' . userid() . ' && market=\'' . $market . '\'';
		} else if ($type == 2) {
			$where = 'peerid=' . userid() . ' && market=\'' . $market . '\'';
		} else {
			$where = '((userid=' . userid() . ') || (peerid=' . userid() . ')) && market=\'' . $market . '\'';
		}

		$this->assign('market', $market);
		$this->assign('type', $type);
		$this->assign('userid', userid());
		
		$Mobile = M('TradeLog');
		$count = $Mobile->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$Page->parameter .= 'type=' . $type . '&market=' . $market . '&';
		$show = $Page->show();
		$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['num'] = $v['num'] * 1;
			$list[$k]['price'] = $v['price'] * 1;
			$list[$k]['mum'] = $v['mum'] * 1;
			$list[$k]['fee_buy'] = $v['fee_buy'] * 1;
			$list[$k]['fee_sell'] = $v['fee_sell'] * 1;
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	// 推广邀请
	public function invite()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		$user = M('User')->where(array('id' => userid()))->find();
		
		if ($user['kyc_lv'] == 1) {
			if ($user['idstate'] == 2) {
				$this->assign('idcard', 1);
			} else {
				$this->error(L('请实名认证，再进行操作！'), U('User/index'));
			}
		} else if ($user['kyc_lv'] == 2) {
			$this->assign('idcard', 1);
		}

		// check_server();
		
		$useracc= M('User')->where(array('id' => $user['invit_1']))->getField('username');
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

		$this->assign('user', $user);
		$this->assign('useracc', $useracc);
		$this->display();
	}

	public function mywd()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		// 统计
		$tongji['invit_1'] = M('User')->where(array('invit_1' => userid()))->count();
		$tongji['invit_2'] = M('User')->where(array('invit_2' => userid()))->count();
		$this->assign('tongji', $tongji);

		$where['invit_1'] = userid();
		$Model = M('User');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id asc')->field('id,username,mobile,addtime,invit_1')->limit($Page->firstRow . ',' . $Page->listRows)->select(); //直推

		foreach ($list as $k => $v) {
			$list[$k]['invits'] = M('User')->where(array('invit_1' => $v['id']))->order('id asc')->field('id,username,mobile,addtime,invit_1')->select();
			$list[$k]['invitss'] = count($list[$k]['invits']); //累计二代

			foreach ($list[$k]['invits'] as $kk => $vv) {
				$list[$k]['invits'][$kk]['invits'] = M('User')->where(array('invit_1' => $vv['id']))->order('id asc')->field('id,username,mobile,addtime,invit_1')->select();
				$list[$k]['invits'][$kk]['invitss'] = count($list[$k]['invits'][$kk]['invits']); //累计三代
			}
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	// public function myjp()
	// 佣金记录
	public function myyj()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		// 统计
		$tongji['daozhang'] = M('invit')->where(array('id' => userid(),'status'=>1))->sum('fee');
		$tongji['weidaozhang'] = M('invit')->where(array('id' => userid(),'status'=>0))->sum('fee');
		$this->assign('tongji', $tongji);
		
		//获取用户信息
		$User = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $User);

		check_server();
		$where['userid'] = userid();
		$Model = M('Invit');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['invit'] = M('User')->where(array('id' => $v['invit']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	// 我的分红
	public function myfh()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		//获取用户信息
		$User = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $User);
		
		$this->display();
	}
	
	// 持币分红
	public function myfh_cbfh()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		//获取用户信息
		$User = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $User);

		$where['userid'] = userid();
		$Model = M('FenhongLog');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	// 交易挖矿
	public function myfh_jywk()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		//获取用户信息
		$User = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $User);

		$where['userid'] = userid();
		$Model = M('Mining');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
}

?>