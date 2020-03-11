<?php
namespace Admin\Controller;

class PtpbcController extends AdminController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index",'add','edit','del','save_action',"chexiao","log","chat","chatStatus","comment","commentStatus","market","marketEdit","marketStatus","invit");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("页面不存在！");
		}
	}

	public function index($market = NULL, $lang = NULL, $merchant = NULL){
		$where = array();
		if ($market) {
			$where['market'] = $market;
		}

		if ($lang) {
			$where['lang'] = $lang;
		}

		// 交易类型
		if ($merchant) {
			$where['merchant'] = array('like','%'.$merchant.'%');
		}

		$count = M('Ptpbc')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Ptpbc')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	public function add(){
		$this->display('ptpbcEdit');
	}
	public function edit(){
		$data=M('Ptpbc')->where(array('id'=>I('id')))->find();
		$this->assign('data',$data);
		$this->display('ptpbcEdit');
	}
	public function del(){
		$data=M('Ptpbc')->where(array('id'=>I('id')))->delete();
		$this->success('删除成功');
	}
	public function save_action(){
		$data=I('post.');
		foreach ($data as $k=>$v){
			if(empty($v) && $k!='id') $this->error('所有选项必须填写');
		}
		if ($data['id']) {
			$rs = M('Ptpbc')->save($data);
		}else {
			$data['addtime'] = time();
			$rs = M('Ptpbc')->add($data);
		}
		if ($rs) {
			$this->success('编辑成功！');
		}else{
			$this->error('编辑失败！');
		}
	}
	public function chexiao($id = NULL)
	{
		$rs = D('Trade')->chexiao($id);

		if ($rs[0]) {
			$this->success($rs[1]);
		}
		else {
			$this->error($rs[1]);
		}
	}

	public function log($field = NULL, $name = NULL, $market = NULL,$status = NULL, $starttime = NULL, $endtime = NULL){
		$where = array();
		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}else if ($field == 'peername') {
				$where['peerid'] = M('User')->where(array('username' => $name))->getField('id');
			}else{
				$where[$field] = $name;
			}
		}
        // 交易状态
        if ($status) {
            $where['status'] = $status;
        }

		if ($market) {
			$where['market'] = $market;
		}

		// 时间--条件
		$where['userid'] = array('gt',0);
		$time_type = 'addtime';
		if (!empty($starttime) && empty($endtime)) {
			$starttime = strtotime($starttime);
			$where[$time_type] = array('EGT',$starttime);
		}else if(empty($starttime) && !empty($endtime)){
			$endtime = strtotime($endtime);
			$where[$time_type] = array('ELT',$endtime);
		}else if(!empty($starttime) && !empty($endtime)){
			$starttime = strtotime($starttime);
			$endtime = strtotime($endtime);
			$where[$time_type] =  array(array('EGT',$starttime),array('ELT',$endtime));
		}else{
			// 无时间查询，显示申请时间类型十天以内数据
			$now_time = time() - 10*24*60*60;
			$where['addtime'] =  array('EGT',$now_time);
		}


		$list_new = M('PtpbcLog')->where($where)->select();

		$num_zong = 0;
		$money_zong = 0;
		$fee_sell_zong=0;

		foreach ($list_new as $k => $v) {
			$num_zong += $v['num'];
			$money_zong += $v['mum'];
			$fee_sell_zong += $v['fee_sell'];
		}

		$count = M('PtpbcLog')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('PtpbcLog')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $v) {
            $list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
            $list[$k]['amount']=$v['price'];
            $list[$k]['price']=$v['price']/$v['num'];
            //$list[$k]['peername'] = M('User')->where(array('id' => $v['peerid']))->getField('username');
        }

		$datas = array();
		$datas['num_zong'] = $num_zong;
		$datas['money_zong'] = $money_zong;
		$datas['fee_sell_zong'] = $fee_sell_zong;

		$this->assign('datas', $datas);
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function chat($field = NULL, $name = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		$count = M('Chat')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Chat')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function chatStatus($id = NULL, $type = NULL, $mobile = 'Chat')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
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

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($mobile)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function comment($field = NULL, $name = NULL, $coinname = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		if ($coinname) {
			$where['coinname'] = $coinname;
		}

		$count = M('CoinComment')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('CoinComment')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function commentStatus($id = NULL, $type = NULL, $mobile = 'CoinComment')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
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

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($mobile)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function market($field = NULL, $name = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		$count = M('Market')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Market')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function marketEdit($id = NULL)
	{
		if (empty($_POST)) {
			if (empty($id)) {
				$this->data = array();
			}
			else {
				$this->data = M('Market')->where(array('id' => $id))->find();
			}
			$time_arr = array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23');
			$time_minute = array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59');
			$this->assign('time_arr', $time_arr);
			$this->assign('time_minute', $time_minute);

			$this->display();
		}
		else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$round = array(0, 1, 2, 3, 4, 5, 6);

			if (!in_array($_POST['round'], $round)) {
				$this->error('小数位数格式错误！');
			}

			if ($_POST['id']) {
				$rs = M('Market')->save($_POST);
			}
			else {
				$_POST['name'] = $_POST['sellname'] . '_' . $_POST['buyname'];
				unset($_POST['buyname']);
				unset($_POST['sellname']);

				if (M('Market')->where(array('name' => $_POST['name']))->find()) {
					$this->error('市场存在！');
				}

				$rs = M('Market')->add($_POST);
			}

			if ($rs) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}
		}
	}

	public function marketStatus($id = NULL, $type = NULL, $mobile = 'Market')
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (empty($id)) {
			$this->error('参数错误！');
		}

		if (empty($type)) {
			$this->error('参数错误1！');
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

		case 'delete':
			$data = array('status' => -1);
			break;

		case 'del':
			if (M($mobile)->where($where)->delete()) {
				$this->success('操作成功！');
			}
			else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		}
		else {
			$this->error('操作失败！');
		}
	}

	public function invit($field = NULL, $name = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			}
			else {
				$where[$field] = $name;
			}
		}

		$count = M('Invit')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Invit')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
			$list[$k]['invit'] = M('User')->where(array('id' => $v['invit']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
}

?>
