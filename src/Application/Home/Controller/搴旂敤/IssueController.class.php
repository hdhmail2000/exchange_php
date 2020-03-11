<?php
/* 应用 - ICO众筹 */
namespace Home\Controller;

class IssueController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","buy","log","upbuy","unlock");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}
	}

	public function index()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$where['status'] = array('neq', 0);
		$Model = M('Issue');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 2);
		$show = $Page->show();
		//$list = $Model->fetchSql()->where($where)->order('addtime desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		$list = $Model->where($where)->order('tuijian asc,paixu desc,addtime desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		$tuijian = $Model->where(array("tuijian"=>1))->order("addtime desc")->limit(1)->find();
		if($tuijian){

			$tuijian['coinname'] = C('coin')[$tuijian['coinname']]['title'];
			$tuijian['buycoin']  = C('coin')[$tuijian['buycoin']]['title'];
			$tuijian['bili']     = round(($tuijian['deal'] / $tuijian['num']) * 100, 2);
			$tuijian['content']  = mb_substr(clear_html($tuijian['content']),0,350);

			$end_ms = strtotime($tuijian['time'])+$tuijian['tian']*3600*24;
			$begin_ms = strtotime($tuijian['time']);

			// $tuijian['beginTime'] = date("Y-m-d H:i:s",$begin_ms);
			$tuijian['beginTime'] = date("Y-m-d",$begin_ms);
			// $tuijian['endTime']   = date("Y-m-d H:i:s",$end_ms);
			$tuijian['endTime']   = date("Y-m-d",$end_ms);

			$tuijian['zhuangtai'] = "进行中" ;

			if($begin_ms>time()){
				$tuijian['zhuangtai'] = "尚未开始";//未开始
			}


			if($tuijian['num']<=$tuijian['deal']){
				$tuijian['zhuangtai'] =  "已结束";//已结束
			}



			if($end_ms<time()){
				$tuijian['zhuangtai'] = "已结束";//已结束
			}

			$tuijian['rengou']="";
			if($tuijian['zhuangtai'] == "进行中"){
				$tuijian['rengou']="<a href='/Issue/buy/id/".$tuijian['id'].".html'>立即认购</a>";
			}
		}

		$list_jinxing = array();
		$list_yure	  = array();
		$list_jieshu  = array();


		foreach ($list as $k => $v) {
			//$list[$k]['img'] = M('Coin')->where(array('name' => $v['coinname']))->getField('img');

			$list[$k]['bili'] = round(($v['deal'] / $v['num']) * 100, 2);
			$list[$k]['endtime'] = date("Y-m-d H:i:s",strtotime($v['time'])+$v['tian']*3600*24);

			$list[$k]['coinname'] = C('coin')[$v['coinname']]['title'];
			$list[$k]['buycoin']  = C('coin')[$v['buycoin']]['title'];
			$list[$k]['bili']     = round(($v['deal'] / $v['num']) * 100, 2);
			// $list[$k]['content']  = mb_substr(clear_html($v['content']),0,350,'utf-8');
			$list[$k]['content']  = mb_substr(clear_html($v['content']),0,350,'utf-8');
			$list[$k]['content2']  = mb_substr(clear_html($v['content']),0,50,'utf-8');
			$dhbli2=1/$list[$k]['price'];
			$list[$k]['duihuan'] = '1'.' '.strtoupper($list[$k]['buycoin']).'='.$dhbli2.' '.strtoupper($list[$k]['coinname']);

			$end_ms = strtotime($v['time'])+$v['tian']*3600*24;
			$begin_ms = strtotime($v['time']);


			$list[$k]['beginTime'] = date("Y-m-d ",$begin_ms);
			// $list[$k]['beginTime'] = date("Y-m-d H:i:s",$begin_ms);
			$list[$k]['endTime']   = date("Y-m-d",$end_ms);
			// $list[$k]['endTime']   = date("Y-m-d H:i:s",$end_ms);

			$list[$k]['zhuangtai'] = "进行中" ;
			$list[$k]['statuss'] =1;//进行中

			if($begin_ms>time()){
				$list[$k]['zhuangtai'] = "尚未开始";//未开始
				$list[$k]['statuss'] =0;//尚未开始
			}



			if($list[$k]['num']<=$list[$k]['deal']){
				$list[$k]['zhuangtai'] =  "已结束";//已结束
				$list[$k]['statuss'] =2;//已结束
			}

			if($end_ms<time()){
				$list[$k]['zhuangtai'] = "已结束";//已结束
				$list[$k]['statuss'] =2;//已结束
			}

			switch($list[$k]['zhuangtai']){
				case "尚未开始":
					$list_yure[] = $list[$k];
					break;
				case "进行中":
					$list_jinxing[] = $list[$k];
					break;
				case "已结束":
					$list_jieshu[] = $list[$k];
					break;
			}
		}

		//var_dump($list_jieshu);
		if(!$tuijian){
			$show=0;
		}else{
			$show=1;
		}
		$dhbli=1/$tuijian['price'];

		$tuijian['duihuan'] = '1'.' '.strtoupper($tuijian['buycoin']).'='.$dhbli.' '.strtoupper($tuijian['coinname']);

		// var_dump($tuijian);die;
		$this->assign('show', $show);
		$this->assign('tuijian', $tuijian);
		$this->assign('list_yure', $list_yure);
		$this->assign('list_jinxing', $list_jinxing);
		$this->assign('list_jieshu', $list_jieshu);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function buy($id=1)
	{
		// 过滤非法字符----------------S
		if (checkstr($id)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E
		if (!userid()) {
			redirect('/Login/index');
		}

		// if (!check($id, 'd')) {
		// 	$this->error('参数错误！');
		// }
		if($id==1){
			$id=M('Issue')->order('id asc')->limit(1)->getField('id');
		}

		$Issue = M('Issue')->where(array('id' => $id))->find();
		$Issue['bili'] = round(($Issue['deal'] / $Issue['num']) * 100, 2);
		$user_coin = M('UserCoin')->where(array('userid' => userid()))->find();
		$this->assign('user_coin', $user_coin);
		$end_ms = strtotime($Issue['time'])+$Issue['tian']*3600*24;
		$begin_ms = strtotime($Issue['time']);
		if (!$Issue) {
			$this->error('认购错误！');
		}
		$Issue['status'] = 1 ;

		if($begin_ms>time()){
			$Issue['status'] = 2;//未开始
		}


		if($Issue['num']==$Issue['deal']){
			$Issue['status'] = 0;//已结束
		}



		if($end_ms<time()){
			$Issue['status'] = 0;//已结束
		}
		$Issue['endtime'] = date("Y-m-d H:i:s",strtotime($Issue['time'])+$Issue['tian']*3600*24);

		$dhbli=1/$Issue['price'];
		$Issue['duihuan'] = '1'.' '.strtoupper($Issue['buycoin']).'='.$dhbli.' '.strtoupper($Issue['coinname']);


		$Issue['img'] = M('Coin')->where(array('name' => $Issue['coinname']))->getField('img');
		// echo ($Issue['price']);
		// $Issue['price'] = $Issue['price']*1;
		$this->assign('issue', $Issue);

		$this->display();

	}

	public function log($ls = 15)
	{
		// 过滤非法字符----------------S
		if (checkstr($ls)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E

		if (!userid()) {
			redirect('/Login/index');
		}

		$this->assign('prompt_text', D('Text')->get_content('game_issue_log'));

		$where['status'] = array('egt', 0);
		$where['userid'] = userid();

		$IssueLog = M('IssueLog');
		$count = $IssueLog->where($where)->count();
		$Page = new \Think\Page($count, $ls);
		$show = $Page->show();
		$list = $IssueLog->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['shen'] = round((($v['ci'] - $v['unlock']) * $v['num']) / $v['ci'], 6);
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function upbuy($id, $num, $paypassword)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($num) || checkstr($paypassword)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E
		if (!userid()) {
			redirect('/Login/index');
		}
		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		if (!check($num, 'd')) {
			$this->error('认购数量格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$User = M('User')->where(array('id' => userid()))->find();
		if (!$User['paypassword']) {
			$this->error('交易密码非法！');
		}
		if (md5($paypassword) != $User['paypassword']) {
			$this->error('交易密码错误！');
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

		$issue_min = ($Issue['min'] ? $Issue['min'] : 9.9999999999999995E-7);
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
		//判断是否新注册 是否推荐了别人注册 做出认购限制 开始

		$Userss = M('User')->where(array('invit_1' => userid()))->select();

		$cur_rg_max = 0 ;

		if(!$Userss){

			$cur_rg_max = C('new_max_rg');

			if($mum > $cur_rg_max){

					$this->error('新注册最高认购 '. $cur_rg_max .' 元');
				}

		}else{

			$cur_rg_max = intval(C('new_tui_add_rg')) * count($Userss);

			if($mum > $cur_rg_max){

					$this->error('您最高可认购 '. $cur_rg_max .' 元');
				}

		}

		//判断是否新注册 是否推荐了别人注册 做出认购限制 结束

		*/

		if (!$mum) {
			$this->error('认购总额错误');
		}

		$buycoin = M('UserCoin')->where(array('userid' => userid()))->getField($Issue['buycoin']);
		if ($buycoin < $mum) {
			$this->error('可用' . C('coin')[$Issue['buycoin']]['title'] . '余额不足');
		}

		$issueLog = M('IssueLog')->where(array('userid' => userid(), 'coinname' => $Issue['coinname']))->sum('num');
		if ($Issue['limit'] < ($issueLog + $num)) {
			$this->error('认购总数量超过最大限制' . $Issue['limit']);
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
		$mo->execute('lock tables tw_invit write ,  tw_user_coin write  , tw_issue write  , tw_issue_log  write ,tw_finance write');

		$rs = array();

		$finance = $mo->table('tw_finance')->where(array('userid' => userid()))->order('id desc')->find();
		$finance_num_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();

		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setDec($Issue['buycoin'], $mum);

		// $rs[] = $finance_nameid = $mo->table('tw_issue_log')->add(array('userid' => userid(), 'coinname' => $Issue['coinname'], 'buycoin' => $Issue['buycoin'], 'name' => $Issue['name'], 'price' => $Issue['price'], 'num' => $num, 'mum' => $mum, 'ci' => $Issue['ci'], 'jian' => $Issue['jian'], 'unlock' => 1, 'addtime' => time(), 'endtime' => time(), 'status' => $Issue['ci'] == 1 ? 1 : 0));//原本购买就解冻一次,释放到钱包

		//endtime(下次解冻时间)=结束时间+间隔时间
		$rs[] = $finance_nameid = $mo->table('tw_issue_log')->add(array('userid' => userid(), 'coinname' => $Issue['coinname'], 'buycoin' => $Issue['buycoin'], 'name' => $Issue['name'], 'price' => $Issue['price'], 'num' => $num, 'mum' => $mum, 'ci' => $Issue['ci'], 'jian' => $Issue['jian'], 'unlock' => 0, 'addtime' => time(), 'endtime' => ($Issue['endtime']+ (60 * 60 * $Issue['jian'])), 'status' => 0));

		$finance_mum_user_coin = $mo->table('tw_user_coin')->where(array('userid' => userid()))->find();

		$finance_hash = md5(userid() . $finance_num_user_coin['cny'] . $finance_num_user_coin['cnyd'] . $mum . $finance_mum_user_coin['cny'] . $finance_mum_user_coin['cnyd'] . MSCODE . 'tp3.net.cn');

		$rs[] = $mo->table('tw_finance')->add(array('userid' => userid(), 'coinname' => 'cny', 'num_a' => $finance_num_user_coin['cny'], 'num_b' => $finance_num_user_coin['cnyd'], 'num' => $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'], 'fee' => $num, 'type' => 2, 'name' => 'issue', 'nameid' => $finance_nameid, 'remark' => '认购中心-立即认购', 'mum_a' => $finance_mum_user_coin['cny'], 'mum_b' => $finance_mum_user_coin['cnyd'], 'mum' => $finance_mum_user_coin['cny'] + $finance_mum_user_coin['cnyd'], 'move' => $finance_hash, 'addtime' => time(), 'status' => $finance['mum'] != $finance_num_user_coin['cny'] + $finance_num_user_coin['cnyd'] ? 0 : 1));

		// $rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setInc($Issue['coinname'], $jd_num);//原本购买就解冻一次,释放到钱包,取消这个解冻

		$rs[] = $mo->table('tw_issue')->where(array('id' => $id))->setInc('deal', $num);

		if ($Issue['num'] <= $Issue['deal']) {
			$rs[] = $mo->table('tw_issue')->where(array('id' => $id))->setField('status', 0);
		}

		if ($User['invit_1'] && $Issue['invit_1']) {
			$invit_num_1 = round(($mum / 100) * $Issue['invit_1'], 6);
			if ($invit_num_1) {
				$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $User['invit_1']))->setInc($Issue['invit_coin'], $invit_num_1);
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $User['invit_1'], 'invit' => userid(), 'name' => $Issue['name'], 'type' => '一代认购赠送', 'num' => $num, 'mum' => $mum, 'fee' => $invit_num_1, 'addtime' => time(), 'status' => 1,'coin'=>strtoupper($Issue['invit_coin'])));
			}
			/*
			//直系下属认购额奖励开始
			$invit_num_1s = round(($mum / 100) * intval(C('tui_rg_jl')), 6);
			if ($invit_num_1s) {
				$rs[] = $mo->table('tw_user_coin')->where(array('userid' => $User['invit_1']))->setInc($Issue['invit_coin'], $invit_num_1s);
				$rs[] = $mo->table('tw_invit')->add(array('userid' => $User['invit_1'], 'invit' => userid(), 'name' => $Issue['name'], 'type' => '直系下属认购奖励', 'num' => $num, 'mum' => $mum, 'fee' => $invit_num_1s, 'addtime' => time(), 'status' => 1,'coin'=>strtoupper($Issue['invit_coin'])));
			}
			//直系下属认购额奖励结束
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
			$this->error('您输入的信息有误！');
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
			$this->error('当前解冻已完成！');
		}

		if ($IssueLog['ci'] <= $IssueLog['unlock']) {
			$this->error('非法访问！');
		}
		
		// $tm = $IssueLog['endtime'] + (60 * 60 * $IssueLog['jian']);
		$tm = $IssueLog['endtime'];
		if (time() < $tm) {
			$this->error('尚未到解冻时间!<br>请在【' . date('Y-m-d',($tm)) . '】之后再次操作');
			// $this->error('尚未到解冻时间!<br>请在【' . addtime($tm) . '】<br>之后再次操作');
		}

		if ($IssueLog['userid'] != userid()) {
			$this->error('非法访问');
		}


		$jd_num = round($IssueLog['num'] / $IssueLog['ci'], 6);

		$mo = M();
		$mo->execute('set autocommit=0');
		$mo->execute('lock tables tw_user_coin write  , tw_issue_log write ');

		$rs = array();
		$rs[] = $mo->table('tw_user_coin')->where(array('userid' => userid()))->setInc($IssueLog['coinname'], $jd_num);
		$rs[] = $mo->table('tw_issue_log')->where(array('id' => $IssueLog['id']))->save(array('unlock' => $IssueLog['unlock'] + 1, 'endtime' => $tm));//下次解冻时间endtime修改成+间隔
		// $rs[] = $mo->table('tw_issue_log')->where(array('id' => $IssueLog['id']))->save(array('unlock' => $IssueLog['unlock'] + 1, 'endtime' => time()));

		if ($IssueLog['ci'] <= $IssueLog['unlock'] + 1) {
			$rs[] = $mo->table('tw_issue_log')->where(array('id' => $IssueLog['id']))->save(array('status' => 1));
		}

		if (check_arr($rs)) {
			$mo->execute('commit');
			$mo->execute('unlock tables');
			$this->success('解冻成功！');
		} else {
			$mo->execute('rollback');
			$this->error('解冻失败！');
		}
	}
}
?>