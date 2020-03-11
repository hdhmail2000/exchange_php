<?php
namespace Home\Controller;

class PoolController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","log","startpool","receiving");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->title = '集市交易';
	}

	public function index()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		if (IS_POST) {
			$input = I('post.');


			foreach ($input as $k => $v) {
				// 过滤非法字符----------------S

				if (checkstr($v)) {
					$this->error('您输入的信息有误！');
				}

				// 过滤非法字符----------------E
			}

			if (!check($input['num'], 'd')) {
				$this->error('购买数量格式错误！');
			}

			if ($input['num'] < 1) {
				$this->error('购买数量错误！');
			}

			if (!check($input['id'], 'd')) {
				$this->error('矿机类型格式错误！');
			}

			$user = $this->User(0, 0);

			if (!$user['id']) {
				$this->error('请先登录！');
			}

			if (md5($input['paypassword']) != $user['paypassword']) {
				$this->error('交易密码错误！');
			}

			$pool = M('Pool')->where(array('id' => $input['id']))->find();

			if (!$pool) {
				$this->error('矿机类型错误！');
			}

			if ($pool['status'] != 1) {
				$this->error('当前矿机没有开通购买！');
			}

			$mum = round($pool['price'] * $input['num'], 6);

			if ($user['coin'][C('rmb_mr')] < $mum) {
				$this->error('可用人民币余额不足');
			}

			$poolLog = M('PoolLog')->where(array('userid' => $user['id'], 'name' => $pool['name']))->sum('num');

			if ($pool['limit']) {
				if ($pool['limit'] < ($poolLog + $input['num'])) {
					$this->error('购买总数量超过限制！');
				}
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables  tw_user write , tw_pool_log  write ,tw_user_coin write');
			$rs = array();
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $user['id']))->setDec(C('rmb_mr'), $mum);
			$rs[] = $mo->table('tw_pool_log')->add(array('userid' => $user['id'], 'coinname' => $pool['coinname'], 'name' => $pool['name'], 'ico' => $pool['ico'], 'price' => $pool['price'], 'num' => $input['num'], 'tian' => $pool['tian'], 'power' => $pool['power'], 'endtime' => time(), 'addtime' => time(), 'status' => 0));

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
		else {
			//$this->get_text();
			$list = M('Pool')->where(array('status' => 1))->select();
			$this->assign('list', $list);
			$this->display();
		}
	}

	public function log()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$user = $this->User();
		$input = I('get.');
		$where['status'] = array('egt', 0);
		$where['userid'] = $user['id'];
		import('ORG.Util.Page');
		$Model = M('PoolLog');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function startpool()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		if (IS_POST) {
			$input = I('post.');

			foreach ($input as $k => $v) {
				// 过滤非法字符----------------S

				if (checkstr($v)) {
					$this->error('您输入的信息有误！');
				}

				// 过滤非法字符----------------E
			}


			if (!check($input['id'], 'd')) {
				$this->error('请选择要工作的矿机！');
			}

			$poolLog = M('PoolLog')->where(array('id' => $input['id']))->find();

			if (!$poolLog) {
				$this->error('参数错误！');
			}

			if ($poolLog['status']) {
				$this->error('访问错误！');
			}

			$user = $this->User(0, 0);

			if (!$user['id']) {
				$this->error('请先登录！');
			}

			if ($poolLog['userid'] != $user['id']) {
				$this->error('非法访问');
			}

			$mum = round($poolLog['price'] * $poolLog['num'], 6);
			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_pool_log write');
			$rs = array();
			$rs[] = $mo->table('tw_pool_log')->where(array('id' => $poolLog['id']))->save(array('endtime' => time(), 'status' => 1));

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success('矿机已经开始工作！');
			}
			else {
				$mo->execute('rollback');
				$this->error(APP_DEBUG ? implode('|', $rs) : '矿机工作失败！');
			}
		}
	}

	public function receiving()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		if (IS_POST) {
			$input = I('post.');


			foreach ($input as $k => $v) {
				// 过滤非法字符----------------S

				if (checkstr($v)) {
					$this->error('您输入的信息有误！');
				}

				// 过滤非法字符----------------E
			}

			if (!check($input['id'], 'd')) {
				$this->error('请选择要收矿的矿机！');
			}

			$poolLog = M('PoolLog')->where(array('id' => $input['id']))->find();

			if (!$poolLog) {
				$this->error('参数错误！');
			}

			if ($poolLog['tian'] <= $poolLog['use']) {
				$this->error('非法访问！');
			}

			$tm = $poolLog['endtime'] + (60 * 60 * C('pool_jian'));

			if (time() < $tm) {
			}

			$user = $this->User(0, 0);

			if (!$user['id']) {
				$this->error('请先登录！');
			}

			if ($poolLog['userid'] != $user['id']) {
				$this->error('非法访问');
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_user_coin write ,  tw_pool_log  write ');
			$rs = array();
			$num = round($poolLog['num'] * C('pool_suan') * $poolLog['power'], 6);
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $user['id']))->setInc($poolLog['coinname'], $num);
			$rs[] = $mo->table('tw_pool_log')->where(array('id' => $poolLog['id']))->save(array('use' => $poolLog['use'] + 1, 'endtime' => time()));

			if ($poolLog['tian'] <= $poolLog['use'] + 1) {
				$rs[] = $mo->table('tw_pool_log')->where(array('id' => $poolLog['id']))->save(array('status' => 2));
			}

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success('收矿成功！获得' . $num . '个品种');
			}
			else {
				$mo->execute('rollback');
				$this->error('收矿失败！');
			}
		}
	}
}

?>