<?php

namespace Mobile\Controller;

class LoginController extends MobileController
{
	protected function _initialize()
	{
		parent::_initialize();	$allow_action=array("index","register","upregister","complete","chkUser","chkmobile","submit","loginout","findpwd","findpaypwd","webreg");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}
	}
	
	// 用户协议
	public function webreg()
	{
		$this->display();
	}
	
	public function index()
	{
		$this->display();
	}

	// 登录提交处理
	public function submit($username, $password, $verify = NULL, $ga='')
	{
		// 过滤非法字符----------------S
		if (checkstr($username) || checkstr($password) || checkstr($verify) || checkstr($ga)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (C('login_verify')) {
			if (!check_verify(strtoupper($verify),'1')) {
				$this->error(L('图形验证码错误!'));
			}
		}
/*		if (check($username, 'email')) {
			$user = M('User')->where(array('username' => $username))->find();
			$remark = '通过邮箱登录';
		}*/
		if (!$user) {
			$user = M('User')->where(array('username' => $username))->find();
			$remark = '通过用户名登录';
		}
		if (!$user) {
			$this->error(L('用户不存在！'));
		}
		if (strlen($password) > 16 || strlen($password) < 6) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (!check($password, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (md5($password) != $user['password']){
			$this->error(L('登录密码错误！'));
		}

		// 处理谷歌身份验证器-------------------S
		if($user['ga']){
			$ga_n = new \Common\Ext\GoogleAuthenticator();
			$arr = explode('|', $user['ga']);
			// 存储的信息为谷歌密钥
			$secret = $arr[0];
			// 存储的登录状态为1需要验证，0不需要验证
			$ga_is_login = $arr[1];
			// 判断是否需要验证
			if($ga_is_login){
				if(!$ga){
					$this->error(L('请输入双重验证码！'));
				}
				if(!check($ga,'d')){
					$this->error(L('双重验证码格式错误！'));
				}
				// 判断登录有无验证码
				$aa = $ga_n->verifyCode($secret, $ga, 1);
				if (!$aa){
					$this->error(L('双重身份验证码错误！'));
				}
			}
		}
		// 处理谷歌身份验证器-------------------E
		
		if (isset($user['status'])&&$user['status'] != 1) {
			$this->error(L('你的账号已冻结请联系管理员！'));
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user write , tw_user_log write ');

		$rs = array();
		$rs[] = $mo->table('tw_user')->where(array('id' => $user['id']))->setInc('logins', 1);
		$rs[] = $mo->table('tw_user_log')->add(array('userid' => $user['id'], 'type' => '登录', 'remark' => $remark, 'addtime' => time(), 'addip' => get_client_ip(), 'addr' => get_city_ip(), 'status' => 1));
		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');

			session('userId', $user['id']);
			session('userName', $user['username']);
            session('is_generalize',$user['is_generalize']);
			file_put_contents("login/".$user['id'].".txt", time());
			session('loginTime', time());

			if (!$user['paypassword']) {
				session('regpaypassword', $rs[0]);
				session('reguserId', $user['id']);
			}

			if (!$user['truename']) {
				session('regtruename', $rs[0]);
				session('reguserId', $user['id']);
			}
			$this->success(L('登录成功！'));
		} else {
			$mo->execute('rollback');
			$this->error(L('登录失败！'));
		}
	}
	
	// 注册页面
	public function register()
	{
		if(!empty($_SESSION['reguserId'])){
			$user=M('User')->where(array('id' => $_SESSION['reguserId']))->find();
			if (!empty($user)) {
				header("Location:/Login/complete");
			}
		}
		
		creatToken(); //创建token
        $areas = M('area')->select();
        $this->assign('areas',$areas);
		$this->display();
	}
	
	// 注册提交处理	
	public function upregister($area_id, $mobile, $password, $repassword, $verify, $invit, $mobilecode, $qz, $token)
	{
		// 过滤非法字符----------------S
		if (checkstr($mobile) || checkstr($password) || checkstr($repassword) || checkstr($verify) || checkstr($invit) || checkstr($mobilecode) || checkstr($qz)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		// 昵称
		$enname = $mobile;
		
		// Token令牌验证
		if (!checkToken($token)) {
			$this->error(L('令牌验证错误，请刷新!'));
		}
		
		if (!check_verify(strtoupper($verify),'1')) {
			$this->error(L('图形验证码错误!'));
		}
		if (M('User')->where(array('username' => $mobile))->find()) {
			$this->error(L('用户名已存在'));
		}
		if (M('User')->where(array('enname' => $enname))->find()) {
			$this->error(L('昵称已存在'));
		}
		if (!check($mobile, 'mobile')) {
			$this->error(L('手机格式错误！'));
		}
/*		if (!check($enname, 'username')) {
			$this->error(L('昵称格式错误！'));
		}*/
		if ($mobile != session('chkmobile')) {
			$this->error(L('短信验证码不匹配！')); //手机号不匹配或验证码超时
		}
		if (md5($mobilecode.'mima') != session('mobileregss_verify')) {
			if (C('register_verify')) {
				$this->error(L('短信验证码错误！'));
			}
		}
		
		if (strlen($password) > 16 || strlen($password) < 6) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (!check($password, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if ($password != $repassword) {
			$this->error(L('两次输入的密码不一致'));
		}

		//邀请码
		if (!$invit) {
			$invit = session('invit');
		}
		
		$invituser = M('User')->where(array('invit' => $invit))->find();
		if (!$invituser) {
			$invituser = M('User')->where(array('id' => $invit))->find();
		}
		if (!$invituser) {
			$invituser = M('User')->where(array('username' => $invit))->find();
		}
		if (!$invituser) {
			$invituser = M('User')->where(array('mobile' => $invit))->find();
		}
		if ($invituser) {
			$invit_1 = $invituser['id'];
			$invit_2 = $invituser['invit_1'];
			$invit_3 = $invituser['invit_2'];
		} else {
			$invit_1 = 0;
			$invit_2 = 0;
			$invit_3 = 0;
		}
		
		for (; true; ) {
			$tradeno = tradenoa();
			if (!M('User')->where(array('invit' => $tradeno))->find()) {
				break;
			}
		}

		$last_user_noid = M('user')->field('noid')->order('id desc')->find();
		if(empty($last_user_noid)){
			$user_noid = 12837 + mt_rand(10,99);
		} else {
			$user_noid = $last_user_noid['noid'] + mt_rand(10,99);
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user write , tw_user_coin write , tw_invit write ');
		$rs = array();
		$rs[] = $mo->table('tw_user')->add(array('username' => $mobile, 'mobile'=>$mobile, 'mobiletime'=>time(), 'password' => md5($password), 'invit' => $tradeno, 'tpwdsetting' => 1, 'invit_1' => $invit_1, 'invit_2' => $invit_2, 'invit_3' => $invit_3, 'addip' => get_client_ip(), 'addr' => get_city_ip(), 'addtime' => time(), 'status' => 1 , 'otcuser'=>trim($mobile),'enname'=>$enname,'qz'=>$qz));
		
		$user_coin = array('userid' => $rs[0]);
		
		// 注册赠送币（直接赠送）
		if (C('give_type') == 1) {
			$coin_name = C('xnb_mr_song'); //赠送币种
			$user_coin[$coin_name] = C('xnb_mr_song_num');
			
			// 赠送邀请人邀请奖励
			if(C('song_num_1') > 0 && $invit_1 > 0){
				$coin_num_1 = C('song_num_1');
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $invit_1, 'invit' => $rs[0], 'name' => '一代注册赠送', 'type' => '注册赠送'.strtoupper($coin_name), 'num' => 0, 'mum' => 0, 'fee' => $coin_num_1, 'addtime' => time(), 'status' => 0,'coin'=>strtoupper($coin_name)));
			}
			if(C('song_num_2') > 0 && $invit_2 > 0){
				$coin_num_2 = C('song_num_2');
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $invit_2, 'invit' => $rs[0], 'name' => '二代注册赠送', 'type' => '注册赠送'.strtoupper($coin_name), 'num' => 0, 'mum' => 0, 'fee' => $coin_num_2, 'addtime' => time(), 'status' => 0,'coin'=>strtoupper($coin_name)));
			}
			if(C('song_num_3') > 0 && $invit_3 > 0){
				$coin_num_3 = C('song_num_3');
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $invit_3, 'invit' => $rs[0], 'name' => '三代注册赠送', 'type' => '注册赠送'.strtoupper($coin_name), 'num' => 0, 'mum' => 0, 'fee' => $coin_num_3, 'addtime' => time(), 'status' => 0,'coin'=>strtoupper($coin_name)));
			}
		}
		
		// 创建用户数字资产档案
		$rs[] = $mo->table('tw_user_coin')->add($user_coin);
		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			
			session('mobileregss_verify', null); //初始化动态验证码
			
			session('reguserId', $rs[0]);
			$user = $mo->table('tw_user')->where(array('id'=>$rs[0]))->find();
			session('userNoid',$user['noid']);
			session('is_generalize',$user['is_generalize']);
			$this->success(L('注册成功！'));
		} else {
		    $mo->execute('rollback');
			$this->error(L('注册失败！'));
		}
	}
	
	// 注册成功页面
	public function complete()
	{
		if(!empty($_SESSION['reguserId'])) {
			$user = M('User')->where(array('id' => session('reguserId')))->find();
			session('userId', $user['id']);
			session('userName', $user['username']);
			session('userNoid',$user['noid']);
			// file_put_contents("login/".$user['id'].".txt", time());
			// session('loginTime', time());
			$this->assign('user', $user);
			$this->display();
		} else {
			header("Location:/Login/index");
		}
	}

	public function chkUser($username)
	{
		// 过滤非法字符----------------S
		if (checkstr($username)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!check($username, 'username')) {
			$this->error(L('用户名格式错误！'));
		}
		if (M('User')->where(array('enname' => $username))->find()) {
			$this->error(L('用户名已存在'));
		}

		$this->success('');
	}
	
	public function chkmobile($mobile)
	{
		// 过滤非法字符----------------S
		if (checkstr($mobile)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (M('User')->where(array('username' => $mobile))->find()) {
			$this->error(L('账号已存在'));
		}

		$this->success('');
	}

	public function loginout()
	{
		session(null);
		redirect('/');
	}

	public function findpwd()
	{
		if (IS_POST) {
			$input = I('post.');
			foreach ($input as $k => $v) {
				// 过滤非法字符----------------S
				if (checkstr($v)) {
					$this->error(L('您输入的信息有误！'));
				}
				// 过滤非法字符----------------E
			}

			if (!check_verify(strtoupper($input['verify']),'1')) {
				$this->error(L('图形验证码错误!'));
			}

			if (!check($input['mobile'], 'mobile')) {
				$this->error(L('手机号码格式错误！'));
			}

			if (!check($input['mobile_verify'], 'd')) {
				$this->error(L('短信验证码格式错误！'));
			}

			if ($input['mobile_verify'] != session('findpwd_verify')) {
				$this->error(L('短信验证码错误！'));
			}

			$user = M('User')->where(array('username' => $input['mobile']))->find();
			if (!$user) {
				$this->error(L('用户不存在！'));
			}

			if ($user['mobile'] != $input['mobile']) {
				$this->error(L('手机号码错误！'));
			}
			if ($user['mibao_question'] != $input['mibao_question']) {
				$this->error(L('密保问题错误！'));
			}
			if ($user['mibao_answer'] != $input['mibao_answer']) {
				$this->error(L('密保答案错误！'));
			}
/*			if (!check($input['password'], 'password')) {
				$this->error('新登录密码格式错误！');
			}*/
			if (strlen($input['password']) > 16 || strlen($input['password']) < 6) {
				$this->error(L('密码格式为6~16位，不含特殊符号！'));
			}
			if (!check($input['password'], 'password')) {
				$this->error(L('密码格式为6~16位，不含特殊符号！'));
			}
			if ($input['password'] != $input['repassword']) {
				$this->error(L('两次输入的密码不一致！'));
			}
			if($user['paypassword'] == md5($input['password'])){
				$this->error(L('登录密码不能和交易密码相同！'));
			}
			if($user['password'] == md5($input['password'])){
				$this->error(L('新登录密码与旧登录密码一致！'));
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_user write , tw_user_log write ');
			
			$rs = array();
			$rs[] = $mo->table('tw_user')->where(array('id' => $user['id']))->save(array('password' => md5($input['password'])));
			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success(L('修改成功'));
			} else {
				$mo->execute('rollback');
				$this->error(L('修改失败'));
			}
		} else {
			$this->display();
		}
	}

	public function findpaypwd()
	{
		if (IS_POST) {
			$input = I('post.');
			foreach ($input as $k => $v) {
				// 过滤非法字符----------------S
				if (checkstr($v)) {
					$this->error(L('您输入的信息有误！'));
				}
				// 过滤非法字符----------------E
			}

			if (!check($input['username'], 'mobile')) {
				$this->error(L('用户名格式错误！'));
			}
			if (!check($input['mobile'], 'mobile')) {
				$this->error(L('手机号码格式错误！'));
			}
			if (!check($input['mobile_verify'], 'd')) {
				$this->error(L('短信验证码格式错误！'));
			}
			if ($input['mobile_verify'] != session('findpaypwd_verify')) {
				$this->error(L('短信验证码错误！'));
			}

			$user = M('User')->where(array('username' => $input['username']))->find();
			if (!$user) {
				$this->error(L('用户名不存在！'));
			}
			if ($user['mobile'] != $input['mobile']) {
				$this->error(L('用户名或手机号码错误！'));
			}
			if ($user['mibao_question'] != $input['mibao_question']) {
				$this->error(L('密保问题错误！'));
			}
			if ($user['mibao_answer'] != $input['mibao_answer']) {
				$this->error(L('密保答案错误！'));
			}
/*			if (!check($input['password'], 'password')) {
				$this->error('新交易密码格式错误！');
			}*/
			if (strlen($input['password']) > 16 || strlen($input['password']) < 6) {
				$this->error(L('密码格式为6~16位，不含特殊符号！'));
			}
			if (!check($input['password'], 'password')) {
				$this->error(L('密码格式为6~16位，不含特殊符号！'));
			}
			if ($input['password'] != $input['repassword']) {
				$this->error(L('两次输入的密码不一致！'));
			}
			if($user['password'] == md5($input['password'])){
				$this->error(L('交易密码不能和登录密码相同！'));
			}
			if($user['paypassword'] == md5($input['password'])){
				$this->error(L('新交易密码与旧交易密码一致！'));
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_user write , tw_user_log write ');

			$rs = array();
			$rs[] = $mo->table('tw_user')->where(array('id' => $user['id']))->save(array('paypassword' => md5($input['password'])));
			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success('设置修改成功');
			} else {
				$mo->execute('rollback');
				$this->error('设置修改失败' . $mo->table('tw_user')->getLastSql());
			}
		} else {
			$this->display();
		}
	}
}
?>