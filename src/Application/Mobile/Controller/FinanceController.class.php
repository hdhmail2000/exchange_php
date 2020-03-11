<?php
namespace Mobile\Controller;

class FinanceController extends MobileController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("index","mycz","myczHuikuan","myczFee","myczRes","myczChakan","myczUp","mytx","mytxlog","mytxUp","mytxChexiao","myzr","myzr_coin_list","myzr_log","myzc","myzcadd","myzc_coin_list","myuser_coin_list","myuseradd","upmyzc","mywt","mywt_coin_list","mycj","mycj_coin_list","mytj","mywd","myjp","myczlog","mycz_type_ajax","myzc_user","upmyzc_user","getShort" ,"coin_show");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}
	}
	
	public function index()
	{
		if (!userid()) {
			redirect('/Login/index.html');
		}

		$CoinList = M('Coin')->where(array('status' => 1))->select();
		$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();
		$Market = M('Market')->where(array('status' => 1))->select();

		foreach ($Market as $k => $v) {
			$Market[$v['name']] = $v;
		}

		$cny['zj'] = 0;

		foreach ($CoinList as $k => $v) {
			if ($v['name'] == 'cny') {
				$cny['ky'] = round($UserCoin[$v['name']], 2) * 1;
				$cny['dj'] = round($UserCoin[$v['name'] . 'd'], 2) * 1;
				$cny['zj'] = $cny['zj'] + $cny['ky'] + $cny['dj'];
			} else {
				if ($Market[$v['name'].'_'.Anchor_CNY]['new_price']) {
					$jia = $Market[$v['name'].'_'.Anchor_CNY]['new_price'];
				} else {
					$jia = 1;
				}

				$coinList[$v['name']] = array(
					'id' => $v['id'],
					'name' => $v['name'], 
					'img' => $v['img'], 
					'title' => $v['title'], 
					'xnb' => round($UserCoin[$v['name']], 6) * 1, 
					'xnbd' => round($UserCoin[$v['name'] . 'd'], 6) * 1, 
					'xnbz' => round($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd'], 6), 
					'jia' => $jia * 1, 
					'zhehe' => round(($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia, 2)
				);
				
				$coinList[$v['name']]['zhehe'] = sprintf("%.4f", $coinList[$v['name']]['zhehe']);
				$cny['zj'] = round($cny['zj'] + (($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia), 2) * 1;
				$coinList[$v['name']]['xnb'] = sprintf("%.4f", $coinList[$v['name']]['xnb']);
				$coinList[$v['name']]['xnbd'] = sprintf("%.4f", $coinList[$v['name']]['xnbd']);
				$coinList[$v['name']]['xnbz'] = sprintf("%.4f", $coinList[$v['name']]['xnbz']);
				$coinList[$v['name']]['zhehe'] = sprintf("%.2f", $coinList[$v['name']]['zhehe']);
				//$coinList[$v['name']]['zhehe'] = number_format($coinList[$v['name']]['zhehe'],2);//千分位显示
				
				$coinList[$v['name']]['token_type'] = $v['token_type'];
			}
		}

		$cny['dj'] = sprintf("%.2f", $cny['dj']);
		$cny['ky'] = sprintf("%.2f", $cny['ky']);
		$cny['zj'] = sprintf("%.2f", $cny['zj']);
		$cny['dj'] = number_format($cny['dj'],2);//千分位显示
		$cny['ky'] = number_format($cny['ky'],2);//千分位显示
		//$cny['zj'] = number_format($cny['zj'],2);//千分位显示

		$this->assign('cny', $cny);
		$this->assign('coinList', $coinList);
		$this->display();
	}
	
	public function coin_show($coin)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (!userid()) {
			redirect('/Login/index.html');
		}
		
		$Coin = M('Coin')->where(array('id' => $coin,'status' => 1))->find();
		$CoinInfo = $Coin;
		$CoinInfo['name'] = strtoupper($Coin['name']);
		
		$this->assign('coin_info', $CoinInfo);
		

		$Market = M('Market')->where(array('status' => 1))->select();
		foreach ($Market as $k => $v) {
			$Market[$v['name']] = $v;
		}
		if ($Market[$Coin['name'].'_'.Anchor_CNY]['new_price']) {
			$jia = $Market[$Coin['name'].'_'.Anchor_CNY]['new_price'];
		} else {
			$jia = 1;
		}
		
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin['xnb'] = sprintf("%.6f", $user_coin[$Coin['name']]);
		$user_coin['xnbd'] = sprintf("%.6f", $user_coin[$Coin['name'].'d']);
		$user_coin['zhehe'] = round(($user_coin[$Coin['name']] + $user_coin[$Coin['name'] . 'd']) * $jia, 2);
		$this->assign('user_coin', $user_coin);
		
		$this->assign('coin', $Coin['name']);
		$this->display();
	}
	
	public function CURLQueryString($url)
	{
        //设置附加HTTP头
        $addHead=array("Content-type: application/json");
        //初始化curl
        $curl_obj=curl_init();
        //设置网址
        curl_setopt($curl_obj,CURLOPT_URL,$url);
        //附加Head内容
        curl_setopt($curl_obj,CURLOPT_HTTPHEADER,$addHead);
        //是否输出返回头信息
        curl_setopt($curl_obj,CURLOPT_HEADER,0);
        //将curl_exec的结果返回
        curl_setopt($curl_obj,CURLOPT_RETURNTRANSFER,1);
        //设置超时时间
        curl_setopt($curl_obj,CURLOPT_TIMEOUT,8);
        //执行
        $result=curl_exec($curl_obj);
        //关闭curl回话
        curl_close($curl_obj);
        return $result;
    }
    //处理返回结果
    public function doWithResult($result,$field){
        $result=json_decode($result,true);
        return isset($result[0][$field])?$result[0][$field]:'';
    }
	 //获取短链接
    public function getShort($url){
        $url='http://api.t.sina.com.cn/short_url/shorten.json?source=31641035&url_long='.$url;
        $result=$this->CURLQueryString($url);
        return $this->doWithResult($result,'url_short');
    }
	
	public function mytj()
	{
		if (!userid()) {
			redirect('/#login');
		}
		$user = M('User')->where(array('id' => userid()))->find();
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
		$user_url=$this->getShort("http://".$_SERVER['HTTP_HOST']."/Login/register/invit/".$user['invit']);
		$this->assign('user', $user);
		$this->assign('user_url', $user_url);
		$this->assign('useracc', $useracc);
		$this->display();
	}

	public function mywd()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$where['invit_1'] = userid();
		$Model = M('User');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id asc')->field('id,username,mobile,addtime,invit_1')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['invits'] = M('User')->where(array('invit_1' => $v['id']))->order('id asc')->field('id,username,mobile,addtime,invit_1')->select();
			$list[$k]['invitss'] = count($list[$k]['invits']);

			foreach ($list[$k]['invits'] as $kk => $vv) {
				$list[$k]['invits'][$kk]['invits'] = M('User')->where(array('invit_1' => $vv['id']))->order('id asc')->field('id,username,mobile,addtime,invit_1')->select();
				$list[$k]['invits'][$kk]['invitss'] = count($list[$k]['invits'][$kk]['invits']);
			}
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	public function myjp()
	{
		if (!userid()) {
			redirect('/#login');
		}
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
	
	// 充值记录
	public function myczlog()
	{
		if (!userid()) {
			redirect("/Login/index");
		}

		$where = array();

		$where['userid'] = userid();
		$count = M('Mycz')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Mycz')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['type'] = M('MyczType')->where(array('name' => $v['type']))->getField('title');
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
			$list[$k]['num'] = sprintf("%.2f", $list[$k]['num']);
			$list[$k]['mum'] = sprintf("%.2f", $list[$k]['mum']);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}


	// 充值方式ajax处理
	public function mycz_type_ajax($pp)
	{
		// 过滤非法字符----------------S
		if (checkstr($pp)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if($pp){
			$my = M('MyczType')->select();
			if($my){
				foreach ($my as $k => $v) {
					if($v['name'] == $pp){
						if($v['min']){
							echo $v['min'];die();
						}else{
							echo 0;die();
						}
					}
				}
				echo 0;
			}else{
				echo 0;
			}
		}else{
			echo 0;
		}
	}

	// 转入虚拟币记录
	public function myzr_log($coin = null)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		$Coin = M('Coin')->where(array('name' => $coin))->find();
		$this->assign('coin_info', $Coin);

		$where['userid'] = userid();
		$where['coinname'] = $coin;
		$where['from_user'] = '0';
		$Moble = M('Myzr');
		$count = $Moble->where($where)->count();
		$Page = new \Think\Page1($count, 10);
		$show = $Page->show();
		$list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $key => $value) {
			$list[$key]['num']=sprintf("%.4f", $value['num']);
			$list[$key]['mum']=sprintf("%.4f", $value['mum']);
			$list[$key]['fee']=sprintf("%.4f", $value['fee']);
		}
		$this->assign('list', $list);
		$this->assign('page', $show);

		$this->display();

	}

	public function mycj_coin_list()
	{
		// // 获取币种列表信息------S
		// $map = array();
		// $map['name'] = array('NEQ','cny');
		// $map['status'] = 1;
		// $coin_list = M('Coin')->where($map)->order('id desc')->select();

		$coin_list=M('market')->where('status=1')->select();
		foreach ($coin_list as $key => $v) {
			$xnb = explode('_', $v['name'])[0];
			$rmb = explode('_', $v['name'])[1];
			$coinxx=M('coin')->where(array('name'=>$xnb))->find();
			$coin_list[$key]['img']=$coinxx['img'];
			$coin_list[$key]['title']=strtoupper($xnb).'/'.strtoupper($rmb);
			# code...
		}

		$this->assign('coin_list', $coin_list);
		// 获取币种列表信息------E

		$this->display();
	}

	/* 币种列表页 */
	public function myzr_coin_list()
	{
		// 获取币种列表信息------S
		$map = array();
		$map['name'] = array('NEQ','cny');
		$map['status'] = 1;

		$coin_list = M('Coin')->where($map)->order('id desc')->select();

		$this->assign('coin_list', $coin_list);
		// 获取币种列表信息------E

		$this->display();
	}

	public function myzc_coin_list()
	{
		// 获取币种列表信息------S
		$map = array();
		$map['name'] = array('NEQ','cny');
		$map['status'] = 1;

		$coin_list = M('Coin')->where($map)->order('id desc')->select();

		$this->assign('coin_list', $coin_list);
		// 获取币种列表信息------E

		$this->display();
	}


	public function myuser_coin_list()
	{
		// 获取币种列表信息------S
		$map = array();
		$map['name'] = array('NEQ','cny');
		$map['status'] = 1;

		$coin_list = M('Coin')->where($map)->order('id desc')->select();

		$this->assign('coin_list', $coin_list);
		// 获取币种列表信息------E

		$this->display();
	}


	/*
		转出虚拟币操作
	*/
	public function myzcadd($coin = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect("/Login/index.html");
		}

		if (C('coin')[$coin]) {
			$coin = trim($coin);
		} else {
			$coin = C('xnb_mr');
		}

		$this->assign('xnb', $coin);
		$Coin = M('Coin')->where(array(
			'status' => 1,
			'name'   => array('neq', 'cny')
			))->select();

		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[$coin] = round($user_coin[$coin], 6);
		$this->assign('user_coin', $user_coin);

		if (!$coin_list[$coin]['zc_jz']) {
			$this->assign('zc_jz', L('当前币种禁止转出！'));
		} else {

			$userQianbaoList = M('UserQianbao')->where(array('userid' => userid(), 'status' => 1, 'coinname' => $coin))->order('id desc')->select();
			$this->assign('userQianbaoList', $userQianbaoList);
			$moble = M('User')->where(array('id' => userid()))->getField('mobile');

			if ($moble) {
				$moble = substr_replace($moble, '****', 3, 4);
			} else {

				redirect(U('/User/mobile'));
				exit();
			}

			$this->assign('moble', $moble);
		}



		$where['userid'] = userid();
		$where['coinname'] = $coin;
		$Moble = M('Myzc');
		$count = $Moble->where($where)->count();
		$Page = new \Think\Page1($count, 10);
		$show = $Page->show();

		$list = $Moble->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		// 处理总计================================
		$lis = $Moble->where($where)->select();
		$fees = 0;
		$nums = 0;
		$mums = 0;
		foreach ($lis as $k => $v) {
			$fees += $v['fee'];
			$nums += $v['num'];
			$mums += $v['mum'];
		}
		$this->assign('fees', $fees);
		$this->assign('nums', $nums);
		$this->assign('mums', $mums);
		// 处理总计================================
		$user=M('user')->where(array('id'=>userid()))->find();
		$this->assign('user', $user);
		$this->assign('coin', $coin);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	/* 币种列表页 */
	public function mywt_coin_list()
	{
		// 获取币种列表信息------S
		$map = array();
		// $map['name'] = array('NEQ','cny');
		$map['status'] = 1;

		$coin_list=M('market')->where('status=1')->select();
		foreach ($coin_list as $key => $v) {
			$xnb = explode('_', $v['name'])[0];
			$rmb = explode('_', $v['name'])[1];
			$coinxx=M('coin')->where(array('name'=>$xnb))->find();
			$coin_list[$key]['img']=$coinxx['img'];
			$coin_list[$key]['title']=strtoupper($xnb).'/'.strtoupper($rmb);
			# code...
		}

		$this->assign('coin_list', $coin_list);
		// 获取币种列表信息------E

		$this->display();
	}

	public function mycz($status = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($status)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect("/Login/index.html");
		}
		
		$config=M('config')->where(array('id'=>1))->find();
		$this->assign('config',$config);
		$myczType = M('MyczType')->where(array('status' => 1))->select();

		foreach ($myczType as $k => $v) {
			$myczTypeList[$v['name']] = $v['title'];
		}

		$this->assign('myczTypeList', $myczTypeList);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin['cny'] = round($user_coin['cny'], 2);
		$user_coin['cnyd'] = round($user_coin['cnyd'], 2);
		$user_coin['cny'] = sprintf("%.2f", $user_coin['cny']);
		$user_coin['cnyd'] = sprintf("%.2f", $user_coin['cnyd']);
		$this->assign('user_coin', $user_coin);

		if (($status == 1) || ($status == 2) || ($status == 3) || ($status == 4)) {
			$where['status'] = $status - 1;
		}

		$this->assign('status', $status);
		$where['userid'] = userid();
		$count = M('Mycz')->where($where)->count();
		$Page = new \Think\Page1($count, 15);
		$show = $Page->show();
		$list = M('Mycz')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['type'] = M('MyczType')->where(array('name' => $v['type']))->getField('title');
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
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

		$mycz_min = ($myczType['min'] ? $myczType['min'] : 100);
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
			if ($type!='weixin') {
				$this->success(L('充值订单创建成功！'), array('id' => $mycz));
			} elseif($type=='weixin') {
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
			$this->error(L('提现订单创建失败！'));
		}
	}

	public function mytx($status = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($status)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect("/Login/index.html");
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
		$Page = new \Think\Page1($count, 15);
		$show = $Page->show();
		
		$list = M('Mytx')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['fee'] = (Num($v['fee']) ? Num($v['fee']) : '');
			$list[$k]['fees'] = $list[$k]['fee']/$list[$k]['num']*100;
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
			$list[$k]['names'] = $v['bank'].' '.$v['bankcard'].' '.$v['truename'];
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	// 提现记录
	public function mytxlog($status = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($status)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect("/Login/index.html");
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
		$Page = new \Think\Page1($count, 15);
		$show = $Page->show();
		
		$list = M('Mytx')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$list[$k]['num'] = (Num($v['num']) ? Num($v['num']) : '');
			$list[$k]['fee'] = (Num($v['fee']) ? Num($v['fee']) : '');
			$list[$k]['fees'] = $list[$k]['fee']/$list[$k]['num']*100;
			$list[$k]['mum'] = (Num($v['mum']) ? Num($v['mum']) : '');
			$list[$k]['names'] = $v['bank'].'　'.$v['bankcard'].'　'.$v['truename'];
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
			$this->error('今天累计提现的金额超出最大值！最多还能提出：'.($mytx_day_max-$today_tx_amount));
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
				$this->success('提现订单创建成功！');
			} else {
				throw new \Think\Exception('提现订单创建失败！');
			}
		} catch(\Think\Exception $e) {
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			$this->error(L('提现订单创建失败！'));
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
					if ($coin=='eth' || $coin=='eos' || $coin_config['token_type'] == 1)  //ETH对接,FFF
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

	public function myzrold($coin = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index.html');
		}

		// 获取币种信息
		$coin_info = M('Coin')->where(array('name' => $coin))->find();
		if(!$coin_info){
			$this->error(L('币种不存在'));
		}

		$this->assign('coin_info', $coin_info);

		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_coin[$coin] = sprintf("%.4f", $user_coin[$coin]);
		$this->assign('user_coin', $user_coin);


		if (!$coin_info['zr_jz']) {
			$qianbao = '当前币种禁止转入！';
		} else {
			$qbdz = $coin . 'b';

			if (!$user_coin[$qbdz]) {
				if ($coin_info['type'] == 'rgb') {
					$qianbao = md5(username() . $coin);
					$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($qbdz => $qianbao));

					if (!$rs) {
						$this->error(L('生成钱包地址出错！'));
					}
				}

				if ($coin_info['type'] == 'qbb') {
					$dj_username = $coin_info['dj_yh'];
					$dj_password = $coin_info['dj_mm'];
					$dj_address = $coin_info['dj_zj'];
					$dj_port = $coin_info['dj_dk'];
					$CoinClient = CoinClient($dj_username, $dj_password, $dj_address, $dj_port, 5, array(), 1);
					$json = $CoinClient->getinfo();

					if (!isset($json['version']) || !$json['version']) {
						$this->error(L('钱包链接失败！'));
					}

					$qianbao_addr = $CoinClient->getaddressesbyaccount(username());

					if (!is_array($qianbao_addr)) {
						$qianbao_ad = $CoinClient->getnewaddress(username());

						if (!$qianbao_ad) {
							$this->error(L('生成钱包地址出错！'));
						} else {
							$qianbao = $qianbao_ad;
						}
					} else {
						$qianbao = $qianbao_addr[0];
					}

					if (!$qianbao) {
						$this->error(L('生成钱包地址出错！'));
					}

					$rs = M('UserCoin')->where(array('userid' => userid()))->save(array($qbdz => $qianbao));
					if (!$rs) {
						$this->error(L('钱包地址添加出错！'));
					}
				}
			} else {
				$qianbao = $user_coin[$coin . 'b'];
			}
		}

		$this->assign('qianbao', $qianbao);
		$this->display();
	}

	public function myzc($coin = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index.html');
		}

		$coin_info = M('Coin')->where(array('name' => $coin))->find();
		if(!$coin_info){
			$this->error(L('币种不存在'));
		}

		$this->assign('coin_info', $coin_info);

		$where = array();

		$where['userid'] = userid();
		$where['coinname'] = $coin;
		$where['to_user'] = array('neq','1' );
		$Mobile = M('Myzc');
		$count = $Mobile->where($where)->count();
		$Page = new \Think\Page1($count, 10);
		$show = $Page->show();
		$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $key => $value) {
			$list[$key]['num']=sprintf("%.4f", $value['num']);
			$list[$key]['mum']=sprintf("%.4f", $value['mum']);
			$list[$key]['fee']=sprintf("%.4f", $value['fee']);
		}
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

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

		$coin_info = M('Coin')->where(array('name' => $coin))->find();
		if (!$coin_info) {
			$this->error(L('币种不存在'));
		}

		$this->assign('coin_info', $coin_info);

		if ($jf_type == 'jf_zr') {
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

	public function myuseradd($coin = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index.html');
		}
		$coin_info = M('Coin')->where(array('name' => $coin))->find();
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$user_info = M('User')->where(array('id' => userid()))->find();
		if ($user_info['mobile']) {
			$user_info['mobile'] = substr_replace($user_info['mobile'], '****', 3, 4);
		}

		$this->assign('user_info', $user_info);
		$this->assign('user_coin', $user_coin);
		$this->assign('coin_info', $coin_info);
		$this->assign('coin', $coin);

		$this->display();
	}

	public function upmyzc($coin, $num, $addr, $paypassword, $mobile_verify)
	{
		// 过滤非法字符----------------S
		if (checkstr($coin) || checkstr($num) || checkstr($mobile_verify)) {
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

		$num = abs($num);

		if (!check($num, 'currency')) {
			$this->error(L('数量格式错误！'));
		}
		if ($coin=='tatc') {
			if ($num <100) {
				$this->error(L('数量不能低于100！'));
			}
		} else {
			if ($num <0.1) {
				$this->error(L('数量不能低于0.1！'));
			}
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
			// $mo->execute('lock tables  tw_user_coin write  , tw_myzc write  , tw_myzr write , tw_myzc_fee write');
			$mo->execute('lock tables  tw_user_coin write  , tw_myzc write  , tw_myzr write , tw_myzc_fee write,tw_finance_log write,tw_user read');

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
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user['username'], 'adminname' => $user['username'], 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 6, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $user_coin[$coin], 'new_amount' => $user_zj_coin[$coin], 'userid' => userid(), 'adminid' => userid(),'addip'=>get_client_ip()));

			// 获取用户信息
			$user_info = $mo->table('tw_user')->where(array('id' => $peer['userid']))->find();
			$user_peer_coin = $mo->table('tw_user_coin')->where(array('userid' => $peer['userid']))->find();

			// 接受人记录
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => $user['username'], 'addtime' => time(), 'plusminus' => 1, 'amount' => $mum, 'optype' => 7, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $peer[$coin], 'new_amount' => $user_peer_coin[$coin], 'userid' => $peer['userid'], 'adminid' => userid(),'addip'=>get_client_ip()));

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
					$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user['username'], 'adminname' => $user['username'], 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 6, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $user_coin[$coin], 'new_amount' => $user_zj_coin[$coin], 'userid' => userid(), 'adminid' => userid(),'addip'=>get_client_ip()));

					// 获取用户信息
					$user_info = $mo->table('tw_user')->where(array('id' => $peer['userid']))->find();
					$user_peer_coin = $mo->table('tw_user_coin')->where(array('userid' => $peer['userid']))->find();

					// 接受人记录
					$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => $user['username'], 'addtime' => time(), 'plusminus' => 1, 'amount' => $mum, 'optype' => 7, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $peer[$coin], 'new_amount' => $user_peer_coin[$coin], 'userid' => $peer['userid'], 'adminid' => userid(),'addip'=>get_client_ip()));

					// 处理资金变更日志-----------------E

					if (check_arr($rs)) {
						$mo->execute('commit');
						$mo->execute('unlock tables');
						session('myzc_verify', null);
						$this->success(L('转账成功！'));
					} else {
						throw new \Think\Exception(L('转账失败!'));
					}
				}catch(\Think\Exception $e){
					$mo->execute('rollback');
					$mo->execute('unlock tables');
					$this->error(L('转账失败!'));
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
					$rs[] = $r = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($coin, $num);
					$rs[] = $aid = $mo->table('tw_myzc')->add(array('userid' => userid(), 'username' => $addr, 'coinname' => $coin, 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'addtime' => time(), 'status' => $auto_status));
										
					if ($fee && $auto_status) {
						$rs[] = $mo->table('tw_myzc_fee')->add(array('userid' => $fee_user['userid'], 'username' => $Coins['zc_user'], 'coinname' => $coin, 'num' => $num, 'fee' => $fee, 'mum' => $mum, 'type' => 2, 'addtime' => time(), 'status' => 1));

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
					$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user['username'], 'adminname' => $user['username'], 'addtime' => time(), 'plusminus' => 0, 'amount' => $num, 'optype' => 6, 'position' => 1, 'cointype' => $Coins['id'], 'old_amount' => $user_coin[$coin], 'new_amount' => $user_zj_coin[$coin], 'userid' => userid(), 'adminid' => userid(),'addip'=>get_client_ip()));

					// 处理资金变更日志-----------------E
					
					//$mum是扣除手续费后的金额
					if (check_arr($rs)) {
						if ($auto_status) {
							if ($coin=='eth' || $coin=='etc') {//以太坊20171110
/*								$mo->execute('commit');
								$mo->execute('unlock tables');
								session('myzc_verify', null);
								$this->success(L('转出申请成功,请等待审核！'));*/

								$EthClient = EthCommon($dj_address, $dj_port);
								$mum = $EthClient->toWei($mum);
								$sendrs = $EthClient->eth_sendTransaction($dj_username,$addr,$dj_password,$mum);

							} elseif($coin='tatc') {
/*								$mo->execute('commit');
								$mo->execute('unlock tables');
								session('myzc_verify', null);
								$this->success(L('转出申请成功,请等待审核！'));*/

								$EthClient = EthCommon($dj_address, $dj_port);
								$mum = dechex ($mum*10000);//代币的位数10000
								$amounthex = sprintf("%064s",$mum);
								$addr2 = explode('0x',  $addr)[1];//接受地址
								$dataraw = '0xa9059cbb000000000000000000000000'.$addr2.$amounthex;//拼接data
								$constadd = '0x09a2fe80c940a39eee7b69e2b89af129cf5006bd';//合约地址
								$sendrs = $EthClient->eth_sendTransactionraw($dj_username,$constadd,$dj_password,$dataraw);
								//转出账户,合约地址,转出账户解锁密码,data值

							} else {//其他币20170922
								$sendrs = $CoinClient->sendtoaddress($addr, floatval($mum));
							}

							if ($sendrs) {
								$res = $mo->table('tw_myzc')->where(array('id'=>$aid))->save(array('txid'=>$sendrs));
								$mo->execute('commit');
								$mo->execute('unlock tables');
							} else {
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
					if ($coin=='eth' or $coin=='tatc') {//以太坊20170922
						if (!$sendrs) {
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
			$this->error('短信验证码格式错误！');
		}
		if ($mobile_verify != session('myzc_verify')) {
			$this->error('短信验证码错误！');
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
			$this->error(L('转入用户1不存在！'));
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
			$this->error(L('转入用户不存在！'));
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
				$this->success(L('转账成功！'));
			} else {
				throw new \Think\Exception('转账1失败！');
			}
		}catch(\Think\Exception $e){
			$mo->execute('rollback');
			$mo->execute('unlock tables');
			$this->error(L('转账失败!'));
		}
	}

	public function mywt($market = NULL, $type = NULL, $status = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($market) || checkstr($type) || checkstr($status)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index.html');
		}

		// 获取币种信息
		$coin_info = M('market')->where(array('name' => $market))->find();
		if(!$coin_info){
			$this->error(L('币种不存在'));
		}
		$this->assign('coin_info', $coin_info);

		if (($type == 1) || ($type == 2)) {
			$where['type'] = $type;
		}
		if (($status == 1) || ($status == 2) || ($status == 3)) {
			$where['status'] = $status - 1;
		}

		$this->assign('market', $market);
		$this->assign('type', $type);
		$this->assign('status', $status);

		// 筛选条件
		$where['userid'] = userid();
		$where['market'] = $market;

		$Mobile = M('Trade');
		$count = $Mobile->db(1,'DB_Read')->where($where)->count();
		$Page = new \Think\Page1($count, 15);
		$show = $Page->show();

		$list = $Mobile->db(1,'DB_Read')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$list[$k]['num'] = $v['num'] * 1;
			$list[$k]['price'] = $v['price'] * 1;
			$list[$k]['deal'] = $v['deal'] * 1;
			if ($v['deal'] <= 0) {
				$list[$k]['demark'] = '未成交';
			} else if ($v['deal'] < $v['num']) {
				$list[$k]['demark'] = '部分成交';
			} else if ($v['deal'] >= $v['num']) {
				$list[$k]['demark'] = '已完成';
			}
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
			redirect('/#login');
		}

		// 获取币种信息
		$coin_info = M('market')->where(array('name' => $market))->find();
		if(!$coin_info){
			$this->error(L('币种不存在'));
		}
		$this->assign('coin_info', $coin_info);

		// $market = $market.'_cny';

		if ($type == 1) {
			$where = 'userid=' . userid() . ' && market=\'' . $market . '\'';
		} else if ($type == 2) {
			$where = 'peerid=' . userid() . ' && market=\'' . $market . '\'';
		} else {
			$where = '((userid=' . userid() . ') || (peerid=' . userid() . ')) && market=\'' . $market . '\'';
		}

		// 按时间筛选条件================================================
		$info = array();
		if (isset($_GET['time1'])) {
			$time1 = $_GET['time1'];
			$info['time1'] = $time1;
		} else {
			$time1 = null;
		}
		if (isset($_GET['time2'])) {
			$time2 = $_GET['time2'];
			$info['time2'] = $time2;
		} else {
			$time2 = null;
		}

		if($time1 && $time2){
			$time1 = strtotime($time1);
			$time2 = strtotime($time2);
			if($time1 < $time2){
				// $where['addtime'] = array(array('egt',$time1),array('elt',$time2));
				$where .= ' && addtime>=' . $time1 . ' && addtime<=\'' . $time2 . '\'';
			}else if($time1 == $time2){
				// $where['addtime'] = array('eq',$time1);
				$where .= ' && addtime=\'' . $time2 . '\'';
			}else if($time1 > $time2){
				// $where['addtime'] = array('egt',$time1);
				$where .= ' && addtime>=\'' . $time1 . '\'';
			}

		}else if($time1 && !$time2){
			$time1 = strtotime($time1);
			// $where['addtime'] = array('egt',$time1);
			$where .= ' && addtime>=\'' . $time1 . '\'';
		}else if(!$time1 && $time2){
			$time2 = strtotime($time2);
			// $where['addtime'] = array('elt',$time2);
			$where .= ' && addtime<=\'' . $time2 . '\'';
		}
		// 按时间筛选条件=====结束===========================================

		$this->assign('market', $market);
		$this->assign('type', $type);
		$this->assign('userid', userid());
		$Mobile = M('TradeLog');
		$count = $Mobile->db(1,'DB_Read')->where($where)->count();
		$Page = new \Think\Page1($count, 15);
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
}
?>