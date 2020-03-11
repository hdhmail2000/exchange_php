<?php
/* 代理商后台 */
namespace Home\Controller;

class BackstageController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();
		/*$allow_action=array("index","mycz","myczHuikuan","myczFee","myczRes","myczChakan","myczUp","mytx","mytxUp","mytxChexiao","myzr","myzc","upmyzc","mywt","mycj","mytj","mywd","myjp","myzc_user","upmyzc_user","myyj","mydh","upmydh","invite");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}*/
		
		/* 备份：M()->table(array('tw_user'=>'a','tw_user_coin'=>'b'))->field('a.invit_1,a.invit_2,a.invit_3,b.userid')->where('b.userid = a.invit_1 OR b.userid = a.invit_2 OR b.userid = a.invit_3')->group('a.invit_1,a.invit_2,a.invit_3')->select(); */
	}
	
	public function index()
	{
		if (!userid()) {
			redirect('/Login/index.html');
		}
		
		$this->display();
	}
	
	// C2C代理商后台
	public function exchange()
	{
		echo '未开放';
	}
	
	// 做市商超级后台
	public function super()
	{
		if (!userid()) {
			redirect('/Login/index.html');
		}
		
		$backstage = M('User')->where(array('id' => userid()))->field('backstage')->find();
		if ($backstage['backstage'] == 0) {
			$this->error(L("非法操作！"));
		}
		
		// 统计所有下级注册人数
		$where_1['invit_1|invit_2|invit_3'] = userid();
		$user_info['count'] = M('user')->where($where_1)->count();
		
		$where_2['invit_1|invit_2|invit_3'] = userid();
		$where_2['addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$user_info['count_jt'] = M('user')->where($where_2)->count();
		
		$where_3['invit_1|invit_2|invit_3'] = userid();
		$where_3['addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$user_info['count_zt'] = M('user')->where($where_3)->count();
		
		// 实名认证
		$where_4['invit_1|invit_2|invit_3'] = userid();
		$map1['kyc_lv'] = 1;
		$map1['idstate'] = 2;
		$map1['_logic'] = 'or';
		$where_4['_complex'] = $map1;
		$map2['kyc_lv'] = 2;
		$map2['idstate'] = 2;
		$map2['_logic'] = 'or';
		$where_4['_complex2'] = $map2;
		$user_info['renzheng_yrz'] = M('user')->where($where_4)->count();
		
		$where_5['invit_1|invit_2|invit_3'] = userid();
		$map3['kyc_lv']  = 1;
		$map3['idstate']  = array('neq',2);
		$map3['_logic'] = 'or';
		$where_5['_complex'] = $map3;
		$map4['kyc_lv']  = 2;
		$map4['idstate']  = array('neq',2);
		$map4['_logic'] = 'or';
		$where_5['_complex2'] = $map4;
		$user_info['renzheng_wrz'] = M('user')->where($where_5)->count();
		
		$this->assign('user_info', $user_info);
		
		// 锚定货币 - 持币情况
/*		$coins[Anchor_CNY] = 0;
		$where_6['invit_1|invit_2|invit_3'] = userid();
		$user_coin_user = M('user')->where($where_6)->select();
		foreach ($user_coin_user as $k => $v) {
			$coins[Anchor_CNY] += M('user_coin')->where(array('userid'=>$v['id']))->sum(Anchor_CNY);
		}
		
		$see = M()->query('SELECT SUM(cnc) FROM `tw_user` LEFT JOIN `tw_user_coin` ON tw_user.id = tw_user_coin.userid WHERE invit_1 = 1 OR invit_3 = 1 OR invit_2 = 1'); // 原生写法
		*/
		$where_6['invit_1|invit_2|invit_3'] = userid();
		$coins[Anchor_CNY] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_user_coin` B ON A.id = B.userid')->where($where_6)->SUM('B.cnc'),4);
		
		/* 今日C2C充值 */
		$where_t_1['invit_1|invit_2|invit_3'] = userid();
		$where_t_1['B.otype'] = 1;
		$where_t_1['B.type'] = Anchor_CNY;
		$where_t_1['B.endtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_t_1['B.status'] = 3;
		$times['cnc_cz_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_exchange_order` B ON A.id = B.userid')->where($where_t_1)->SUM('B.mum'),4) * 1;
		/* 昨日C2C充值 */
		$where_t_2['invit_1|invit_2|invit_3'] = userid();
		$where_t_2['B.otype'] = 1;
		$where_t_2['B.type'] = Anchor_CNY;
		$where_t_2['B.endtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_t_2['B.status'] = 3;
		$times['cnc_cz_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_exchange_order` B ON A.id = B.userid')->where($where_t_2)->SUM('B.mum'),4) * 1;
		/* 今日C2C提现 */
		$where_t_3['invit_1|invit_2|invit_3'] = userid();
		$where_t_3['B.otype'] = 2;
		$where_t_3['B.type'] = Anchor_CNY;
		$where_t_3['B.endtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_t_3['B.status'] = 3;
		$times['cnc_tx_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_exchange_order` B ON A.id = B.userid')->where($where_t_3)->SUM('B.mum'),4) * 1;
		/* 昨日C2C提现 */
		$where_t_4['invit_1|invit_2|invit_3'] = userid();
		$where_t_4['B.otype'] = 2;
		$where_t_4['B.type'] = Anchor_CNY;
		$where_t_4['B.endtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_t_4['B.status'] = 3;
		$times['cnc_tx_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_exchange_order` B ON A.id = B.userid')->where($where_t_4)->SUM('B.mum'),4) * 1;
		/* 锚定货币 持有人数 */
		$where_p_1['invit_1|invit_2|invit_3'] = userid();
		$where_p_1['B.cnc'] = array('gt',0);
		$people[Anchor_CNY] = M()->table('tw_user A')->join('LEFT JOIN `tw_user_coin` B ON A.id = B.userid')->where($where_p_1)->count();

		
		// ETH持币情况
		$where_6['invit_1|invit_2|invit_3'] = userid();
		$coins['eth'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_user_coin` B ON A.id = B.userid')->where($where_6)->SUM('B.eth'),4);
		/* 今日ETH充值 */
		$where_t_5['invit_1|invit_2|invit_3'] = userid();
		$where_t_5['B.coinname'] = 'eth';
		$where_t_5['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_t_5['B.status'] = 1;
		$times['eth_cz_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzr` B ON A.id = B.userid')->where($where_t_5)->SUM('B.mum'),4) * 1;
		/* 昨日ETH充值 */
		$where_t_6['invit_1|invit_2|invit_3'] = userid();
		$where_t_6['B.coinname'] = 'eth';
		$where_t_6['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_t_6['B.status'] = 1;
		$times['eth_cz_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzr` B ON A.id = B.userid')->where($where_t_6)->SUM('B.mum'),4) * 1;
		/* 今日ETH提现 */
		$where_t_7['invit_1|invit_2|invit_3'] = userid();
		$where_t_7['B.coinname'] = 'eth';
		$where_t_7['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_t_7['B.status'] = 1;
		$times['eth_tx_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzc` B ON A.id = B.userid')->where($where_t_7)->SUM('B.mum'),4) * 1;
		/* 昨日ETH提现 */
		$where_t_8['invit_1|invit_2|invit_3'] = userid();
		$where_t_8['B.coinname'] = 'eth';
		$where_t_8['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_t_8['B.status'] = 1;
		$times['eth_tx_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzc` B ON A.id = B.userid')->where($where_t_8)->SUM('B.mum'),4) * 1;
		/* ETH持有人数 */
		$where_p_2['invit_1|invit_2|invit_3'] = userid();
		$where_p_2['B.eth'] = array('gt',0);
		$people['eth'] = M()->table('tw_user A')->join('LEFT JOIN `tw_user_coin` B ON A.id = B.userid')->where($where_p_2)->count();
		
		
		// SUF持币情况
		$where_6['invit_1|invit_2|invit_3'] = userid();
		$coins['suf'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_user_coin` B ON A.id = B.userid')->where($where_6)->SUM('B.suf'),4);
		/* 今日SUF充值 */
		$where_t_9['invit_1|invit_2|invit_3'] = userid();
		$where_t_9['B.coinname'] = 'suf';
		$where_t_9['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_t_9['B.status'] = 1;
		$times['suf_cz_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzr` B ON A.id = B.userid')->where($where_t_9)->SUM('B.mum'),4) * 1;
		/* 昨日SUF充值 */
		$where_t_10['invit_1|invit_2|invit_3'] = userid();
		$where_t_10['B.coinname'] = 'suf';
		$where_t_10['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_t_10['B.status'] = 1;
		$times['suf_cz_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzr` B ON A.id = B.userid')->where($where_t_10)->SUM('B.mum'),4) * 1;
		/* 今日SUF提现 */
		$where_t_11['invit_1|invit_2|invit_3'] = userid();
		$where_t_11['B.coinname'] = 'suf';
		$where_t_11['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_t_11['B.status'] = 1;
		$times['suf_tx_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzc` B ON A.id = B.userid')->where($where_t_11)->SUM('B.mum'),4) * 1;
		/* 昨日SUF提现 */
		$where_t_12['invit_1|invit_2|invit_3'] = userid();
		$where_t_12['B.coinname'] = 'suf';
		$where_t_12['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_t_12['B.status'] = 1;
		$times['suf_tx_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzc` B ON A.id = B.userid')->where($where_t_12)->SUM('B.mum'),4) * 1;
		/* SUF持有人数 */
		$where_p_3['invit_1|invit_2|invit_3'] = userid();
		$where_p_3['B.suf'] = array('gt',0);
		$people['suf'] = M()->table('tw_user A')->join('LEFT JOIN `tw_user_coin` B ON A.id = B.userid')->where($where_p_3)->count();
		
		
		// RT持币情况
		$where_7['invit_1|invit_2|invit_3'] = userid();
		$coins['rt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_user_coin` B ON A.id = B.userid')->where($where_7)->SUM('B.rt'),4);
		/* 今日RT充值 */
		$where_t_13['invit_1|invit_2|invit_3'] = userid();
		$where_t_13['B.coinname'] = 'rt';
		$where_t_13['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_t_13['B.status'] = 1;
		$times['rt_cz_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzr` B ON A.id = B.userid')->where($where_t_13)->SUM('B.mum'),4) * 1;
		/* 昨日RT充值 */
		$where_t_14['invit_1|invit_2|invit_3'] = userid();
		$where_t_14['B.coinname'] = 'rt';
		$where_t_14['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_t_14['B.status'] = 1;
		$times['rt_cz_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzr` B ON A.id = B.userid')->where($where_t_14)->SUM('B.mum'),4) * 1;
		/* 今日RT提现 */
		$where_t_15['invit_1|invit_2|invit_3'] = userid();
		$where_t_15['B.coinname'] = 'rt';
		$where_t_15['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_t_15['B.status'] = 1;
		$times['rt_tx_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzc` B ON A.id = B.userid')->where($where_t_15)->SUM('B.mum'),4) * 1;
		/* 昨日RT提现 */
		$where_t_16['invit_1|invit_2|invit_3'] = userid();
		$where_t_16['B.coinname'] = 'rt';
		$where_t_16['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_t_16['B.status'] = 1;
		$times['rt_tx_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_myzc` B ON A.id = B.userid')->where($where_t_16)->SUM('B.mum'),4) * 1;
		/* RT持有人数 */
		$where_p_4['invit_1|invit_2|invit_3'] = userid();
		$where_p_4['B.rt'] = array('gt',0);
		$people['rt'] = M()->table('tw_user A')->join('LEFT JOIN `tw_user_coin` B ON A.id = B.userid')->where($where_p_4)->count();
		
		$this->assign('coins', $coins);
		$this->assign('times', $times);
		$this->assign('people', $people);
		
		
		// ETH交易情况
		$where_trade_1['invit_1|invit_2|invit_3'] = userid();
		$where_trade_1['B.market'] = 'eth_cnc';
		$where_trade_1['B.status'] = 1;
		$trades['eth_mum'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_1)->SUM('B.mum'),4);
		/* ETH交易量 */
		$where_trade_2['invit_1|invit_2|invit_3'] = userid();
		$where_trade_2['B.market'] = 'eth_cnc';
		$where_trade_2['B.status'] = 1;
		$trades['eth_num'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_2)->SUM('B.num'),4);
		/* 今日买入ETH */
		$where_trade_3['invit_1|invit_2|invit_3'] = userid();
		$where_trade_3['B.market'] = 'eth_cnc';
		$where_trade_3['B.type'] = 1;
		$where_trade_3['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_trade_3['B.status'] = 1;
		$trades['eth_buy_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_3)->SUM('B.num'),4);
		/* 今日卖出ETH */
		$where_trade_4['invit_1|invit_2|invit_3'] = userid();
		$where_trade_4['B.market'] = 'eth_cnc';
		$where_trade_4['B.type'] = 2;
		$where_trade_4['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_trade_4['B.status'] = 1;
		$trades['eth_sell_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_4)->SUM('B.num'),4);
		/* 昨天买入ETH */
		$where_trade_5['invit_1|invit_2|invit_3'] = userid();
		$where_trade_5['B.market'] = 'eth_cnc';
		$where_trade_5['B.type'] = 1;
		$where_trade_5['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_trade_5['B.status'] = 1;
		$trades['eth_buy_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_5)->SUM('B.num'),4);
		/* 昨天卖出ETH */
		$where_trade_6['invit_1|invit_2|invit_3'] = userid();
		$where_trade_6['B.market'] = 'eth_cnc';
		$where_trade_6['B.type'] = 2;
		$where_trade_6['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_trade_6['B.status'] = 1;
		$trades['eth_sell_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_6)->SUM('B.num'),4);
		
		
		// SUF交易情况
		$where_trade_7['invit_1|invit_2|invit_3'] = userid();
		$where_trade_7['B.market'] = 'suf_cnc';
		$where_trade_7['B.status'] = 1;
		$trades['suf_mum'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_1)->SUM('B.mum'),4);
		/* SUF交易量 */
		$where_trade_8['invit_1|invit_2|invit_3'] = userid();
		$where_trade_8['B.market'] = 'suf_cnc';
		$where_trade_8['B.status'] = 1;
		$trades['suf_num'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_8)->SUM('B.num'),4);
		/* 今日买入SUF */
		$where_trade_9['invit_1|invit_2|invit_3'] = userid();
		$where_trade_9['B.market'] = 'suf_cnc';
		$where_trade_9['B.type'] = 1;
		$where_trade_9['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_trade_9['B.status'] = 1;
		$trades['suf_buy_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_9)->SUM('B.num'),4);
		/* 今日卖出SUF */
		$where_trade_10['invit_1|invit_2|invit_3'] = userid();
		$where_trade_10['B.market'] = 'suf_cnc';
		$where_trade_10['B.type'] = 2;
		$where_trade_10['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_trade_10['B.status'] = 1;
		$trades['suf_sell_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_10)->SUM('B.num'),4);
		/* 昨天买入SUF */
		$where_trade_11['invit_1|invit_2|invit_3'] = userid();
		$where_trade_11['B.market'] = 'suf_cnc';
		$where_trade_11['B.type'] = 1;
		$where_trade_11['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_trade_11['B.status'] = 1;
		$trades['suf_buy_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_11)->SUM('B.num'),4);
		/* 昨天卖出SUF */
		$where_trade_12['invit_1|invit_2|invit_3'] = userid();
		$where_trade_12['B.market'] = 'suf_cnc';
		$where_trade_12['B.type'] = 2;
		$where_trade_12['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_trade_12['B.status'] = 1;
		$trades['suf_sell_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_12)->SUM('B.num'),4);
		
		
		// RT交易情况
		$where_trade_13['invit_1|invit_2|invit_3'] = userid();
		$where_trade_13['B.market'] = 'rt_cnc';
		$where_trade_13['B.status'] = 1;
		$trades['rt_mum'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_13)->SUM('B.mum'),4);
		/* RT交易量 */
		$where_trade_14['invit_1|invit_2|invit_3'] = userid();
		$where_trade_14['B.market'] = 'rt_cnc';
		$where_trade_14['B.status'] = 1;
		$trades['rt_num'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_14)->SUM('B.num'),4);
		/* 今日买入RT */
		$where_trade_15['invit_1|invit_2|invit_3'] = userid();
		$where_trade_15['B.market'] = 'rt_cnc';
		$where_trade_15['B.type'] = 1;
		$where_trade_15['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_trade_15['B.status'] = 1;
		$trades['rt_buy_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_15)->SUM('B.num'),4);
		/* 今日卖出RT */
		$where_trade_4['invit_1|invit_2|invit_3'] = userid();
		$where_trade_4['B.market'] = 'rt_cnc';
		$where_trade_4['B.type'] = 2;
		$where_trade_4['B.addtime'] = array('EGT',strtotime(date('Y-m-d 0:0:0')));
		$where_trade_4['B.status'] = 1;
		$trades['rt_sell_jt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_4)->SUM('B.num'),4);
		/* 昨天买入RT */
		$where_trade_16['invit_1|invit_2|invit_3'] = userid();
		$where_trade_16['B.market'] = 'rt_cnc';
		$where_trade_16['B.type'] = 1;
		$where_trade_16['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_trade_16['B.status'] = 1;
		$trades['rt_buy_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_16)->SUM('B.num'),4);
		/* 昨天卖出RT */
		$where_trade_17['invit_1|invit_2|invit_3'] = userid();
		$where_trade_17['B.market'] = 'rt_cnc';
		$where_trade_17['B.type'] = 2;
		$where_trade_17['B.addtime'] = array( array('EGT',strtotime(date('Y-m-d 0:0:0',strtotime("-1 day")))),array('ELT',strtotime(date('Y-m-d 23:59:59',strtotime("-1 day")))) );
		$where_trade_17['B.status'] = 1;
		$trades['rt_sell_zt'] = round(M()->table('tw_user A')->join('LEFT JOIN `tw_trade_log` B ON (A.id = B.userid OR A.id = B.peerid)')->where($where_trade_17)->SUM('B.num'),4);
		
		$this->assign('trades', $trades);
		$this->display();
	}
}
?>