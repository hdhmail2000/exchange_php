<?php
/* 应用 - 集市交易 */
namespace Home\Controller;

class BazaarController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","mywt","mywtUp","sell","buy","log","whole","mycj");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}
	}
	
	public function index($market = NULL)
	{


		// 过滤非法字符----------------S

		if (checkstr($market)) {
			$this->error('您输入的信息有误！');
		}

		// 过滤非法字符----------------E


		//redirect('/');

		if (C('bazaar_login')) {
			if (!userid()) {
				redirect('/Login/index');
			}
		}

		$this->assign('prompt_text', D('Text')->get_content('game_bazaar'));
		$marketConfig = M('BazaarConfig')->where(array('status' => 1))->order('sort asc')->select();
		$market_list = array();
		$market_mr = '';

		if ($marketConfig) {
			foreach ($marketConfig as $k => $v) {
				$market_list[$v['market']] = $v;
			}

			$market_mr = $marketConfig[0];
		}

		if (empty($market) || !$market_list[$market]) {
			$market = $market_mr;
		}

		$this->assign('market', $market);
		$this->assign('market_list', $market_list);
		$where['market'] = $market;
		$where['status'] = 0;
		$count = M('Bazaar')->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = M('Bazaar')->where($where)->order('price asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['num'] = Num($v['num']);
			$list[$k]['deal'] = Num($v['num'] - $v['deal']);
			$list[$k]['price'] = Num($v['price']);
			$list[$k]['mum'] = Num($v['mum']);
			$list[$k]['fee'] = Num($v['fee']);
			$list[$k]['mumfee'] = Num(($v['mum'] / 100) * $v['fee']);
			$list[$k]['addtime'] = addtime($v['addtime']);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function mywt($market = NULL)
	{


		// 过滤非法字符----------------S

		if (checkstr($market)) {
			$this->error('您输入的信息有误！');
		}

		// 过滤非法字符----------------E


		if (!userid()) {
			$this->error('请先登录！');
		}

		$this->assign('prompt_text', D('Text')->get_content('game_bazaar_mycj'));
		$marketConfig = M('BazaarConfig')->where(array('status' => 1))->order('sort asc')->select();
		$market_list = array();
		$market_mr = '';

		if ($marketConfig) {
			foreach ($marketConfig as $k => $v) {
				$market_list[$v['market']] = $v;
			}

			$market_mr = $marketConfig[0];
		}

		if (empty($market) || !$market_list[$market]) {
			$market = $market_mr;
		}

		$this->assign('market', $market);
		$this->assign('market_list', $market_list);
		$where['market'] = $market;
		$where['userid'] = userid();
		$where['status'] = array('egt', 0);
		$count = M('Bazaar')->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = M('Bazaar')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['price'] = Num($v['price']);
			$list[$k]['num'] = Num($v['num']);
			$list[$k]['mum'] = Num($v['mum']);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function mywtUp($market, $num, $price, $paypassword)
	{

		// 过滤非法字符----------------S

		if (checkstr($market) || checkstr($num) || checkstr($price) || checkstr($paypassword)) {
			$this->error('您输入的信息有误！');
		}

		// 过滤非法字符----------------E



		if (!($uid = userid())) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($num, 'double')) {
			$this->error('委托数量格式错误！');
		}

		if (!check($price, 'double')) {
			$this->error('委托价格格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$market_list = D('Bazaar')->get_market_list();

		if (!$market_list) {
			exit('集市交易市场列表配置错误，请在后台添加集市交易市场');
		}

		if (!$market_list[$market]) {
			$this->error('交易市场错误！');
		}

		$bazaar_config = D('BazaarConfig')->where(array('market' => $market))->find();

		if (!$bazaar_config) {
			$this->error('当前市场集市不存在');
		}

		$user = M('User')->where(array('id' => $uid))->find();

		if (!$user) {
			$this->error('用户不存在,非法操作！');
		}

		if (!$user['paypassword'] || (md5($paypassword) != $user['paypassword'])) {
			$this->error('交易密码错误！');
		}

		if ($num < $bazaar_config['num_mix']) {
			$this->error('委托数量不能小于' . ($bazaar_config['num_mix'] * 1));
		}

		if ($bazaar_config['num_max'] < $num) {
			$this->error('委托数量不能大于' . ($bazaar_config['num_max'] * 1));
		}

		if ($price < $bazaar_config['price_min']) {
			$this->error('委托价格不能低于' . ($bazaar_config['price_min'] * 1));
		}

		if ($bazaar_config['price_max'] < $price) {
			$this->error('委托价格不能高于' . ($bazaar_config['price_max'] * 1));
		}

		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];

		if (!$xnb) {
			$this->error('交易市场格式错误！,核心错误');
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write  , tw_bazaar write');
		$rs = array();
		$UserCoin = $mo->table('tw_user_coin')->where(array('userid' => $uid))->find();

		if (!$UserCoin) {
			$this->error('用户信息错误');
		}

		if ($UserCoin[$xnb] < $num) {
			$this->error('余额不足!');
		}

		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $uid))->setDec($xnb, $num);
		$mum = round($num * $price, 8);
		$rs[] = $mo->table('tw_bazaar')->add(array('userid' => $uid, 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $bazaar_config['fee'], 'addtime' => time(), 'status' => 0));

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('提交成功！');
		}
		else {
			$mo->execute('rollback');
			$this->error(APP_DEBUG ? implode('|', $rs) : '提交失败!');
		}
	}

	public function sell($market, $num, $price, $paypassword)
	{

		// 过滤非法字符----------------S

		if (checkstr($market) || checkstr($num) || checkstr($price) || checkstr($paypassword)) {
			$this->error('您输入的信息有误！');
		}

		// 过滤非法字符----------------E



		if (!($uid = userid())) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($num, 'double')) {
			$this->error('委托数量格式错误！');
		}

		if (!check($price, 'double')) {
			$this->error('委托价格格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$market_list = D('Bazaar')->get_market_list();

		if (!$market_list) {
			exit('集市交易市场列表配置错误，请在后台添加集市交易市场');
		}

		if (!$market_list[$market]) {
			$this->error('交易市场错误！');
		}

		$bazaar_config = D('BazaarConfig')->where(array('market' => $market))->find();

		if (!$bazaar_config) {
			$this->error('当前市场集市不存在');
		}

		$user = M('User')->where(array('id' => $uid))->find();

		if (!$user) {
			$this->error('用户不存在,非法操作！');
		}

		if (!$user['paypassword'] || (md5($paypassword) != $user['paypassword'])) {
			$this->error('交易密码错误！');
		}

		if ($num < $bazaar_config['num_mix']) {
			$this->error('委托数量不能小于' . ($bazaar_config['num_mix'] * 1));
		}

		if ($bazaar_config['num_max'] < $num) {
			$this->error('委托数量不能大于' . ($bazaar_config['num_max'] * 1));
		}

		if ($price < $bazaar_config['price_min']) {
			$this->error('委托价格不能低于' . ($bazaar_config['price_min'] * 1));
		}

		if ($bazaar_config['price_max'] < $price) {
			$this->error('委托价格不能高于' . ($bazaar_config['price_max'] * 1));
		}

		$xnb = explode('_', $market)[0];
		$rmb = explode('_', $market)[1];

		if (!$xnb) {
			$this->error('交易市场格式错误！,核心错误');
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write  , tw_bazaar write');
		$rs = array();
		$UserCoin = $mo->table('tw_user_coin')->where(array('userid' => $uid))->find();

		if (!$UserCoin) {
			$this->error('用户信息错误');
		}

		if ($UserCoin[$xnb] < $num) {
			$this->error('余额不足!');
		}

		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $uid))->setDec($xnb, $num);
		$mum = round($num * $price, 8);
		$rs[] = $mo->table('tw_bazaar')->add(array('userid' => $uid, 'market' => $market, 'price' => $price, 'num' => $num, 'mum' => $mum, 'fee' => $bazaar_config['fee'], 'addtime' => time(), 'status' => 0));

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('提交成功！');
		}
		else {
			$mo->execute('rollback');
			$this->error(APP_DEBUG ? implode('|', $rs) : '提交失败!');
		}
	}

	public function buy($id = NULL, $num = NULL, $paypassword = NULL)
	{

		// 过滤非法字符----------------S

		if (checkstr($id) || checkstr($num) || checkstr($paypassword)) {
			$this->error('您输入的信息有误！');
		}

		// 过滤非法字符----------------E



		if (!userid()) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($id, 'd')) {
			$this->error('交易订单格式错误！');
		}

		if (!check($num, 'double')) {
			$this->error('交易数量格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$bazaar = M('Bazaar')->where(array('id' => $id))->find();

		if (!$bazaar) {
			$this->error('交易订单不存在！');
		}

		if (0 < $bazaar['status']) {
			$this->error('交易订单已完成！');
		}

		$bazaar_config = M('BazaarConfig')->where(array('market' => $bazaar['market']))->find();

		if (!$bazaar_config) {
			$this->error('交易市场不存在！');
		}

		if ($bazaar_config['status'] != 1) {
			$this->error('交易市场未开放！');
		}

		if ($num < $bazaar_config['num_min']) {
			$this->error('交易数量不能小于' . $bazaar_config['num_min']);
		}

		if ($bazaar_config['num_max'] < $num) {
			$this->error('交易数量不能大于' . $bazaar_config['num_min']);
		}

		if (($bazaar_config['fee'] < 0) || (100 < $bazaar_config['fee'])) {
			$this->error('该集市手续费设置错误!');
		}

		$xnb = explode('_', $bazaar['market'])[0];
		$rmb = explode('_', $bazaar['market'])[1];
		$mum = round($num * $bazaar['price'], 8);
		$fee = ($mum * $bazaar_config['fee']) / 100;

		if ($mum < 0) {
			$this->error('交易总额错误！', $mum);
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if (!$user) {
			$this->error('用户不存在,非法操作！');
		}

		if (!$user['paypassword'] || (md5($paypassword) != $user['paypassword'])) {
			$this->error('交易密码错误！');
		}

		$user_coin = M('UserCoin')->where(array('userid' => $user['id']))->find();

		if (!$user_coin) {
			$this->error('用户财产错误！');
		}

		if (($user_coin[$rmb] < 0) || ($user_coin[$rmb] < $mum)) {
			$this->error('可用余额不足');
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write  , tw_bazaar write , tw_bazaar_log write');
		$rs = array();
		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $user['id']))->setDec($rmb, $mum);
		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $bazaar['userid']))->setInc($rmb, round($mum - $fee, 8));
		$rs[] = $mo->table('tw_bazaar_log')->add(array('userid' => $user['id'], 'coin' => $xnb, 'price' => $bazaar['price'], 'num' => $num, 'mum' => $mum, 'fee' => $fee, 'addtime' => time(), 'status' => 1));
		$rs[] = $mo->table('tw_bazaar_log')->add(array('userid' => 0, 'coin' => $xnb, 'price' => $bazaar['price'], 'num' => $num, 'mum' => $fee, 'fee' => 0, 'addtime' => time(), 'status' => 1));

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('提交成功！');
		}
		else {
			$mo->execute('rollback');
			$this->error(APP_DEBUG ? implode('|', $rs) : '提交失败!');
		}
	}

	public function log()
	{
		if (!S('game_list_auth_bazaar')) {
			redirect('/');
		}

		$this->assign('prompt_text', D('Text')->get_content('bazaar_log'));

		if (IS_POST) {
			$input = I('post.');

			if (!check($input['id'], 'd')) {
				$this->error('请选择要要买入的挂单！');
			}

			if (!check($input['num'], 'double')) {
				$this->error('交易数量格式错误');
			}
			else {
				$num = round(trim($input['num']), 6);
			}

			if (10000000 < $num) {
				$this->error('交易数量超过最大限制！');
			}

			if ($num < 9.9999999999999995E-7) {
				$this->error('交易数量超过最小限制！');
			}

			$user = $this->User(0, 0);

			if (!$user['id']) {
				$this->error('请先登录！');
			}

			$bazaar = M('Bazaar')->where(array('id' => $input['id'], 'status' => 0))->find();

			if (!$bazaar) {
				$this->error('挂单错误！');
			}

			if (md5($input['paypassword']) != $user['paypassword']) {
				$this->error('交易密码错误！');
			}

			if (($bazaar['num'] - $bazaar['deal']) < $input['num']) {
				$this->error('剩余量不足！');
			}

			$mum = round($bazaar['price'] * $input['num'], 6);
			$fee = C('bazaar_fee');

			if ($user['coin'][$bazaar['coin']] < $mum) {
				$this->error('可用余额不足');
			}

			$buy_shang_mum = round(((($mum / 100) * (100 - $fee)) / 100) * (100 - C('bazaar_invit1')), 6);
			$sell_mum = round(($mum / 100) * (100 - $fee), 6);
			$zong_fee = round(($mum / 100) * $fee, 6);
			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_invit write , tw_user write , tw_user_coin write  , tw_bazaar write  , tw_bazaar_log write');
			$rs = array();
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $user['id']))->setDec(C('rmb_mr'), $mum);
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $user['id']))->setInc($bazaar['coin'], $input['num']);
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $bazaar['userid']))->setInc(C('rmb_mr'), $sell_mum);
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $bazaar['userid']))->setDec($bazaar['coin'], $input['num']);
			$rs[] = $mo->table('tw_bazaar')->where(array('id' => $bazaar['id']))->setInc('deal', $input['num']);

			if ($bazaar['num'] <= $bazaar['deal']) {
				$rs[] = $mo->table('tw_bazaar')->where(array('id' => $bazaar['id']))->save(array('status' => 1));
			}

			$rs[] = $mo->table('tw_bazaar_log')->add(array('userid' => $user['id'], 'peerid' => $bazaar['userid'], 'coin' => $bazaar['coin'], 'price' => $bazaar['price'], 'num' => $input['num'], 'mum' => $mum, 'fee' => $zong_fee, 'addtime' => time(), 'status' => 1));

			if ($buy_shang_mum) {
				$invit = $mo->table('tw_user')->where(array('id' => $bazaar['userid']))->find();

				if ($invit['id']) {
					$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $invit['id']))->setInc(C('rmb_mr'), $buy_shang_mum);
					$rs[] = $mo->table('tw_invit')->add(array('userid' => $bazaar['userid'], 'invit' => $invit['username'], 'type' => '集市赠送', 'num' => $mum, 'mum' => $buy_shang_mum, 'addtime' => time(), 'status' => 1));
				}
			}

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success('购买成功！');
			}
			else {
				$mo->execute('rollback');
				$this->error(APP_DEBUG ? implode('|', $rs) : '购买失败!');
			}
		}
		else {
			// TODO: SEPARATE
			$input = I('get.');
			$coin = (is_array(C('coin')[$input['coin']]) ? trim($input['coin']) : C('xnb_mr'));
			$this->assign('coin', $coin);
			$where['coin'] = $coin;
			$where['status'] = 0;
			import('ORG.Util.Page');
			$Mobile = M('Bazaar');
			$count = $Mobile->where($where)->count();
			$Page = new \Think\Page($count, 30);
			$show = $Page->show();
			$list = $Mobile->where($where)->order('price asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

			foreach ($list as $k => $v) {
				$list[$k]['price'] = $v['price'] * 1;
				$list[$k]['num'] = $v['num'] * 1;
				$list[$k]['mum'] = $v['mum'] * 1;
			}

			$this->assign('list', $list);
			$this->assign('page', $show);
			$this->display();
		}
	}

	public function whole()
	{
		if (!S('game_list_auth_bazaar')) {
			redirect('/');
		}
		// TODO: SEPARATE

		$this->assign('prompt_text', D('Text')->get_content('game_bazaar_whole'));
		$input = I('get.');
		$coin = (is_array(C('coin')[$input['coin']]) ? trim($input['coin']) : C('xnb_mr'));
		$this->assign('coin', $coin);
		$where = 'coin = \'' . $coin . '\' and status = \'1\' and userid > \'0\' and type = 1';
		import('ORG.Util.Page');
		$Mobile = M('BazaarLog');
		$count = $Mobile->where($where)->count();
		$Page = new \Think\Page($count, 30);
		$show = $Page->show();
		$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['price'] = $v['price'] * 1;
			$list[$k]['num'] = $v['num'] * 1;
			$list[$k]['mum'] = $v['mum'] * 1;
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function mycj()
	{
		if (!($uid = userid())) {
			redirect('/Login/index');
		}

		if (!S('game_list_auth_bazaar')) {
			redirect('/');
		}
		// TODO: SEPARATE

		$this->assign('prompt_text', D('Text')->get_content('game_bazaar_mywt'));
		$input = I('get.');
		$coin = (is_array(C('coin')[$input['coin']]) ? trim($input['coin']) : C('xnb_mr'));
		$this->assign('coin', $coin);
		$where['coin'] = $coin;
		$where['status'] = 1;
		$where['userid'] = $uid;
		import('ORG.Util.Page');
		$Mobile = M('BazaarLog');
		$count = $Mobile->where($where)->count();
		$Page = new \Think\Page($count, 30);
		$show = $Page->show();
		$list = $Mobile->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['price'] = $v['price'] * 1;
			$list[$k]['num'] = $v['num'] * 1;
			$list[$k]['mum'] = $v['mum'] * 1;
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
}

?>