<?php
/* 应用 - 上币投票 */
namespace Home\Controller;

class VoteController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("index","info","log");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}
	}
	
	public function index($cid=NULL, $type=NULL, $num=0, $paypassword=NULL)
	{
		if (IS_POST)
		{
			if (!userid()) {
				$this->error(L('请先登录！'));
			}
			
			$num = floor($num);
			
			// 过滤非法字符----------------S
			if (checkstr($cid) || checkstr($type) || checkstr($num) || checkstr($paypassword)) {
				$this->error(L('您输入的信息有误！'));
			}
			// 过滤非法字符----------------E

			if (!check($cid, 'd')) {
				$this->error(L('ID参数错误！'));
			}
			if (($type != 1) && ($type != 2)) {
				$this->error(L('TYPE参数错误！'));
			}
			if (!check($num, 'double')) {
				$this->error(L('存币数量格式错误！'));
			}
			if (!check($paypassword, 'password')) {
				$this->error(L('交易密码格式错误！'));
			}
			
			$user = M('User')->where(array('id'=>userid()))->find();
			if (md5($paypassword) != $user['paypassword']) {
				$this->error(L('交易密码错误！'));
			}
			
			$VoteTypeData = M('VoteType')->where(array('id'=>$cid, 'status'=>1))->find();
			$vt_coinname = $VoteTypeData['coinname'];
			$vt_title = $VoteTypeData['title'];
			$vt_votecoin = $VoteTypeData['votecoin'];
			$vt_assumnum = $VoteTypeData['assumnum'];
			
			if ($num < $vt_assumnum) {
				$this->error(L('票数必须小于 1 票'));
			}
			if (10000 < $num) {
				$this->error(L('票数必须大于 10000 票'));
			}
			
			if ($VoteTypeData) {
				$userCoin = M('UserCoin')->where(array('userid'=>userid()))->find();
				if ($userCoin[$vt_votecoin] < $vt_assumnum * $num) {
					$this->error('投票所需要的 '.strtoupper($vt_votecoin).' 数量不足');
				}
			} else {
				$this->error('不存在的投票类型');
			}
			
			if (M('Vote')->where(array('userid'=>userid(), 'coinname'=>$vt_coinname))->find()) {
				$this->error(L('您已经投票过，不能再次操作！'));
			}
			
			// 判断是否需要扣除币
			if ($vt_assumnum > 0) {
				try{
					$mo = M();
					$mo->execute('set autocommit=0');
					$mo->execute('lock tables tw_user_coin write, tw_vote write, tw_finance_log write');
					$rs = array();

					$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
					/* 修改金额 */
					$rs[] = $mo->table("tw_user_coin")->where(array('userid' =>userid()))->setDec($vt_votecoin,$vt_assumnum*$num); 
					$rs[] = $mo->table("tw_vote")->add(array('userid'=>userid(), 'coinname'=>$vt_coinname, 'type'=>$type, 'num'=>$num, 'votecoin'=>$vt_votecoin, 'mum'=>$vt_assumnum*$num, 'addtime'=>time(), 'status'=>1));
					
					$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();
					
					
					// 处理资金变更日志-----------------S
					/*
					 * 操作位置（0后台，1前台） position
					 * 动作类型（参考function.php） optype
					 * 资金类型（1人民币） cointype
					 * 类型（0减少，1增加） plusminus
					 * 操作数据 amount
					 */
					$rs[] = $mo->table('tw_finance_log')->add(array('username' => session('userName'), 'adminname' => session('userName'), 'addtime' => time(), 'plusminus' => 0, 'amount' => $vt_assumnum*$num, 'optype' => 30, 'position' => 1, 'cointype' => C("coin")[$vt_votecoin]["id"], 'old_amount' => $finance_num_user_coin[$vt_votecoin], 'new_amount' => $finance_mum_user_coin[$vt_votecoin], 'userid' => session('userId'), 'adminid' => session('userId'),'addip'=>get_client_ip()));
					// 处理资金变更日志-----------------E

					if (check_arr($rs)) {
						$mo->execute('commit');
						$mo->execute('unlock tables');
						$this->success(L('投票成功！'));
					} else {
						$mo->execute('rollback');
						$this->error(APP_DEBUG ? implode('|', $rs) : L('投票失败！'));
					}
				}catch(\Think\Exception $e){
					$mo->execute('rollback');
					$mo->execute('unlock tables');
					//$this->error($e->getMessage());exit();
					$this->error(L('订单创建失败！'));
				}
			} else {
				if (M('Vote')->add(array('userid'=>userid(), 'coinname'=>$vt_coinname, 'type'=>$type, 'num'=>1, 'addtime'=>time(), 'status'=>1))) {
					$this->success(L('投票成功！'));
				} else {
					$this->error(L('投票失败！'));
				}
			}
			
		} else {
			$where['status'] = 1;
			$coin_list = M('VoteType')->where($where)->select();
			if (is_array($coin_list)) {
				foreach ($coin_list as $k => $v) {
					$v_coin = C('coin')[$v['coinname']];
					$list[$v_coin['name']]['id'] = $v['id'];
					$list[$v_coin['name']]['name'] = $v_coin['name'];
					$list[$v_coin['name']]['title'] = $v['title'];
					$list[$v_coin['name']]['zhichi'] = M('Vote')->where(array('coinname'=>$v_coin['name'], 'type'=>1))->sum('num') + $v['zhichi'];
					$list[$v_coin['name']]['fandui'] = M('Vote')->where(array('coinname'=>$v_coin['name'], 'type'=>2))->sum('num') + $v['fandui'];
					$list[$v_coin['name']]['zongji'] = $list[$v_coin['name']]['zhichi'] - $list[$v_coin['name']]['fandui'];
					$list[$v_coin['name']]['bili'] = round(($list[$v_coin['name']]['zhichi'] / $list[$v_coin['name']]['zongji']) * 100, 2);
				}

				$sort = array('direction'=>'SORT_DESC', 'field'=>'zongji');  
				$arrSort = array();  
				foreach ($list AS $uniqid => $row) {
					foreach ($row AS $key => $value) {
						$arrSort[$key][$uniqid] = $value;
					}  
				} 
				if ($sort['direction']) {
					array_multisort($arrSort[$sort['field']], constant($sort['direction']), $list);  
				}

				$this->assign('list', $list);
			}
			
			$this->assign('text', D('Text')->get_url('apps_vote'));
			$this->display();
		}
	}
	
	public function info($id)
	{
		if (!userid()) {
			$this->error(L('请先登录！'));
		}

		$id = intval($id);
		if (!$id) {
			$this->error(L("参数错误"));
		}

		$data = M("VoteType")->where(array("id" => $id))->find();
		$ret = array();
		$ret["data"] = $data;
		$ret["data"]['coinimg'] = '/Upload/coin/'.C('coin')[$data['coinname']]['img'];
		$ret["data"]['votecoininfo'] = $data['assumnum'].' '.$data['votecoin'];
		
		$this->success($ret);
	}
	
	// 投票记录
	public function log()
	{
		if (!userid()) {
			redirect(U('Login/index'));
		}

		$where['userid'] = userid();
		$count = M('Vote')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Vote')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]["coinname"] = strtoupper(C("coin")[$v['coinname']]['name']);
			if ($list[$k]['type']==1) {
				$list[$k]['type'] = '支持';
			} else {
				$list[$k]['type'] = '反对';
			}
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
}

?>