<?php
namespace Admin\Controller;

class ExchangeController extends AdminController
{
	public function index()
	{
		$this->display();
	}
	
	// C2C充值记录
	public function mycz($name = NULL, $status = NULL)
	{
		$where = array();
		/* 用户名--条件 */
		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else {
				$where[$field] = $name;
			}
		}
		// 默认状态
		if ($status == "") {
			$where['_string'] = '(status = 1 OR status = 2)';
		}
		/* 状态--条件 */
		if ($status != '99') {
			if ($status) {
				$where['status'] = $status;
			}
		}
		
		// 订单统计
		$tongji['dcl'] = M('exchange_order')->where(array('otype'=>1,'status'=>1))->sum('mum') * 1;
		$tongji['ywc'] = M('exchange_order')->where(array('otype'=>1,'status'=>3))->sum('mum') * 1;
		$tongji['cx'] = M('exchange_order')->where(array('otype'=>1,'status'=>8))->sum('mum') * 1;
		$this->assign('tongji', $tongji);
		
		$where['otype'] = 1; // 订单类型

		$count = M('exchange_order')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		
		if ($status == 1 || $status == NULL) {
			$list = M('exchange_order')->where($where)->order('id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		} else {
			$list = M('exchange_order')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		}
		
		foreach ($list as $k => $v) {
			$aids = '';
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$aids = M('exchange_agent')->where(array('id' => $v['aid']))->field("id,aid")->find();
			$list[$k]['agent'] = M('User')->where(array('id' => $aids['aid']))->getField('username');
		}
		
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	// C2C充值数据更新
	public function myczQueren()
	{
		$id = $_GET['id'];

		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		$mycz = M('exchange_order')->where(array('id' => $id))->find();
		if ($mycz['status'] != 0 && $mycz['status'] != 1 && $mycz['status'] != 2) {
			$this->error('已经处理，禁止再次操作！');
		}
		
		$fp = fopen("lockcz.txt", "w+");
		if (flock($fp,LOCK_EX | LOCK_NB))
		{
			$mo = M();
			$mo->execute('set autocommit=0');
			$mo->execute('lock tables tw_user_coin write,tw_exchange_order write,tw_finance write,tw_finance_log write,tw_user read, tw_exchange_config read, tw_market read');
			
			$types = $mycz['type']; //充值类型
			$typed = $mycz['type'].'d'; //充值类型，冻结
			$nums = $mycz['num']; //充值数量
			$mums = $mycz['mum']; //实际充值数量
			
			$rs = array();
			$finance = $mo->table('tw_finance')->where(array('userid' => $mycz['userid']))->order('id desc')->find();

			// 数据未处理时的查询（原数据）
			$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => $mycz['userid']))->find();
			// 用户账户数据处理
			$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $mycz['userid']))->setInc($types, $mums);
			$rs[] = $mo->table('tw_exchange_order')->where(array('id' => $mycz['id']))->save(array('status' => 3, 'endtime' => time()));
			// 数据处理完的查询（新数据）
			$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => $mycz['userid']))->find();
			$finance_hash = md5($mycz['userid'] . $finance_num_user_coin[$types] . $finance_num_user_coin[$typed] . $mums . $finance_mum_user_coin[$types] . $finance_mum_user_coin[$typed] . MSCODE);
			
			$finance_num = $finance_num_user_coin[$types] + $finance_num_user_coin[$typed];
			if ($finance['num'] < $finance_num) {
				$finance_status = (1 < ($finance_num - $finance['num']) ? 0 : 1);
			} else {
				$finance_status = (1 < ($finance['num'] - $finance_num) ? 0 : 1);
			}
			
			// 处理资金变更日志-----------------S
			
			$rs[] = $mo->table('tw_finance')->add(array('userid' => $mycz['userid'], 'coinname' => $types, 'num_a' => $finance_num_user_coin[$types], 'num_b' => $finance_num_user_coin[$typed], 'num' => $finance_num_user_coin[$types] + $finance_num_user_coin[$typed], 'fee' => $mums, 'type' => 1, 'name' => 'mycz_c2c', 'nameid' => $mycz['id'], 'remark' => 'C2C充值'.$types.'-人工到账', 'mum_a' => $finance_mum_user_coin[$types], 'mum_b' => $finance_mum_user_coin[$typed], 'mum' => $finance_mum_user_coin[$types] + $finance_mum_user_coin[$typed], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
			
			// 获取用户信息
			$user_info = $mo->table('tw_user')->where(array('id' => $mycz['userid']))->find();
			// optype=1 充值类型 'cointype' => 1人民币类型 'plusminus' => 1增加类型
			$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => session('admin_username'), 'addtime' => time(), 'plusminus' => 1, 'amount' => $mums, 'optype' => 1, 'cointype' => 1, 'old_amount' => $finance_num_user_coin[$types], 'new_amount' => $finance_mum_user_coin[$types], 'userid' => $user_info['id'], 'adminid' => session('admin_id'),'addip'=>get_client_ip()));
			
			// 处理资金变更日志-----------------E
			
			
			// 首次充值赠送币
			$configs = M('exchange_config')->where(array('id' => 1))->find();
			$qbsong_num = $configs['xnb_mr_song_tiaojian']; // 充值条件，满足此金额奖励才能执行。
			$coin_name = $configs['xnb_mr_song']; // 赠送币种
			// 查询市场最新成交价
			$markets = M('market')->where(array('name'=>$coin_name.'_'.Anchor_CNY))->field('new_price')->find();
			if ($markets['new_price']) {$new_price = $markets['new_price'];} else {$new_price = 0.001;}
			$user_coin_num = ($mums * ($configs['xnb_mr_song_num'] / 100)) / $new_price; //赠送数量（（充值金额*(赠送比例/100)）/ 赠送币种当前价格）
			
			if ($configs['give_type'] == 1 && $configs['xnb_mr_song_num'] > 0 && $nums >= $qbsong_num)
			{
				if (!(M('finance_log')->where(array('userid'=>$mycz['userid'],'description'=>array('like',"%首次充值赠送%")))->find())) {
					// 判断是否首次充值赠送
					if ($configs['grant_type'] == 1) {
						/* 锁定发放奖励 */
					} else {
						/* 直接发放奖励 */
						$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $mycz['userid']))->setInc($coin_name, $user_coin_num);
						
						// 处理资金变更日志-----------------S
						$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => session('admin_username'), 'addtime' => time(), 'plusminus' => 1, 'amount' => $user_coin_num, 'description' => '首次充值赠送'.$coin_name, 'optype' => 28, 'cointype' => 3, 'old_amount' => $finance_num_user_coin[$coin_name], 'new_amount' => $finance_mum_user_coin[$coin_name]+$user_coin_num, 'userid' => $user_info['id'], 'adminid' => session('admin_id'),'addip'=>get_client_ip()));
						// 处理资金变更日志-----------------E
					}
				}
			} else if ($configs['give_type'] == 2 && $configs['xnb_mr_song_num'] > 0 && $nums >= $qbsong_num) {
				// 判断是否每次充值赠送
				if ($configs['grant_type'] == 1) {
					/* 锁定发放奖励 */
				} else {
					/* 直接发放奖励 */
					$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $mycz['userid']))->setInc($coin_name, $user_coin_num);
					
					// 处理资金变更日志-----------------S
					$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => session('admin_username'), 'addtime' => time(), 'plusminus' => 1, 'amount' => $user_coin_num, 'description' => '充值赠送'.$coin_name, 'optype' => 28, 'cointype' => 3, 'old_amount' => $finance_num_user_coin[$coin_name], 'new_amount' => $finance_mum_user_coin[$coin_name]+$user_coin_num, 'userid' => $user_info['id'], 'adminid' => session('admin_id'),'addip'=>get_client_ip()));
					// 处理资金变更日志-----------------E
				}
			}
			

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				$message="操作成功";
				$res=1;
			} else {
				$mo->execute('rollback');
				$message="操作失败";
				$res=0;
			}
			flock($fp,LOCK_UN);
		} else {
			$message="请不要重复提交";
			$res=0;
		}
		fclose($fp);
		if ($res == 1) {
			$this->success($message);
		} else {
			$this->error($message);
		}
	}

	// C2C充值处理
	public function myczChuli()
	{
		$id = $_GET['id'];
		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		if (M('exchange_order')->where(array('id' => $id))->save(array('status' => 2,'endtime' => time()))) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}
	
	// C2C充值撤销
	public function myczChexiao()
	{
		$id = $_GET['id'];
		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		if (M('exchange_order')->where(array('id' => $id))->save(array('status' => 8,'endtime' => time()))) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}
	
	// C2C提现记录
	public function mytx($name = NULL, $status = NULL)
	{
		$where = array();
		/* 用户名--条件 */
		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else {
				$where[$field] = $name;
			}
		}
		// 默认状态
		if ($status == "") {
			$where['_string'] = '(status = 1 OR status = 2)';
		}
		/* 状态--条件 */
		if ($status != '99') {
			if ($status) {
				$where['status'] = $status;
			}
		}
		
		// 订单统计
		$tongji['dcl'] = M('exchange_order')->where(array('otype'=>2,'status'=>1))->sum('mum') * 1;
		$tongji['ywc'] = M('exchange_order')->where(array('otype'=>2,'status'=>3))->sum('mum') * 1;
		$tongji['cx'] = M('exchange_order')->where(array('otype'=>2,'status'=>8))->sum('mum') * 1;
		$this->assign('tongji', $tongji);
		
		$where['otype'] = 2; // 订单类型

		$count = M('exchange_order')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		
		if ($status == 1 || $status == NULL) {
			$list = M('exchange_order')->where($where)->order('id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		} else {
			$list = M('exchange_order')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		}
		
		foreach ($list as $k => $v) {
			$matchs ='';
			$aids = '';
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$aids = M('exchange_agent')->where(array('id' => $v['aid']))->field("id,aid")->find();
			$list[$k]['agent'] = M('User')->where(array('id' => $aids['aid']))->getField('username');
			preg_match('/([\d]{4})([\d]{4})([\d]{4})([\d]{4})([\d]{0,})?/',$v['bankcard'],$match);
			foreach ($match as $kb => $vo) { if($kb == 0){}else{$matchs .= $vo.' ';} }
			
			$list[$k]['bankname'] = '姓名：'.$v['truename'].'<br>'.'银行名称：'.$v['bank'].'<br>'.'银行账号：<b style="font-size:15px;color:#3498db;">'.$matchs.'</b><br>'.'开户行：'.$v['bankprov'].' - '.$v['bankcity'].' - '.$v['bankaddr'];
		}
		
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	// C2C提现数据更新
	public function mytxQueren()
	{
		$id = $_GET['id'];
		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		if (M('exchange_order')->where(array('id' => $id))->save(array('status' => 3))) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}
	
	// C2C提现处理
	public function mytxChuli()
	{
		$id = $_GET['id'];
		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		if (M('exchange_order')->where(array('id' => $id))->save(array('status' => 2,'endtime' => time()))) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}
	
	// C2C提现撤销
	public function mytxChexiao()
	{
		$id = $_GET['id'];
		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		$mytx = M('exchange_order')->where(array('id' => trim($_GET['id'])))->find();

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write,tw_exchange_order write,tw_finance write,tw_finance_log write,tw_user read');

		$types = $mytx['type']; //提现类型
		$typed = $mytx['type'].'d'; //提现类型，冻结
		
		$rs = array();
		$finance = $mo->table('tw_finance')->where(array('userid' => $mytx['userid']))->order('id desc')->find();

		// 数据未处理时的查询（原数据）
		$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => $mytx['userid']))->find();
		// 用户账户数据处理
		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $mytx['userid']))->setInc($types, $mytx['num']); // 修改金额
		$rs[] = $mo->table('tw_exchange_order')->where(array('id' => $mytx['id']))->setField('status', 8);
		// 数据处理完的查询（新数据）
		$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => $mytx['userid']))->find();
		$finance_hash = md5($mytx['userid'] . $finance_num_user_coin[$types] . $finance_num_user_coin[$typed] . $mytx['num'] . $finance_mum_user_coin[$types] . $finance_mum_user_coin[$typed] . MSCODE . 'tp3.net.cn');
		
		$finance_num = $finance_num_user_coin[$types] + $finance_num_user_coin[$typed];
		if ($finance['mum'] < $finance_num) {
			$finance_status = (1 < ($finance_num - $finance['mum']) ? 0 : 1);
		} else {
			$finance_status = (1 < ($finance['mum'] - $finance_num) ? 0 : 1);
		}

		// 处理资金变更日志-----------------S
		
		$rs[] = $mo->table('tw_finance')->add(array('userid' => $mytx['userid'], 'coinname' => $types, 'num_a' => $finance_num_user_coin[$types], 'num_b' => $finance_num_user_coin[$typed], 'num' => $finance_num_user_coin[$types] + $finance_num_user_coin[$typed], 'fee' => $mytx['num'], 'type' => 1, 'name' => 'mytx_c2c', 'nameid' => $mytx['id'], 'remark' => 'C2C提现'.$types.'-撤销提现', 'mum_a' => $finance_mum_user_coin[$types], 'mum_b' => $finance_mum_user_coin[$typed], 'mum' => $finance_mum_user_coin[$types] + $finance_mum_user_coin[$typed], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance_status));
		
		// 获取用户信息
		$user_info = $mo->table('tw_user')->where(array('id' => $mytx['userid']))->find();
		// optype=4 提现撤销-动作类型 'cointype' => 1人民币-资金类型 'plusminus' => 1增加类型
		$rs[] = $mo->table('tw_finance_log')->add(array('username' => $user_info['username'], 'adminname' => session('admin_username'), 'addtime' => time(), 'plusminus' => 1, 'amount' => $mytx['num'], 'optype' => 24, 'cointype' => 1, 'old_amount' => $finance_num_user_coin[$types], 'new_amount' => $finance_mum_user_coin[$types], 'userid' => $user_info['id'], 'adminid' => session('admin_id'),'addip'=>get_client_ip()));
		
		// 处理资金变更日志-----------------E

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('操作成功！');
		} else {
			$mo->execute('rollback');
			$this->error('操作失败！');
		}
	}
	
	
	// C2C配置
	public function config()
	{
		$this->data = M('exchange_config')->where(array('id' => 1))->find();
		
		$this->display();
	}
	
	public function configedit()
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}
		
		header('Content-Type:text/html;charset=UTF-8');
        //$data = I('post.');
		$_POST['mycz_prompt'] = htmlspecialchars($_POST['mycz_prompt']);
		$_POST['mytx_prompt'] = htmlspecialchars($_POST['mytx_prompt']);
	
		
		if (M('exchange_config')->where(array('id' => 1))->save($_POST)) {
			$this->success('修改成功！');
		} else {
			$this->error('修改失败');
		}
	}
	
	// C2C代理商
	public function agent()
	{
		$where = array();
		/* 用户名--条件 */
		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else {
				$where[$field] = $name;
			}
		}
		/* 状态--条件 */
		if ($status) {
			$where['status'] = $status - 1;
		}
		/* 时间--条件 */
		if (!empty($starttime) && empty($endtime)) {
			$starttime = strtotime($starttime);
			$where[$time_type] = array('EGT',$starttime);

		} else if (empty($starttime) && !empty($endtime)) {
			$endtime = strtotime($endtime);
			$where[$time_type] = array('ELT',$endtime);

		} else if (!empty($starttime) && !empty($endtime)) {
			$starttime = strtotime($starttime);
			$endtime = strtotime($endtime);
			$where[$time_type] =  array(array('EGT',$starttime),array('ELT',$endtime));
		}


		$count = M('exchange_agent')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('exchange_agent')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$i=0;
		foreach ($list as $k => $v) {
			$i++;
			$list[$k]['username'] = M('User')->where(array('id' => $v['aid']))->getField('username');
			preg_match('/([\d]{4})([\d]{4})([\d]{4})([\d]{4})([\d]{0,})?/',$v['bankcard'],$match);
			foreach ($match as $kb => $vo) { if($kb == 0){}else{$matchs[$i] .= $vo.' ';} }
			
			$list[$k]['bankinfo'] = '银行名称：'.$v['bank'].'<br>'.'银行账号：<b style="font-size:15px;color:#3498db;">'.$matchs[$i].'</b><br>'.'开户行：'.$v['bankprov'].' - '.$v['bankcity'].' - '.$v['bankaddr'];
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		
		$this->display();
	}
	
	// C2C代理商 - 新增
	public function agentEdit($id = NULL)
	{
		if (empty($_POST)) {
			$liste = '';
			
			if ($id) {
				$this->data = M('exchange_agent')->where(array('id' => trim($id)))->find();
			} else {
				$this->data = null;
			}
			
			$this->display();
		} else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			if ($_POST['id']) {
				$rs = M('exchange_agent')->save($_POST);
			} else {
				$_POST['addtime'] = time();
				$rs = M('exchange_agent')->add($_POST);
			}

			if ($rs) {
				$this->success('编辑成功！');
			} else {
				$this->error('编辑失败！');
			}
		}	
	}

	public function agentStatus($id = NULL, $type = NULL, $mobile = 'exchange_agent')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}
		if (empty($type)) {
			$this->error('参数错误2！');
		}

		if (strpos(',', $id)) {
			$id = implode(',', $id);
		}
		
		$where['id'] = array('in', $id);

		switch (strtolower($type)) {
			case 'forbid':
				$data = array('status' => 0);
				break;

			case 'resume':
				$data = array('status' => 1);
				break;

			case 'repeal':
				$data = array('status' => 2, 'endtime' => time());
				break;

			case 'del':
				$data = array('status' => -1);
				break;

			case 'delete':
				if (M($mobile)->where($where)->delete()) {
					$this->success('操作成功！');
				} else {
					$this->error('操作失败！');
				}
				break;

			default:
				$this->error('非法参数！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}
}
?>