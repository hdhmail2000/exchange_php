<?php
/*
 * 部分定时任务处理
 */
namespace Home\Controller;

class RewardController extends HomeController
{
    public function index(){}
	
	// 推荐奖励处理（定时任务）
    public function RecommendHandle()
    {
		$i = 0;
		
		$where['status'] = 0;
		$where['name'] = array('like',"%注册赠送%");
		$where['addtime'] = array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day"))));
		$invit_data = M('invit')->where($where)->select();
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_invit write, tw_user_coin write');
		foreach ($invit_data as $k => $v) {
			$i++;
			$rs = array();
			
			$where_1['userid'] = $v['userid'];
			$rs[$k][] = $mo->table('tw_user_coin')->where($where_1)->setInc(strtolower($v['coin']), $v['fee']); // 修改金额
			
			$where_2['id'] = $v['id'];
			$where_2['status'] = 0;
			$rs[$k][] = $mo->table('tw_invit')->where($where_2)->save(array('status' => 1));

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				echo '操作成功！('.$i.')<br>';
			} else {
				$mo->execute('rollback');
				echo '操作失败！('.$i.')<br>';
			}
		}
		
		echo '共处理 '.$i.' 条数据';
    }
	
	// 交易佣金奖励处理（定时任务）
    public function CommissionHandle()
    {
		$i = 0;
		
		$where['status'] = 0;
		$where['name'] = array('like',array('%买入赠送%','%卖出赠送%'),'OR');
		$where['addtime'] = array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day"))));
		$invit_data = M('invit')->where($where)->select();
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_invit write, tw_user_coin write');
		foreach ($invit_data as $k => $v) {
			$i++;
			$rs = array();
			
			$where_1['userid'] = $v['invit'];
			$rs[$k][] = $mo->table('tw_user_coin')->where($where_1)->setInc(strtolower($v['coin']), $v['fee']); // 修改金额
			
			$where_2['id'] = $v['id'];
			$where_2['status'] = 0;
			$rs[$k][] = $mo->table('tw_invit')->where($where_2)->save(array('status' => 1));

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				echo '操作成功！('.$i.')<br>';
			} else {
				$mo->execute('rollback');
				echo '操作失败！('.$i.')<br>';
			}
		}
		
		echo '共处理 '.$i.' 条数据';
    }
	
	// 交易挖矿奖励处理（定时任务）
    public function MiningHandle()
    {
		$i = 0;
		
		$where['status'] = 0;
		$where['name'] = array('like',"%交易挖矿%");
		$where['addtime'] = array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day"))));
		$invit_data = M('mining')->where($where)->select();
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_mining write, tw_user_coin write');
		foreach ($invit_data as $k => $v) {
			$i++;
			$rs = array();
			
			$where_1['userid'] = $v['invit'];
			$rs[$k][] = $mo->table('tw_user_coin')->where($where_1)->setInc(strtolower($v['coin']), $v['fee']); // 修改金额
			
			$where_2['id'] = $v['id'];
			$where_2['status'] = 0;
			$rs[$k][] = $mo->table('tw_mining')->where($where_2)->save(array('status' => 1));

			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				echo '操作成功！('.$i.')<br>';
			} else {
				$mo->execute('rollback');
				echo '操作失败！('.$i.')<br>';
			}
		}
		
		echo '共处理 '.$i.' 条数据';
    }
	
	
	// 应用：理财（定时任务）
	public function FinancingHandle()
	{
		$br = (IS_CLI ? "\n" : "<br>");
		echo IS_CLI ? "" : "<pre>";
		echo "启动理财排队 : " . $br;
		$MoneyList = M("Money")->where(array("status" => 1))->select();
		//debug($MoneyList, "MoneyList");
		
		foreach ($MoneyList as $money) {
			//debug($money, "money");
			
			if ($money["endtime"] < $money["lasttime"]) {
				echo "end ok " . $br;
				$MoneyLogList = D("MoneyLog")->where(array("money_id" => $money["id"], "status" => 1))->select();

				if ($MoneyLogList) {
					$mo = M();

					foreach ($MoneyLogList as $user_money_list) {
						if (!$user_money_list["status"]) {
							continue;
						}

						$mo->execute("set autocommit=0");
						$mo->execute("lock tables tw_user_coin write,tw_money_log write,tw_money_dlog write");
						$rs = array();
						$rs[] = $mo->table("tw_user_coin")->where(array("userid" => $user_money_list["userid"]))->setInc($money["coinname"], $user_money_list["num"]);
						$rs[] = $mo->table("tw_money_log")->where(array("id" => $user_money_list["id"]))->save(array("status" => 0));
						$rs[] = $mo->table("tw_money_dlog")->add(array("userid" => $user_money_list["userid"], "money_id" => $money["id"], "log_id" => $user_money_list["id"], "type" => 1, "num" => $user_money_list["num"], "addtime" => time(), "content" => "理财结束,退回本金:" . $user_money_list["num"] . "个"));

						if (check_arr($rs)) {
							$mo->execute("commit");
							$mo->execute("unlock tables");
							echo "commit ok " . $br;
						} else {
							$mo->execute("rollback");
							echo "rollback " . $br;
						}
					}
				} else {
					D("Money")->save(array("id" => $money["id"], "status" => 0));
					D("MoneyLog")->save(array("money_id" => $money["id"], "status" => 0));
					continue;
				}
			}

			echo "项目名称：".$money["name"]." 剩余时间：".(($money["lasttime"] + $money["step"]) - time()) . " s" . $br;
			//debug(array("lasttime" => $money["lasttime"], "step" => $money["step"], "time()" => time()), "check time");

			if (!$money["lasttime"] || ($money["lasttime"] + $money["step"]) < time()) {
				echo "理财名称 " . $money["name"] . "#:" . $br;
				$mo = M();
				debug("A");
				$MoneyLogList = M("MoneyLog")->where(array("money_id" => $money["id"], "status" => 1))->select();
				debug("B");
				debug($MoneyLogList, "MoneyLogList");

				foreach ($MoneyLogList as $MoneyLog) {
					debug("C");
					debug(array($MoneyLog, $money), "chktime");

					if ($MoneyLog["chktime"] == $money["lasttime"]) {
						continue;
					}

					$mo->execute("set autocommit=0");
					$mo->execute("lock tables tw_user_coin write,tw_money_log write,tw_money_dlog write");
					$rs = array();
					
					$fee = round(($money["fee"] * $MoneyLog["num"]) / 100, 8);
					echo "update " . $MoneyLog["userid"] . " coin " . $br;
					
					$rs[] = $mo->table("tw_user_coin")->where(array("userid" => $MoneyLog["userid"]))->setInc($money["feecoin"], $fee);
					echo "update " . $MoneyLog["userid"] . " log " . $br;
					
					$MoneyLog["allfee"] = round($MoneyLog["allfee"] + $fee, 8);
					$MoneyLog["times"] = $MoneyLog["times"] + 1;
					$MoneyLog["chktime"] = $money["lasttime"];
					$rs[] = $mo->table("tw_money_log")->save($MoneyLog);
					
					echo "add " . $MoneyLog["userid"] . " dlog " . $br;
					$rs[] = $mo->table("tw_money_dlog")->add(array("userid" => $MoneyLog["userid"], "money_id" => $money["id"], "log_id" => $MoneyLog["id"], "type" => 1, "num" => $fee, "addtime" => time(), "content" => "本金:" . $money["coinname"] . " :" . $MoneyLog["num"] . "个,获取理财利息" . $money["feecoin"] . " " . $fee . "个"));

					if (check_arr($rs)) {
						$mo->execute("commit");
						$mo->execute("unlock tables");
						echo "commit ok " . $br;
					} else {
						$mo->execute("rollback");
						echo "rollback " . $br;
					}
				}

				if (D("Money")->where(array("id" => $money["id"]))->setField("lasttime", time())) {
					echo "update money last time ok" . $br;
				} else {
					echo "update money last time fail!!!!!!!!!!!!!!!!!!!!!! " . $br;
				}
			} else {
				echo "项目名称：".$money["name"]." 时间未到". $br . $br;
			}
		}
	}
	
	
	// 【所有人补发注册奖励】 慎用
    public function RegisterReissue()
    {
		// 补发调整后的奖励，刚开始活动注册送10个币，改成注册送50个币，所有人补发40个币
/*		$i = 0;
		
		$where['rt'] = array('gt',0);
		$user_data = M('user_coin')->where($where)->select();
		
		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write, tw_finance_log write, tw_user read');
		foreach ($user_data as $k => $v) {
			$i++;
			$rs = array();
			$datas = array();
			
			$datas[$k] = $mo->table('tw_user')->where(array('id' => $v['userid']))->find();
			
			// 数据未处理时的查询（原数据）
			$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => $v['userid']))->find();
			// 用户账户数据处理
			$coin_name = 'rt'; //赠送币种
			$song_num =  '40'; //赠送数量
			
			$where1['userid'] = $v['userid'];
			$rs[$k][] = $mo->table('tw_user_coin')->where($where1)->setInc($coin_name, $song_num); // 修改金额
			// 数据处理完的查询（新数据）
			$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => $v['userid']))->find();

			// optype=1 充值类型 'cointype' => 1人民币类型 'plusminus' => 1增加类型
			$rs[$k][] = $mo->table('tw_finance_log')->add(array('username' => $datas[$k]['username'], 'adminname' => session('admin_username'), 'addtime' => time(), 'plusminus' => 1, 'amount' => $song_num, 'description' => '注册赠送（补发）', 'optype' => 27, 'cointype' => 3, 'old_amount' => $finance_num_user_coin[$coin_name], 'new_amount' => $finance_mum_user_coin[$coin_name], 'userid' => $datas[$k]['id'], 'adminid' => session('admin_id'),'addip'=>get_client_ip()));
			
			
			if (check_arr($rs)) {
				$mo->execute('commit');
				$mo->execute('unlock tables');
				echo '操作成功！('.$i.')<br>';
			} else {
				$mo->execute('rollback');
				echo '操作失败！('.$i.')<br>';
			}
		}
		
		echo '共处理 '.$i.' 条数据';*/
	}
}
?>