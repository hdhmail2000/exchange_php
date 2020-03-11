<?php
/* C2C交易 */
namespace Home\Controller;

class ExchangeController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
	}
	
	public function index()
	{
		if (!userid()) {
			$this->assign('logins', 0);
		} else {
			$this->assign('logins', 1);
		}
		
		// 搜索实名认证信息
		$user = M('user')->where(array('id' => userid()))->find();
		if ($user['kyc_lv'] == 1) {
			if ($user['idstate'] == 2) {
				$this->assign('idcard', 1);
			} else {
				$this->assign('idcard', 0);
			}
		} else if ($user['kyc_lv'] == 2) {
			$this->assign('idcard', 1);
		}
		
		// 搜索银行卡信息
		$user_bank = M('user_bank')->where(array('userid' => userid()))->find();
		if (!($user_bank)) {
			$this->assign('banks', 0);
		} else {
			$this->assign('banks', 1);
		}
		
		// C2C配置信息
		$configs = M('exchange_config')->where(array('id' => 1))->find();
		$this->assign('configs', $configs);
		
		
		$where['userid'] = userid();
		
		$count = M('exchange_order')->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		
		$list = M('exchange_order')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		
		$order_info = M('exchange_order')->where(array('userid' => userid(),'otype' => 1,'status' => 1))->find();
		$order_agent_info = M('exchange_agent')->where(array('id' => $order_info['aid']))->find();
		$this->assign('order_info', $order_info);
		$this->assign('order_agent_info', $order_agent_info);
		
		$UserCoin = M('user_coin')->where(array('userid' => userid()))->find();
		
		$cny['ky'] = round($UserCoin[Anchor_CNY], 2) * 1;
		$cny['ky'] = sprintf("%.2f", $cny['ky']);
		$this->assign('cny', $cny);
		
		$this->display();
	}
	
	// 充值处理
	public function upTrade($price, $num, $otype)
	{
		if (!userid()) {
			$this->error(L('请先登录！'));
		}
		if($otype != 1){
			$this->error(L('非法操作！'));
		}
		
		$coin_type = Anchor_CNY; //充值类型
		
		if (M('exchange_order')->where(array('userid' => userid(),'otype' => 1,'status' => 1))->find()) {
			$this->error(L('您有订单未完成，无法创建！'));
		}
		
		/** 检查设置条件 **/
		$configs = M('exchange_config')->where(array('id' => 1))->find();
		if($configs['mycz_status'] == 0){
			$this->error(L('网络繁忙，请稍后再试！'));	
		}
		
		$price = $configs['mycz_uprice'];
		
		if ($num < $configs['mycz_min']) {
			$this->error(L('每次充值金额不能小于') . $configs['mycz_min'] . L('元！'));
		}
		if ($configs['mycz_max'] < $num) {
			$this->error(L('每次充值金额不能大于') . $configs['mycz_max'] . L('元！'));
		}
		
		/** 生成汇款备注 **/
		for (; true; ) {
			$tradeno = tradeno();
			if (!M('Mycz')->where(array('tradeno' => $tradeno))->find()) {
				break;
			}
		}
		
		/**
		 * 生成订单号
		 * @return  string
		 */
		function build_order_no()
		{
			/* 选择一个随机的方案 */
			mt_srand((double) microtime() * 1000000);
			return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		}
		
		/** 随机匹配代理商 **/
		$data_agent = M('exchange_agent')->where(array('status' => 1))->field("id,aid")->limit(1)->order('rand()')->find();
		
		/** 实际到账金额 **/
		$mum = $num * $price;
		
		$mycz = M('exchange_order')->add(array('otype' => $otype, 'orderid' => build_order_no(), 'userid' => userid(),'remarks' => $tradeno, 'uprice' => $price, 'num' => $num, 'mum' => $mum, 'type' => $coin_type, 'aid' => $data_agent['id'], 'addtime' => time(), 'status' => 1));
		
		if ($mycz) {
			$this->success(L('订单创建成功！'), array('id' => $mycz,'aid' => $data_agent['id']));
		} else {
			$this->error(L('订单创建失败！'));
		}
	}
	
	// 提现处理
	public function upMytx($price, $num, $otype)
	{
		if (!userid()) {
			$this->error(L('请先登录！'));
		}
		if($otype != 2){
			$this->error(L('非法操作！'));
		}
		
		$coin_type = Anchor_CNY; //提现类型
		$coin_type_id = 2;
		
		/** 帐号金额 **/
		$user_coin = M('user_coin')->where(array('userid' => userid()))->find();
		if ($user_coin[$coin_type] < $num) {
			$this->error(C('coin')[$coin_type]['title'] . L('余额不足！'));
		}
		
		/** 检查设置条件 **/
		$configs = M('exchange_config')->where(array('id' => 1))->find();
		
		if($configs['mytx_status'] == 0){
			$this->error(L('网络繁忙，请稍后再试！'));	
		}
		
		$price = $configs['mytx_uprice'];
		
		if ($num < $configs['mytx_min']) {
			$this->error(L('每次提现金额不能小于') . $configs['mytx_min'] . L('元！'));
		}

		if ($configs['mytx_max'] < $num) {
			$this->error(L('每次提现金额不能大于') . $configs['mytx_max'] . L('元！'));
		}
		if ($configs['mytx_bei']) {
			if ($num % $configs['mytx_bei'] != 0) {
				$this->error(L('每次提现金额必须是') . $configs['mytx_bei'] . L('的整倍数！'));
			}
		}
		
		
		/**
		 * 生成订单号
		 * @return  string
		 */
		function build_order_no()
		{
			/* 选择一个随机的方案 */
			mt_srand((double) microtime() * 1000000);
			return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		}

		/** 随机匹配代理商 **/
		$data_agent = M('exchange_agent')->where(array('status' => 1))->field("id,aid")->limit(1)->order('rand()')->find();

		/** 搜索银行卡信息 **/
		$user_bank = M('user_bank')->where(array('userid' => userid()))->find();
		
		/** 实际到账金额 **/
		$mum = $num * $price;

		try{
			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_exchange_order write , tw_user_coin write ,tw_finance write,tw_finance_log write');
			
			$rs = array();
			$finance = $mo->table('tw_finance')->where(array('userid' => $mytx['userid']))->order('id desc')->find();
			
			// 数据未处理时的查询（原数据）
			$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
			// 用户账户数据处理
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($coin_type,$num); // 修改金额
			$rs[] = $finance_nameid = $mo->table('tw_exchange_order')->add(array('otype' => $otype, 'orderid' => build_order_no(), 'userid' => userid(),'remarks' => $tradeno, 'uprice' => $price, 'num' => $num, 'mum' => $mum, 'type'=>$coin_type, 'aid' => $data_agent['id'], 'truename' => $user_bank['truename'], 'bank' => $user_bank['bank'], 'bankprov' => $user_bank['bankprov'], 'bankcity' => $user_bank['bankcity'], 'bankaddr' => $user_bank['bankaddr'], 'bankcard' => $user_bank['bankcard'], 'addtime' => time(), 'status' => 1));
			// 数据处理完的查询（新数据）
			$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
			$finance_hash = md5(userid() . $finance_num_user_coin[$coin_type] . $finance_num_user_coin[$coin_type.'d'] . $mum . $finance_mum_user_coin[$coin_type] . $finance_mum_user_coin[$coin_type.'d'] . MSCODE . 'tp3.net.cn');
			
			$finance_num = $finance_num_user_coin[$coin_type] + $finance_num_user_coin[$coin_type.'d'];
			if ($finance['mum'] < $finance_num) {
				$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
			} else {
				$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
			}
			
			// 处理资金变更日志-----------------S
			
			$rs[] = $mo->table('tw_finance')->add(array('userid' => userid(), 'coinname' => $coin_type, 'num_a' => $finance_num_user_coin[$coin_type], 'num_b' => $finance_num_user_coin[$coin_type.'d'], 'num' => $finance_num_user_coin[$coin_type] + $finance_num_user_coin[$coin_type.'d'], 'fee' => $num, 'type' => 2, 'name' => 'mytx_c2c', 'nameid' => finance_nameid, 'remark' => 'C2C提现'.$coin_type.'-申请提现', 'mum_a' => $finance_mum_user_coin[$coin_type], 'mum_b' => $finance_mum_user_coin[$coin_type.'d'], 'mum' => $finance_mum_user_coin[$coin_type] + $finance_mum_user_coin[$coin_type.'d'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));

			/*
			 * 操作位置（0后台，1前台） position
			 * 动作类型（参考function.php） optype
			 * 资金类型（1人民币） cointype
			 * 类型（0减少，1增加） plusminus
			 * 操作数据 amount
			 */
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => session('userName'), 'adminname' => session('userName'), 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 5, 'position' => 1, 'cointype' => C("coin")[$coin_type]["id"], 'old_amount' =>  $finance_num_user_coin[$coin_type], 'new_amount' => $finance_mum_user_coin[$coin_type], 'userid' => session('userId'), 'adminid' => session('userId'),'addip'=>get_client_ip()));
			
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
	
	// 银行卡管理
	public function bank()
	{
		if (!userid()) {
			$this->error(L('请先登录！'),U('Login/index'));
		}
		
		// 搜索实名认证信息
		$user = D('user')->where(array('id' => userid()))->find();
		if ($user['kyc_lv'] == 1) {
			if ($user['idstate'] == 2) {
				$this->assign('idcard', 1);
			} else {
				$this->error(L('请先通过实名认证，再进行操作！'), U('User/index'));
			}
		} else if ($user['kyc_lv'] == 2) {
			$this->assign('idcard', 1);
		}

		$UserBankType = M('UserBankType')->where(array('status' => 1))->order('id desc')->select();
		$this->assign('UserBankType', $UserBankType);
		
		$truename = M('User')->where(array('id' => userid()))->getField('truename');
		$this->assign('truename', $truename);
		
		$UserBank = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();
		$this->assign('UserBank', $UserBank);
		$this->display();
	}
	
	// 删除银行卡
	public function delbank($id, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($paypassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect(U('Login/index'));
		}

		if (!check($id, 'd')) {
			$this->error(L('参数错误！'));
		}
		
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');
		if (md5($paypassword) != $user_paypassword) {
			$this->error(L('交易密码错误！'));
		}

		if (!M('UserBank')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error(L('非法访问！'));
		} else if (M('UserBank')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success(L('删除成功！'));
		} else {
			$this->error(L('删除失败！'));
		}
	}
	
	// 新增银行卡信息
	public function upbank($bank, $bankprov, $bankcity, $bankaddr, $bankcard, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($bank) || checkstr($bankprov) || checkstr($bankcity) || checkstr($bankaddr) || checkstr($bankcard) || checkstr($paypassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect(U('Login/index'));
		}

/*		if (!check($name, 'a')) {
			$this->error(L('备注名称格式错误！'));
		}*/
		if (!check($bank, 'a')) {
			$this->error(L('开户银行格式错误！'));
		}
		if (!check($bankprov, 'c')) {
			$this->error(L('开户省市格式错误！'));
		}
		if (!check($bankcity, 'c')) {
			$this->error(L('开户省市格式错误！'));
		}
		if (!check($bankaddr, 'a')) {
			$this->error(L('开户行地址格式错误！'));
		}
		if (!check($bankcard, 'd')) {
			$this->error(L('请填写正确的银行卡号！'));
		}
		if (!preg_match('/^\d{13,}$/',$bankcard)) {
			$this->error(L('请填写正确的银行卡号！'));
		}
		
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');
		if (md5($paypassword) != $user_paypassword) {
			$this->error(L('交易密码错误！'));
		}

		if (!M('UserBankType')->where(array('title' => $bank))->find()) {
			$this->error(L('开户银行错误！'));
		}

		$userBank = M('UserBank')->where(array('userid' => userid()))->select();
		foreach ($userBank as $k => $v) {
/*			if ($v['name'] == $name) {
				$this->error(L('请不要使用相同的备注名称！'));
			}*/
			if ($v['bankcard'] == $bankcard) {
				$this->error(L('银行卡号已存在！'));
			}
		}

		if (1 <= count($userBank)) {
			$this->error(L('每个用户最多只能添加1个地址！'));
		}
		
		$truename = M('User')->where(array('id' => userid()))->getField('truename');

		if (M('UserBank')->add(array('userid' => userid(), 'truename' => $truename, 'bank' => $bank, 'bankprov' => $bankprov, 'bankcity' => $bankcity, 'bankaddr' => $bankaddr, 'bankcard' => $bankcard, 'addtime' => time(), 'status' => 1))) {
			$this->success(L('银行添加成功！'));
		} else {
			$this->error(L('银行添加失败！'));
		}
	}
}
?>