<?php
namespace Home\Controller;

class UserController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("index","login","nameauth","uppassword","paypassword","uppaypassword","uppaypasswordset","ga","mobile","upmobile","alipay","upalipay","tpwdset","tpwdsetting","uptpwdsetting","bank","upbank","delbank","qianbao","upqianbao","delqianbao","goods","upgoods","delgoods","log","gaGoogle","kyc","kyc1","kyc2","kyc1_Handle","kyc2_Handle","kyc_api");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}
	}

	public function index()
	{
		if (!userid()) {
			redirect('/Login/index.html');
		}
		
		//获取用户信息
		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);
		$this->assign('mobiles', substr_replace($user['mobile'], '****', 6, 5));
		
		//登录日志
		$userlog = M('UserLog')->where(array('userid' => userid()))->order('id desc')->limit(10)->select();
		$this->assign('userlog', $userlog);
		
		$is_ga = ($user['ga'] ? 1 : 0);
		$this->assign('is_ga', $is_ga);

		if (!$is_ga) {
			$ga = new \Common\Ext\GoogleAuthenticator();
			$secret = $ga->createSecret();
			session('secret', $secret);
			$this->assign('Asecret', $secret);
			
			//$zhanghu = $user['username'].'-'.$_SERVER['HTTP_HOST'];
			$zhanghu = C('google_prefix') . '-' . $user['username'];
			$this->assign('zhanghu', $zhanghu);
			//$qrCodeUrl = $ga->getQRCodeGoogleUrl($user['username'] . '-' . $_SERVER['HTTP_HOST'], $secret);
			$qrCodeUrl = $ga->getQRCodeGoogleUrl(C('google_prefix') . '-' . $user['username'], $secret);
			$this->assign('qrCodeUrl', $qrCodeUrl);
		} else {
			$arr = explode('|', $user['ga']);
			$this->assign('ga_login', $arr[1]);
			$this->assign('ga_transfer', $arr[2]);
		}
		
		$this->display();
	}


	public function login()
	{
		$link= M('Link')->where(array('status' => 1))->select();
		$this->assign('link', $link);
		$this->display();
	}

	public function nameauth()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		$user = M('User')->where(array('id' => userid()))->find();
		if ($user['idcard']) {
			$user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
		}
		
/*		if (!$user['idcard']) {
			//未设置
			redirect('/Login/register3');
		}*/

		$this->assign('user', $user);
		$this->display();
	}
	
	// KYC身份认证，身份证，护照
	public function kyc()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		$user = M('User')->where(array('id' => userid()))->find();
		if ($user['idcard']) {
			$user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
		}
		
