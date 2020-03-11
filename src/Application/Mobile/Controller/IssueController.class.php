<?php
/* 应用 - ICO众筹认购 */
namespace Mobile\Controller;

class IssueController extends MobileController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("index","details","log", "updata","unlock");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}
	}

	public function index()
	{
		$list  = array();
		$where['status'] = 1;
		$coin_list = M('Issue')->where($where)->order('sort desc')->select();
		if (is_array($coin_list)) {
			foreach ($coin_list as $k => $v) {		
				$list[$k]['id'] = $v['id'];
				$list[$k]['name'] = $v['name'];
				$list[$k]['coinname'] = $v['coinname'];
				$list[$k]['abstract'] = $v['abstract'];
				$list[$k]['show_item'] = $v['show_item'];
				$list[$k]['tongji'] = M('issue_log')->where(array('pid'=>$v['id'] ,'coinname'=>$v['coinname']))->count();
				$list[$k]['bili'] = round(($v['deal'] / $v['num']) * 100, 2);
				
				$begin_ms = strtotime($v['time']);
				$end_ms = $v['endtime'];
				$list[$k]['beginTime'] = date("Y-m-d H:i:s",$begin_ms);
				$list[$k]['endTime'] = date("Y-m-d H:i:s",$end_ms);
				
				$list[$k]['zhuangtai'] = '<b class="jxz">'.L("进行中").'</b>' ;
				$list[$k]['statuss'] = 1; //进行中
				
				if ($v['show_item'] == 1) { //路演展示
					$list[$k]['zhuangtai'] = '<b class="lyzs">'.L("路演展示").'</b>' ;
					$list[$k]['statuss'] = 0;
				} else {
					if ($begin_ms>time()) { //尚未开始
						$list[$k]['zhuangtai'] = '<b class="lyzs">'.L("即将上线").'</b>';
						$list[$k]['statuss'] = 0;
						$list[$k]['show_item'] = 2;
					}
					if ($v['num']<=$v['deal']) { //已结束
						$list[$k]['zhuangtai'] = '<b class="yjs">'.L("已结束").'</b>';
						$list[$k]['statuss'] = 2;
					}
					if ($end_ms<time()) { //已结束
						$list[$k]['zhuangtai'] = '<b class="yjs">'.L("已结束").'</b>';
						$list[$k]['statuss'] = 2;
					}
				}
			}
		}
		
		$this->assign('text', D('Text')->get_url('apps_vlssue'));
		$this->assign('list', $list);
		$this->display();
	}
	
	public function details($id=NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($id)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (!userid()) {
			$this->assign('logins', 0);
		} else {
			$this->assign('logins', 1);
		}
		
		if (!check($id, 'd')) {
			$this->error(L('参数错误!'));
		}
		$this->assign('cid', $id);
		
		$info = M('Issue')->where(array('id'=>$id))->find();
		
		$info['tongji'] = M('issue_log')->where(array('pid'=>$id ,'coinname'=>$info['coinname']))->count();
		$info_bili = round(($info['deal'] / $info['num']) * 100, 2);
		if ($info_bili > 0) {
			$info['bili'] = $info_bili;
		} else {
			$info['bili'] = 0;
		}
		
		//$info['endtime'] = date("Y-m-d H:i:s",strtotime($info['time']." + {$info['tian']} day"));
		$begin_ms = strtotime($info['time']);
		$end_ms = $info['endtime'];
		$info['beginTime'] = date("Y-m-d H:i:s",$begin_ms);
		$info['endtime'] = date("Y-m-d H:i:s",$end_ms);
		
		$this->assign('info', $info);
		
		$UserCoin = M('user_coin')->where(array('userid'=>userid()))->find();
		$UserCoin['kyye'] =  round($UserCoin[$info['buycoin']],5)*1;
		$this->assign('UserCoin', $UserCoin);
		
		
		$timejd['zhuangtai'] = '<b class="jxz">'.L("进行中").'</b>' ;
		$timejd['statuss'] = 1; //进行中

		if ($info['show_item'] == 1) { //路演展示
			$timejd['zhuangtai'] = '<b class="lyzs">'.L("路演展示").'</b>' ;
			$timejd['statuss'] = 0;
		} else {
			if ($begin_ms>time()) { //尚未开始
				$timejd['zhuangtai'] = '<b class="lyzs">'.L("即将上线").'</b>';
				$timejd['statuss'] = 2;
			}
			if ($info['num']<=$info['deal']) { //已结束
				$timejd['zhuangtai'] = '<b class="yjs">'.L("已结束").'</b>';
				$timejd['statuss'] = 3;
			}
			if ($end_ms<time()) { //已结束
				$timejd['zhuangtai'] = '<b class="yjs">'.L("已结束").'</b>';
				$timejd['statuss'] = 3;
			}
		}
		
		$this->assign('timejd', $timejd);
		
		$this->display();
	}
	
	public function log($ls = 15)
	{
		// 过滤非法字符----------------S
		if (checkstr($ls)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		$where['status'] = array('egt', 0);
		$where['userid'] = userid();

		$IssueLog = M('IssueLog');
		$count = $IssueLog->where($where)->count();
		$Page = new \Think\Page($count, $ls);
		$show = $Page->show();
		$list = $IssueLog->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['jian'] = $v['jian'].' '.$this->danweitostr($v['danwei']);
			//$list[$k]['shen'] = round((($v['ci'] - $v['unlock']) * $v['num']) / $v['ci'], 6);
			//$list[$k]['endtime'] = date("Y-m-d H:i:s",strtotime($v['time']." + {$v['tian']} day"));
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function updata($id, $num, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($num) || checkstr($paypassword)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (!userid()) {
			$this->error(L('请先登录！'));
		}
		
		if (!check($id, 'd')) {
			$this->error(L('ID参数错误!'));
		}
		if (!check($num, 'd')) {
			$this->error(L('认购数量格式错误！'));
		}
		if (!check($paypassword, 'password')) {
			$this->error(L('交易密码格式错误！'));
		}
		
		$user = M('User')->where(array('id'=>userid()))->find();
		if (md5($paypassword) != $user['paypassword']) {
			$this->error(L('交易密码错误！'));
		}

		$Issue = M('Issue')->where(array('id' => $id))->find();
		if (!$Issue) {
			$this->error('认购错误！');
		}
		if (time() < strtotime($Issue['time'])) {
			$this->error('当前认购还未开始！');
		}
		if (!$Issue['status']) {
			$this->error('当前认购已经结束！');
		}

		$issue_min = ($Issue['min'] ? $Issue['min'] : 1);
		$issue_max = ($Issue['max'] ? $Issue['max'] : 100000000);
		if ($num < $issue_min) {
			$this->error('单次认购数量不得少于系统设置' . $issue_min . '个');
		}
		if ($issue_max < $num) {
			$this->error('单次认购数量不得大于系统设置' . $issue_max . '个');
		}
		if (($Issue['num'] - $Issue['deal']) < $num) {
			$this->error('认购数量超过当前剩余量！');
		}

		$mum = round($Issue['price'] * $num, 6);

/*
		// 判断是否新注册 是否推荐了别人注册 做出认购限制
		$Userss = M('User')->where(array('invit_1' => userid()))->select();
		$cur_rg_max = 0 ;
		if (!$Userss) {
			$cur_rg_max = C('new_max_rg');
			if($mum > $cur_rg_max){
				$this->error('新注册最高认购 '. $cur_rg_max .' 元');
			}
		} else {
			$cur_rg_max = intval(C('new_tui_add_rg')) * count($Userss);
			if($mum > $cur_rg_max){
				$this->error('您最高可认购 '. $cur_rg_max .' 元');
			}
		}
*/

		if (!$mum) {
			$this->error('认购总额错误');
		}

		$buycoin = M('UserCoin')->where(array('userid' => userid()))->getField($Issue['buycoin']);
		if ($buycoin < $mum) {
			$this->error('可用'.C('coin')[$Issue['buycoin']]['title'].'余额不足');
		}

		$issueLog = M('IssueLog')->where(array('userid'=>userid(), 'coinname'=>$Issue['coinname']))->sum('num');
		if ($Issue['limit'] < ($issueLog + $num)) {
			$this->error('认购总数量超过最大限制'.$Issue['limit']);
		}

		if ($Issue['ci']) {
			$jd_num = round($num / $Issue['ci'], 6);
		} else {
			$jd_num = $num;
		}

		if (!$jd_num) {
			$this->error('认购解冻数量错误');
		}

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_invit write, tw_user_coin write ,tw_issue write ,tw_issue_log write ,tw_finance_log write');

		$rs = array();		
		$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
		// $rs[] = $finance_nameid = $mo->table('tw_issue_log')->add(array('userid' => userid(), 'coinname' => $Issue['coinname'], 'buycoin' => $Issue['buycoin'], 'name' => $Issue['name'], 'price' => $Issue['price'], 'num' => $num, 'mum' => $mum, 'ci' => $Issue['ci'], 'jian' => $Issue['jian'], 'unlock' => 1, 'addtime' => time(), 'endtime' => time(), 'status' => $Issue['ci'] == 1 ? 1 : 0));//原本购买就解冻一次,释放到钱包
		
		//endtime(下次解冻时间)=结束时间+间隔时间
		$rs[] = $finance_nameid = $mo->table('tw_issue_log')->add(array('userid'=>userid(), 'pid'=>$id, 'coinname'=>$Issue['coinname'], 'buycoin'=>$Issue['buycoin'], 'name'=>$Issue['name'], 'price'=>$Issue['price'], 'num'=>$num, 'mum'=>$mum, 'ci' =>$Issue['ci'], 'jian'=>$Issue['jian'], 'unlock'=>0, 'addtime'=>time(), 'endtime'=>($Issue['endtime']+(60*60*$Issue['jian'])), 'status'=>0));
		
		/* 修改金额 */
		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($Issue['buycoin'], $mum);
		$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
		
		// 处理资金变更日志-----------------S
		/*
		 * 操作位置（0后台，1前台） position
		 * 动作类型（参考function.php） optype
		 * 资金类型（1人民币） cointype
		 * 类型（0减少，1增加） plusminus
		 * 操作数据 amount
		 */
		$rs[] = $mo->table('tw_finance_log')->add(array('username' => session('userName'), 'adminname' => session('userName'), 'addtime' => time(), 'plusminus' => 0, 'amount' => $mum, 'optype' => 31, 'position' => 1, 'cointype' => C("coin")[$Issue['buycoin']]["id"], 'old_amount' => $finance_num_user_coin[$Issue['buycoin']], 'new_amount' => $finance_mum_user_coin[$Issue['buycoin']], 'userid' => session('userId'), 'adminid' => session('userId'),'addip'=>get_client_ip()));
		// 处理资金变更日志-----------------E

		// $rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setInc($Issue['coinname'], $jd_num);//原本购买就解冻一次,释放到钱包,取消这个解冻

		$rs[] = $mo->table('tw_issue')->where(array('id' => $id))->setInc('deal', $num);

		if ($Issue['num'] <= $Issue['deal']) {
			$rs[] = $mo->table('tw_issue')->where(array('id' => $id))->setField('status', 0);
		}
		
		// 推荐认购返利奖励
		if ($User['invit_1'] && $Issue['invit_1']) {
			$invit_num_1 = round(($mum / 100) * $Issue['invit_1'], 6);
			if ($invit_num_1) {
				$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $User['invit_1']))->setInc($Issue['invit_coin'], $invit_num_1);
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $User['invit_1'], 'invit' => userid(), 'name' => $Issue['name'], 'type' => '一代认购赠送', 'num' => $num, 'mum' => $mum, 'fee' => $invit_num_1, 'addtime' => time(), 'status' => 1,'coin'=>strtoupper($Issue['invit_coin'])));
			}
