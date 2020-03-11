<?php
/* 应用 - 理财中心 */
namespace Mobile\Controller;

class FinancingController extends MobileController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","queue","log","dlog","fee","info","beforeGet","danweitostr", "adds");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}
	}

	public function index()
	{
		if (IS_POST)
		{
			if (!userid()) {
				$this->error(L('请先登录！'));
			}
			
			$id = $_POST['id'];
			$num = $_POST['num'];
			$paypassword = $_POST['paypassword'];

			if (!check($id, 'd')) {
				$this->error(L('ID编号格式错误！'));
			}
			if (!check($num, 'd')) {
				$this->error(L('理财数量格式错误！'));
			}
			if (!check($paypassword, 'password')) {
				$this->error(L('交易密码格式错误！'));
			}

/*			$money_min = (C('money_min') ? C('money_min') : 1);
			$money_max = (C('money_max') ? C('money_max') : 10000000);
			$money_bei = (C('money_bei') ? C('money_bei') : 1);*/

			$money_min = 1;
			$money_max = 1000000;
			$money_bei = 1;
			
			if ($num < $money_min) {
				$this->error('理财数量超过系统最小限制1！');
			}
			if ($money_max < $num) {
				$this->error('理财数量超过系统最大限制！');
			}
			if ($num % $money_bei != 0) {
				$this->error('每次理财数量必须是' . $money_bei . '的整倍数！');
			}

			$user = M('User')->where(array('id' => userid()))->find();
			if (md5($paypassword) != $user['paypassword']) {
				$this->error('交易密码错误！');
			}

			$money = M('Money')->where(array('id' => $id))->find();
			if (!$money) {
				$this->error('当前理财错误！');
			}
			if (!$money['status']) {
				$this->error('当前理财已经禁用！');
			}
			if (($money['num'] - $money['deal']) < $num) {
				$this->error('系统剩余量不足！');
			}

			$userCoin = M('UserCoin')->where(array('userid' => userid()))->find();
			if (!$userCoin || !isset($userCoin[$money['coinname']])) {
				$this->error('当前品种错误!');
			}
			if ($userCoin[$money['coinname']] < $num) {
				$this->error('可用余额不足,当前账户余额:' . $userCoin[$money['coinname']]);
			}

/*			$money_log_num = M('MoneyLog')->where(array('userid' => userid(), 'money_id' => $money['id']))->sum('num');
			if ($money['max'] < ($money_log_num + $num)) {
				$this->error('当前理财最大可购买' . $money['lnum'] . ',您已经购买:' . $money_log_num);
			}*/
			
			$money_log_num = M("MoneyLog")->where("userid = ".userid().' and money_id = '.$money['id']." and addtime > ".(time()-$userCoin["step"]))->sum("num");
			if ($money["lnum"] < ($money_log_num + $num)) {
				debug(array($money_log_num, M("MoneyLog")->getLastSql()));
				$this->error("本周期内最大可购买" . $money["lnum"] . ",您已经购买:" . $money_log_num);
			}

			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_user_coin write, tw_money_log write, tw_money write');
			$rs = array();
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($money['coinname'], $num);
			$rs[] = $mo->table("tw_money_log")->add(array("userid" => $user["id"], "money_id" => $money["id"], "num" => $num, "addtime" => time(), "status" => 1));
			
			if ($money['num'] <= $money['deal']) {
				$rs[] = $mo->table('tw_money')->where(array('id' => $id))->setField('status', 0);
			} else {
				$rs[] = $mo->table('tw_money')->where(array('id' => $id))->setInc('deal', $num);
			}

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$this->success('购买成功！');
			} else {
				$mo->execute('rollback');
				$this->error(APP_DEBUG ? implode('|', $rs) : '购买失败!');
			}
		} else {
			$where['status'] = 1;
			$count = M('Money')->where($where)->count();
			$Page = new \Think\Page($count, 10);
			$show = $Page->show();
			$list = M('Money')->where($where)->order('sort desc')->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

			foreach ($list as $k => $v) {
				$list[$k]['fee'] = Num($v['fee']);
				$list[$k]['addtime'] = addtime($v['addtime']);
				$list[$k]['bili'] = round($v['deal'] / $v['num'], 2) * 100;
				$list[$k]['times'] = M('MoneyLog')->where(array('money_id' => $v['id']))->count();
				$list[$k]['shen'] = round($v['num'] - $v['deal'], 2);
				$list[$k]["tian"] = $list[$k]["tian"] . '<span class="unit">' . $this->danweitostr($list[$k]["danwei"]).'</span>';
				$list[$k]["shengyu"] = $v["num"] - $v["deal"]; // number_format($v["num"] - $v["deal"])
			}

			$this->assign('list', $list);
			$this->assign('page', $show);

			
			$log_where['userid'] = userid();
			$log_count = M('MoneyLog')->where($log_where)->count();
			$log_Page = new \Think\Page($log_count, 10);
			$log_show = $log_Page->show();
			$log_list = M('MoneyLog')->where($log_where)->order('id desc')->limit($log_Page->firstRow . ',' . $log_Page->listRows)->select();

			foreach ($log_list as $k => $v) {
				$log_list[$k]["money"] = M("Money")->where(array("id" => $v["money_id"]))->find();
				$log_list[$k]["money"]["tian"] = $log_list[$k]["money"]["tian"] . " " . $this->danweitostr($log_list[$k]["money"]["danwei"]);
			}

			$this->assign('log_list', $log_list);
			$this->assign('log_page', $log_show);
			$this->display();
		}
	}

	public function adds($id)
	{
		if (!userid()) {
			$this->error(L('请先登录！'));
		}
		
		$id = intval($id);
		if (!$id) {
			$this->error("参数错误");
		}
		
		$Money = M("Money")->where(array("id" => $id))->find();
		$coin_info = M("Coin")->where(array("name" => $Money['coinname']))->find();
		$user_coin = M("UserCoin")->where(array("userid" => userid()))->find();
		
		$this->assign('Money', $Money);
		$this->assign('coin_info', $coin_info);
		$this->assign('user_coin', $user_coin);
		$this->assign('coin', $Money['coinname']);
		$this->assign('ids', $id);
		$this->display();
	}
	
	public function log()
	{
		if (!userid()) {
			redirect(U('Login/index'));
		}

		$where['userid'] = userid();
		$count = M('MoneyLog')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('MoneyLog')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]["money"] = M("Money")->where(array("id" => $v["money_id"]))->find();
			$list[$k]["money"]["tian"] = $list[$k]["money"]["tian"] . " " . $this->danweitostr($list[$k]["money"]["danwei"]);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	public function dlog()
	{
		if (!userid()) {
			redirect(U('Login/index'));
		}

		$input = I("get.");
		$where["userid"] = userid();
		$count = M("MoneyDlog")->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M("MoneyDlog")->where($where)->order("id desc")->limit($Page->firstRow . "," . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]["money"] = M("Money")->where(array("id" => $v["money_id"]))->find();
		}

		$this->assign("list", $list);
		$this->assign("page", $show);
		$this->display();
	}

	public function fee()
	{
		if (!userid()) {
			redirect(U('Login/index'));
		}

		$id = $_GET['id'];
		if (!check($id, 'd')) {
			$this->error('参数错误!');
		}

		$where['moneylogid'] = $id;
		$where['userid'] = userid();
		$count = M('MoneyFee')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('MoneyFee')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	public function info($id)
	{
		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		$id = intval($id);
		if (!$id) {
			$this->error("参数错误");
		}

		$Money = M("Money")->where(array("id" => $id))->find();
		$UserCoin = M("UserCoin")->where(array("userid" => userid()))->find();
		
		$ret = array();
		$ret["Money"] = array_merge($Money, array("yue" => $UserCoin[$Money["coinname"]]));
		

		if ($ret["Money"]['type'] == 1) {
			$types = '活期';
		} else {
			$types = '定期';
		}

		$ret["Money"]['type'] = $types;
		
		$this->success($ret);
	}

	public function beforeGet($id)
	{
		if (!userid()) {
			redirect(U('Login/index'));
		}

		$id = intval($id);
		
		$MoneyLog = M('MoneyLog')->where(array('userid' => userid(), 'id' => $id, 'status' => 1))->find();
		if (!$MoneyLog) {
			$this->error('参数错误');
		}

		$Money = M('Money')->where(array('id' => $MoneyLog['money_id']))->find();
		if (!$Money) {
			$this->error('参数错误');
		}

		$num = $MoneyLog['num'];
		$fee = ($Money['outfee'] ? round(($MoneyLog['num'] * $Money['outfee']) / 100, 8) : 0);
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write  , tw_money_log  write,tw_money_dlog  write');
		$rs = array();

		if ($Money['coinname'] != $Money['feecoin']) {
			$user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();

			if (!isset($user_coin[$Money['feecoin']])) {
				$this->error('利息品种不存在,请联系管理员');
			}
			if ($user_coin[$Money['feecoin']] < $fee) {
				$this->error('您的' . $Money['feecoin'] . '不够取现手续费(' . $fee . ')');
			}

			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($Money['feecoin'], $fee);
			debug($mo->table('tw_user_coin')->getLastSql(), 'tw_user_coin_sql0');
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setInc($Money['coinname'], $num);
			debug($mo->table('tw_user_coin')->getLastSql(), 'tw_user_coin_sql1');
		} else {
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setInc($Money['coinname'], round($num - $fee, 8));
			debug($mo->table('tw_user_coin')->getLastSql(), 'tw_user_coin_sql2');
		}

		$rs[] = $mo->table('tw_money_log')->where(array('id' => $MoneyLog['id']))->setField('status', 0);
		debug($mo->table('tw_money_log')->getLastSql(), 'tw_money_log_sql');
		$rs[] = $mo->table('tw_money_dlog')->add(array('userid' => userid(), 'money_id' => $Money['id'], 'type' => 2, 'num' => $fee, 'addtime' => time(), 'content' => '提前抽取' . $Money['title'] . ' 理财本金' . $Money['coinname'] . ' ' . $MoneyLog['num'] . '个,扣除利息' . $Money['feecoin'] . ': ' . $fee . '个'));

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('操作成功！');
		} else {
			$mo->execute('rollback');
			$this->error(APP_DEBUG ? implode('|', $rs) : '操作失败!');
		}
	}

	private function danweitostr($danwei)
	{
		switch ($danwei) {
		case 'y':
			return '年';
			break;

		case 'm':
			return '月';
			break;

		case 'd':
			return '天';
			break;

		case 'h':
			return '小时';
			break;

		default:

		case 'i':
			return '分钟';
			break;
		}
	}
}
?>