/*		if (!$user['idcard']) {
			//未设置
			redirect('/Login/register3');
		}*/

		$this->assign('user', $user);
		$this->display();
	}
	
	public function kyc1()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		$user = M('User')->where(array('id' => userid()))->find();
		if ($user['kyc_lv'] == 1) {
			if ($user['idstate'] == 1) {
				$this->error(L('正在审核中'), U('User/index'));
			} else if ($user['idstate'] == 2) {
				$this->error(L('非法操作'), U('User/index'));
			}
		} else if ($user['kyc_lv'] == 2) {
			$this->error(L('非法操作'), U('User/index'));
		} 
		
		if ($user['idcard']) {
			$user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
		}

		$this->assign('user', $user);
		$this->display();
	}
	public function kyc1_Handle($idnationality, $idtype, $truename, $idcard)
	{
		// 过滤非法字符----------------S
		if (checkstr($idnationality) || checkstr($idtype) || checkstr($truename) || checkstr($idcard)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}
		
		$user = M('User')->where(array('id' => userid()))->find();
		if ($user['kyc_lv'] == 1) {
			if ($user['idstate'] == 1) {
				redirect('/User/index');
			} else if ($user['idstate'] == 2) {
				redirect('/User/index');
			}
		} else if ($user['kyc_lv'] == 2) {
			redirect('/User/index');
		}
		
		if (!idnationality) {
			$this->error(L('请输入国籍'));
		}

		if ($user['idcard'] != $idcard) {
			if (M('User')->where(array('idcard' => $idcard))->find()) {
				$this->error(L('该身份证号已被注册!'));
			}
		}
		
		if ($idnationality == '中国' || $idnationality == 'China' || $idnationality == 'china') {
/*			if (!check($truename, 'truename')) {
				$this->error('真实姓名格式错误！');
			}*/
			if (!check($idcard, 'idcard')) {
		 		$this->error(L('身份证号格式错误！'));
			}
			$this->kyc_api($idcard,$truename); // 启动api自动认证
		}
		
		if (M('User')->where(array('id' => userid()))->save(array('kyc_lv' => 1, 'idnationality' => $idnationality, 'idtype' => $idtype, 'truename' => $truename, 'idcard' => $idcard, 'idstate' => 1))) {
			$this->success(L('身份验证成功！'));
		} else {
			$this->error(L('身份验证失败！'));
		}
	}
	// API实名认证
    public function kyc_api($cardno,$name)
    {
		// 过滤非法字符----------------S
		if (checkstr($cardn) || checkstr($name)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if(!($cardno) || !($name)){
			$this->error(L('非法操作！'));
		}
		
		if (!userid()) {
			redirect('/Login/index');
		}
		
		function postData($url, $data, $method='GET')
		{
			$host = $url;
			$path = "/lianzhuo/idcard";
			$appcode = C('realpass'); //填写appcode
			$headers = array();
			array_push($headers, "Authorization:APPCODE " . $appcode);
			$bodys = "";
			
			$url = $url.$path.'?cardno='.$data['cardno'].'&name='.$data['name'];

			$curl = curl_init(); // 启动一个CURL会话
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_FAILONERROR, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容

			if (1 == strpos("$". $host, "https://")) {
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			}

			$handles = curl_exec($curl);
			curl_close($curl); // 关闭CURL会话
			$handles = json_decode($handles, true);
			return $handles;
		}
		
		$data = array(
			'cardno' => $cardno, //证件号码
			'name' => $name, //真实姓名
		);
		
		$urls = 'http://idcard.market.alicloudapi.com';
		$handlas = postData($urls,$data);

		if($handlas['resp']['code'] || $handlas['resp']['code'] == 0){
			M('User')->where(array('id' => userid()))->save(array('idapi' => $handlas['resp']['code']));
		}
	}
	
	public function kyc2()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		$user = M('User')->where(array('id' => userid()))->find();
		if ($user['kyc_lv'] == 1) {
			if ($user['idstate'] == 2) {} else {
				$this->error(L('非法操作'), U('User/index'));
			}
		} else if ($user['kyc_lv'] == 2) {
			if ($user['idstate'] == 1) {
				$this->error(L('非法操作'), U('User/index'));
			} else if ($user['idstate'] == 2) {
				$this->error(L('非法操作'), U('User/index'));
			}
		}
		
		$this->assign('user', $user);
		$this->display();
	}
	public function kyc2_Handle($idimg1, $idimg2, $idimg3)
	{
		// 过滤非法字符----------------S
		if (checkstr($idimg1) || checkstr($idimg2) || checkstr($idimg3)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}
		
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}
		
		$user = M('User')->where(array('id' => userid()))->find();
		if ($user['kyc_lv'] == 1) {
			if ($user['idstate'] == 2) {} else {
				redirect('/User/index');
			}
		} else if ($user['kyc_lv'] == 2) {
			if ($user['idstate'] == 1) {
				redirect('/User/index');
			} else if ($user['idstate'] == 2) {
				redirect('/User/index');
			}
		}
		
		
		if(!$idimg1 && !$idimg2 && !$idimg3){
			$this->error(L('请上传证件照后再提交！'));
		}
		
		if (M('User')->where(array('id' => userid()))->save(array('kyc_lv' => 2, 'idimg1' => $idimg1, 'idimg2' => $idimg2, 'idimg3' => $idimg3, 'idstate' => 1))) {
			$this->success(L('证件上传成功！'));
		} else {
			$this->error(L('证件上传失败！'));
		}
	}

	// 修改登录密码：提交处理
	public function uppassword($mobile_verify, $oldpassword, $newpassword, $repassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($mobile_verify) || checkstr($oldpassword) || checkstr($newpassword) || checkstr($repassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		$user_info = M('user')->where(array('id'=>userid()))->find();
		if ($user_info['mobile'] != session('chkmobile')) {
			$this->error(L('短信验证码不匹配！')); //手机号不匹配或验证码超时
		}
		if (!check($mobile_verify, 'd')) {
			$this->error(L('短信验证码格式错误！'));
		}
		if (md5($mobile_verify.'mima') != session('pass_verify')) {
			$this->error(L('短信验证码错误！'));
		}

		if (!check($oldpassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (strlen($newpassword) > 16 || strlen($newpassword) < 6) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (!check($newpassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if ($newpassword != $repassword) {
			$this->error(L('两次输入的密码不一致'));
		}

		$password = M('User')->where(array('id' => userid()))->getField('password');
		$paypasswords = M('User')->where(array('id' => userid()))->getField('paypassword');
		
		if (md5($oldpassword) != $password) {
			$this->error(L('旧登录密码错误！'));
		}
		if (md5($newpassword) == $paypasswords) {
			$this->error(L('登录密码不能和交易密码相同！'));
		}
		if (md5($newpassword) == $password) {
			$this->error(L('新登录密码跟原密码相同，修改失败！'));
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('password' => md5($newpassword)));
		if ($rs) {
			$this->success(L('修改成功'));
		} else {
			$this->error(L('修改失败'));
		}
	}

	// 设置交易密码：提交处理
	public function uppaypasswordset($paypassword, $repaypassword, $mobile_verify)
	{
		// 过滤非法字符----------------S
		if (checkstr($paypassword) || checkstr($repaypassword) || checkstr($mobile_verify)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E
		
		if (!userid()) {
			$this->error(L('请先登录！'));
		}
		
		$user_info = M('user')->where(array('id'=>userid()))->find();
		if($user_info['paypassword']){
			$this->error(L('非法操作'));
		}
		
		if (!check($mobile_verify, 'd')) {
			$this->error(L('短信验证码格式错误！'));
		}
		if ($mobile_verify != session('paypass_verify')) {
			$this->error(L('短信验证码错误！'));
		}
		
		if (strlen($paypassword) > 16 || strlen($paypassword) < 6) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if ($paypassword != $repaypassword) {
			$this->error(L('两次输入的密码不一致'));
		}
		
		if (M('User')->where(array('id' => userid(), 'password' => md5($paypassword)))->find()) {
			$this->error('交易密码不能和登录密码一样！');
		}
		
		$rs = M('User')->where(array('id' => userid()))->save(array('paypassword' => md5($paypassword)));
		if ($rs) {
			$this->success(L('设置交易密码成功！'));
		}
		else {
			$this->error(L('设置交易密码失败！'));
		}
	}
	
	// 修改交易密码：提交处理
	public function uppaypassword($mobile_verify, $oldpaypassword, $newpaypassword, $repaypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($mobile_verify) || checkstr($oldpaypassword) || checkstr($newpaypassword) || checkstr($repaypassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		$user_info = M('user')->where(array('id'=>userid()))->find();
		
/*		if ($user_info['mobile'] != session('chkmobile')) {
			$this->error(L('短信验证码不匹配！')); //手机号不匹配或验证码超时
		}*/
		if (!check($mobile_verify, 'd')) {
			$this->error(L('短信验证码格式错误！'));
		}
		if ($mobile_verify != session('paypass_verify')) {
			$this->error(L('短信验证码错误！'));
		}

		if (!check($oldpaypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (strlen($newpaypassword) > 16 || strlen($newpaypassword) < 6) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if (!check($newpaypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		if ($newpaypassword != $repaypassword) {
			$this->error(L('两次输入的密码不一致'));
		}

		$user = M('User')->where(array('id' => userid()))->find();
		if (md5($oldpaypassword) != $user['paypassword']) {
			$this->error(L('旧交易密码错误！'));
		}
		if (md5($newpaypassword) == $user['paypassword']) {
			$this->error(L('新交易密码跟原交易密码相同，修改失败！'));
		}
		if (md5($newpaypassword) == $user['password']) {
			$this->error(L('交易密码不能和登录密码相同！'));
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('paypassword' => md5($newpaypassword)));
		if ($rs) {
			$this->success(L('修改成功'));
		} else {
			$this->error(L('修改失败'));
		}
	}

	public function ga()
	{
		if (empty($_POST)) {
			if (!userid()) {
				redirect('/Login/index');
			}

			$user = M('User')->where(array('id' => userid()))->find();
			$is_ga = ($user['ga'] ? 1 : 0);
			$this->assign('is_ga', $is_ga);

			if (!$is_ga) {
				$ga = new \Common\Ext\GoogleAuthenticator();
				$secret = $ga->createSecret();
				session('secret', $secret);
				$this->assign('Asecret', $secret);
				
				//$zhanghu = $user['username'].'-'.$_SERVER['HTTP_HOST'];
				$zhanghu = C('google_prefix') . '-' . $user['username'];
				$this->assign('zhanghu', $zhanghu);
				//$qrCodeUrl = $ga->getQRCodeGoogleUrl($user['username'] . '-' . $_SERVER['HTTP_HOST'], $secret);
				$qrCodeUrl = $ga->getQRCodeGoogleUrl(C('google_prefix') . '-' . $user['username'], $secret);
				$this->assign('qrCodeUrl', $qrCodeUrl);
				$this->display();
			} else {
				$arr = explode('|', $user['ga']);
				$this->assign('ga_login', $arr[1]);
				$this->assign('ga_transfer', $arr[2]);
				$this->display();
			}
		} else {

			foreach ($_POST as $k => $v) {
				// 过滤非法字符----------------S
				if (checkstr($v)) {
					$this->error(L('您输入的信息有误！'));
				}
				// 过滤非法字符----------------E
			}

			if (!userid()) {
				$this->error(L('登录已经失效,请重新登录!'));
			}

			$delete = '';
			$gacode = trim(I('ga'));
			$type = trim(I('type'));
			$ga_login = (I('ga_login') == false ? 0 : 1);
			$ga_transfer = (I('ga_transfer') == false ? 0 : 1);

			if (!$gacode) {
				$this->error(L('请输入验证码!'));
			}

			if ($type == 'add') {
				$secret = session('secret');

				if (!$secret) {
					$this->error(L('验证码已经失效,请刷新网页!'));
				}
			} else if (($type == 'updat') || ($type == 'delet')) {
				$user = M('User')->where('id = ' . userid())->find();

				if (!$user['ga']) {
					$this->error(L('还未设置谷歌验证码!'));
				}

				$arr = explode('|', $user['ga']);
				$secret = $arr[0];
				$delete = ($type == 'delet' ? 1 : 0);
			} else {
				$this->error(L('操作未定义'));
			}

			$ga = new \Common\Ext\GoogleAuthenticator();
			if ($ga->verifyCode($secret, $gacode, 1)) {
				$ga_val = ($delete == '' ? $secret . '|' . $ga_login . '|' . $ga_transfer : '');
				M('User')->save(array('id' => userid(), 'ga' => $ga_val));
				$this->success(L('操作成功'));
			} else {
				$this->error(L('验证失败'));
			}
		}
	}
	
	// 谷歌验证器
	public function gaGoogle($ga_verify, $ga_login=NULL, $ga_transfer=NULL, $type)
	{
		// 过滤非法字符----------------S
		if (checkstr($ga_verify)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('登录已经失效,请重新登录!'));
		}
		
		$ga_login = ($ga_login == false ? 0 : 1);
		$ga_transfer = ($ga_transfer == false ? 0 : 1);
		
		$user_info = M('user')->where(array('id'=>userid()))->find();

		if (!$ga_verify) {
			$this->error(L('谷歌验证码错误！'));
		}
		
		if ($type == 'add') {
			$secret = session('secret');

			if (!$secret) {
				$this->error(L('验证码已经失效,请刷新网页!'));
			}
		} else if (($type == 'updat') || ($type == 'delet')) {	
			$user = M('User')->where('id = ' . userid())->find();

			if (!$user['ga']) {
				$this->error(L('还未设置谷歌验证码!'));
			}

			$arr = explode('|', $user['ga']);
			$secret = $arr[0];
			$delete = ($type == 'delet' ? 1 : 0);
		} else {
			$this->error(L('操作未定义'));
		}

		$ga = new \Common\Ext\GoogleAuthenticator();
		if ($ga->verifyCode($secret, $ga_verify, 1)) {
			$ga_val = ($delete == '' ? $secret . '|' . $ga_login . '|' . $ga_transfer : '');
			M('User')->save(array('id' => userid(), 'ga' => $ga_val));
			$this->success(L('操作成功'));
		} else {
			$this->error(L('验证失败'));
		}
	}

	public function mobile()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if ($user['mobile']) {
			$user['mobile'] = substr_replace($user['mobile'], '****', 3, 4);
		}

		$this->assign('user', $user);
		$this->display();
	}

	public function upmobile($mobile, $mobile_verify)
	{
		// 过滤非法字符----------------S
		if (checkstr($mobile) || checkstr($mobile_verify)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('您没有登录请先登录！'));
		}

		if (!check($mobile, 'mobile')) {
			$this->error(L('手机号码格式错误！'));
		}
		if ($mobile != session('chkmobile')) {
			$this->error(L('短信验证码不匹配！')); //手机号不匹配或验证码超时
		}
		if (M('User')->where(array('mobile' => $mobile))->find()) {
			$this->error(L('手机号码已存在！'));
		}
		
		if (!check($mobile_verify, 'd')) {
			$this->error(L('短信验证码格式错误！'));
		}
		if ($mobile_verify != session('mobilebd_verify')) {
			$this->error(L('短信验证码错误！'));
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('mobile' => $mobile, 'mobiletime' => time()));
		if ($rs) {
			$this->success(L('手机认证成功！'));
		} else {
			$this->error(L('手机认证失败！'));
		}
	}

	public function alipay()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		D('User')->check_update();
		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);
		$this->display();
	}

	public function upalipay($alipay = NULL, $paypassword = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($alipay) || checkstr($paypassword)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($alipay, 'mobile')) {
			if (!check($alipay, 'email')) {
				$this->error('支付宝账号格式错误！');
			}
		}

		if (!check($paypassword, 'password')) {
			$this->error('密码格式为6~16位，不含特殊符号！');
		}

		$user = M('User')->where(array('id' => userid()))->find();
		if (md5($paypassword) != $user['paypassword']) {
			$this->error('交易密码错误！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('alipay' => $alipay));
		if ($rs) {
			$this->success('支付宝认证成功！');
		} else {
			$this->error('支付宝认证失败！');
		}
	}

	public function tpwdset()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);
		$this->display();
	}

	public function tpwdsetting()
	{
		if (userid()) {
			$tpwdsetting = M('User')->where(array('id' => userid()))->getField('tpwdsetting');
			exit($tpwdsetting);
		}
	}

	public function uptpwdsetting($paypassword, $tpwdsetting)
	{
		// 过滤非法字符----------------S
		if (checkstr($paypassword) || checkstr($tpwdsetting)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}

		if (($tpwdsetting != 1) && ($tpwdsetting != 2) && ($tpwdsetting != 3)) {
			$this->error(L('选项错误！') . $tpwdsetting);
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');
		if (md5($paypassword) != $user_paypassword) {
			$this->error(L('交易密码错误！'));
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('tpwdsetting' => $tpwdsetting));
		if ($rs) {
			$this->success(L('成功！'));
		} else {
			$this->error(L('失败！'));
		}
	}

	public function bank()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$UserBankType = M('UserBankType')->where(array('status' => 1))->order('id desc')->select();
		$this->assign('UserBankType', $UserBankType);
		$truename = M('User')->where(array('id' => userid()))->getField('truename');
		$this->assign('truename', $truename);
		$UserBank = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();
		$this->assign('UserBank', $UserBank);
		$this->display();
	}

	public function upbank($name, $bank, $bankprov, $bankcity, $bankaddr, $bankcard, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($name) || checkstr($bank) || checkstr($bankprov) || checkstr($bankcity) || checkstr($bankaddr) || checkstr($bankcard) || checkstr($paypassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		if (!check($name, 'a')) {
			$this->error(L('备注名称格式错误！'));
		}
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
		if (!M('UserBankType')->where(array('title' => $bank))->find()) {
			$this->error(L('开户银行错误！'));
		}
		
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}		
		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');
		if (md5($paypassword) != $user_paypassword) {
			$this->error(L('交易密码错误！'));
		}

		$userBank = M('UserBank')->where(array('userid' => userid()))->select();
		foreach ($userBank as $k => $v) {
			if ($v['name'] == $name) {
				$this->error(L('请不要使用相同的备注名称！'));
			}
			if ($v['bankcard'] == $bankcard) {
				$this->error(L('银行卡号已存在！'));
			}
		}

		if (1 <= count($userBank)) {
			$this->error(L('每个用户最多只能添加1个地址！'));
		}

		if (M('UserBank')->add(array('userid' => userid(), 'name' => $name, 'bank' => $bank, 'bankprov' => $bankprov, 'bankcity' => $bankcity, 'bankaddr' => $bankaddr, 'bankcard' => $bankcard, 'addtime' => time(), 'status' => 1))) {
			$this->success(L('银行添加成功！'));
		} else {
			$this->error(L('银行添加失败！'));
		}
	}

	public function delbank($id, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($paypassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}

		if (!check($id, 'd')) {
			$this->error(L('参数错误！'));
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error(L('交易密码错误！'));
		}

		if (!M('UserBank')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error(L('非法访问！'));
		}
		else if (M('UserBank')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success(L('删除成功！'));
		}
		else {
			$this->error(L('删除失败！'));
		}
	}

	public function qianbao($coin = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}
		
		//获取用户信息
		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);

		$Coin = M('Coin')->where(array(
			'status' => 1,
			'type'   => array('neq', 'ptb'),
			'name'   => array('neq', Anchor_CNY)
			))->select();

		if (!$coin) {
			$coin = $Coin[0]['name'];
		}

		$this->assign('xnb', $coin);
		
		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$userQianbaoList = M('UserQianbao')->where(array('userid' => userid(), 'status' => 1, 'coinname' => $coin))->order('id desc')->select();
		$this->assign('userQianbaoList', $userQianbaoList);
		$this->display();
	}

	public function upqianbao($coin, $name=NULL, $addr, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin) || checkstr($name) ||checkstr($addr) || checkstr($paypassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

/*		if (!check($name, 'a')) {
			$this->error(L('备注名称格式错误！'));
		}*/
		if (!check($addr, 'dw')) {
			$this->error(L('钱包地址格式错误！'));
		}
		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}
		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');
		if (md5($paypassword) != $user_paypassword) {
			$this->error(L('交易密码错误！'));
		}
		if (!M('Coin')->where(array('name' => $coin))->find()) {
			$this->error(L('品种错误！'));
		}

		$userQianbao = M('UserQianbao')->where(array('userid' => userid(), 'coinname' => $coin))->select();
		foreach ($userQianbao as $k => $v) {
/*			if ($v['name'] == $name) {
				$this->error(L('请不要使用相同的钱包备注！'));
			}*/
			if ($v['addr'] == $addr) {
				$this->error(L('钱包地址已存在！'));
			}
		}

		if (3 <= count($userQianbao)) {
			$this->error(L('每个人最多只能添加3个地址！'));
		}

		if (M('UserQianbao')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'coinname' => $coin, 'addtime' => time(), 'status' => 1))) {
			$this->success(L('添加成功！'));
		} else {
			$this->error(L('添加失败！'));
		}
	}

	public function delqianbao($id, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($paypassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		if (!check($paypassword, 'password')) {
			$this->error(L('密码格式为6~16位，不含特殊符号！'));
		}

		if (!check($id, 'd')) {
			$this->error(L('参数错误！'));
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');
		if (md5($paypassword) != $user_paypassword) {
			$this->error(L('交易密码错误！'));
		}

		if (!M('UserQianbao')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error(L('非法访问！'));
		} else if (M('UserQianbao')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success(L('删除成功！'));
		} else {
			$this->error(L('删除失败！'));
		}
	}

	public function goods()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$userGoodsList = M('UserGoods')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();

		foreach ($userGoodsList as $k => $v) {
			$userGoodsList[$k]['mobile'] = substr_replace($v['mobile'], '****', 3, 4);
			$userGoodsList[$k]['idcard'] = substr_replace($v['idcard'], '********', 6, 8);
		}

		$this->assign('userGoodsList', $userGoodsList);
		$this->display();
	}

	public function upgoods($name, $truename, $idcard, $mobile, $addr, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($name) || checkstr($truename) || checkstr($idcard) || checkstr($mobile) || checkstr($paypassword)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		if (!check($name, 'a')) {
			$this->error('备注名称格式错误！');
		}
		if (!check($truename, 'truename')) {
			$this->error('联系姓名格式错误！');
		}
		if (!check($idcard, 'idcard')) {
			$this->error('身份证号格式错误！');
		}
		if (!check($mobile, 'mobile')) {
			$this->error('联系电话格式错误！');
		}
		if (!check($addr, 'a')) {
			$this->error('联系地址格式错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');
		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		$userGoods = M('UserGoods')->where(array('userid' => userid()))->select();
		foreach ($userGoods as $k => $v) {
			if ($v['name'] == $name) {
				$this->error('请不要使用相同的地址标识！');
			}
		}

		if (10 <= count($userGoods)) {
			$this->error('每个人最多只能添加10个地址！');
		}

		if (M('UserGoods')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'idcard' => $idcard, 'truename' => $truename, 'mobile' => $mobile, 'addtime' => time(), 'status' => 1))) {
			$this->success('添加成功！');
		} else {
			$this->error('添加失败！');
		}
	}

	public function delgoods($id, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($paypassword)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		if (!check($paypassword, 'password')) {
			$this->error('密码格式为6~16位，不含特殊符号！');
		}

		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');
		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('UserGoods')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error('非法访问！');
		} else if (M('UserGoods')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success('删除成功！');
		} else {
			$this->error('删除失败！');
		}
	}

	public function log()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		
		//获取用户信息
		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);

		$where['status'] = array('egt', 0);
		$where['userid'] = userid();
		$Model = M('UserLog');
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