/*
			// 直系下属认购额奖励
			$invit_num_1s = round(($mum / 100) * intval(C('tui_rg_jl')), 6);
			if ($invit_num_1s) {
				$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $User['invit_1']))->setInc($Issue['invit_coin'], $invit_num_1s);
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $User['invit_1'], 'invit' => userid(), 'name' => $Issue['name'], 'type' => '直系下属认购奖励', 'num' => $num, 'mum' => $mum, 'fee' => $invit_num_1s, 'addtime' => time(), 'status' => 1,'coin'=>strtoupper($Issue['invit_coin'])));
			}
*/
		}
		if ($User['invit_2'] && $Issue['invit_2']) {
			$invit_num_2 = round(($mum / 100) * $Issue['invit_2'], 6);
			if ($invit_num_2) {
				$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $User['invit_2']))->setInc($Issue['invit_coin'], $invit_num_2);
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $User['invit_2'], 'invit' => userid(), 'name' => $Issue['name'], 'type' => '二代认购赠送', 'num' => $num, 'mum' => $mum, 'fee' => $invit_num_2, 'addtime' => time(), 'status' => 1,'coin'=>strtoupper($Issue['invit_coin'])));
			}
		}
		if ($User['invit_3'] && $Issue['invit_3']) {
			$invit_num_3 = round(($mum / 100) * $Issue['invit_3'], 6);
			if ($invit_num_3) {
				$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $User['invit_3']))->setInc($Issue['invit_coin'], $invit_num_3);
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $User['invit_3'], 'invit' => userid(), 'name' => $Issue['name'], 'type' => '三代认购赠送', 'num' => $num, 'mum' => $mum, 'fee' => $invit_num_3, 'addtime' => time(), 'status' => 1,'coin'=>strtoupper($Issue['invit_coin'])));
			}
		}

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('购买成功！');
		} else {
			$mo->execute('rollback');
			$this->error('购买失败!');
		}
	}

	public function unlock($id)
	{
		// 过滤非法字符----------------S
		if (checkstr($id)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}
		if (!check($id, 'd')) {
			$this->error('请选择解冻项！');
		}

		$IssueLog = M('IssueLog')->where(array('id' => $id))->find();
		if (!$IssueLog) {
			$this->error('参数错误！');
		}
		if ($IssueLog['status']) {
			echo '当前解冻已完成！';die();
		}
		if ($IssueLog['ci'] <= $IssueLog['unlock']) {
			$this->error('非法访问！');
		}
		if ($IssueLog['userid'] != userid()) {
			$this->error('非法访问');
		}
		
		$Issue = M('Issue')->where(array('id' => $IssueLog['pid']))->find();
		$tm = $IssueLog['endtime'] + $Issue["step"];
		if (time() < $tm) {
			//$this->error('尚未到解冻时间!<br>请在【' . date('Y-m-d',($tm)) . '】之后再次操作');
			echo '尚未到解冻时间!<br>请在【' . addtime($tm) . '】<br>之后再次操作';die();
		}

		$jd_num = round($IssueLog['num'] / $IssueLog['ci'], 6);

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write ,tw_issue_log write');

		$rs = array();
		$rs[] = $mo->table('tw_user_coin')->where(array('userid'=>userid()))->setInc($IssueLog['coinname'], $jd_num);
		$rs[] = $mo->table('tw_issue_log')->where(array('id'=>$IssueLog['id']))->save(array('unlock'=>$IssueLog['unlock']+1, 'endtime'=>time()));//解冻时间 endtime+间隔

		if ($IssueLog['ci'] <= $IssueLog['unlock'] + 1) {
			$rs[] = $mo->table('tw_issue_log')->where(array('id'=>$IssueLog['id']))->save(array('status'=>1));
		}

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			echo '解冻成功！';die();
		} else {
			$mo->execute('rollback');
			echo '解冻失败！';die();
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
            default:
			case 'h':
				return '小时';
				break;

			

			case 'i':
				return '分钟';
				break;
		}
	}
}
?>