<?php
namespace Admin\Controller;

class IssueController extends AdminController
{
	public function index($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else if ($field == 'name') {
				$where['name'] = array('like', '%' . $name . '%');
			} else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('Issue')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		
		$list = M('Issue')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$list[$k]['jian'] = $v['jian'].' '.$this->danweitostr($v['danwei']);
			//$list[$k]['endtime'] = date("Y-m-d H:i:s",strtotime($v['time']." + {$v['tian']} day"));
			$list[$k]['endtime'] = date("Y-m-d H:i:s",$v['endtime']);
		}
		
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	public function issueimage()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/public/';
		$upload->autoSub = false;
		
		$info = $upload->upload();
		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}

	public function edit()
	{
		if (empty($_GET['id'])) {
			$this->data = false;
		} else {
			$this->data = M('Issue')->where(array('id' => trim($_GET['id'])))->find();
		}
		
		$this->display();
	}

	public function save()
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		$_POST['addtime'] = time();

		if (strtotime($_POST['time']) != strtotime(addtime(strtotime($_POST['time'])))) {
			$this->error('开启时间格式错误！');
		}
		if (!floatval($_POST['tian'])) {
			$this->error('认购周期不能为空');
		}
		if (floatval($_POST['ci'] <= 0)) {
			$this->error('最低解冻次数不能小于0');
		}
		if (floatval($_POST['jian'] <= 0)) {
			$this->error('最低解冻间隔不能小于0');
		}
		if (floatval($_POST['min'] <= 0) || floatval($_POST['max'] <= 0)) {
			$this->error('单次最小数量 或 单次最大数量 不能小于0');
		}
		
		if($_POST['tuijian']==1){
			//推荐的话 先把其它的推荐修改成不推荐
			M('Issue')-> where('tuijian=1')->setField('tuijian','2');
		}
		
		switch ($_POST['danwei']) {
			case 'y':
				$_POST['step'] = $_POST['jian'] * 12 * 30 * 24 * 60 * 60;
				break;

			case 'm':
				$_POST['step'] = $_POST['jian'] * 30 * 24 * 60 * 60;
				break;

			case 'd':
				$_POST['step'] = $_POST['jian'] * 24 * 60 * 60;
				break;

			case 'h':
				$_POST['step'] = $_POST['jian'] * 60 * 60;
				break;

			default:

			case 'i':
				$_POST['step'] = $_POST['jian'] * 60;
				break;
		}
		
		$_POST['endtime'] = strtotime($_POST['time']) + ($_POST['tian'] * 24 * 60 * 60);

		if ($_POST['id']) {
			$rs = M('Issue')->save($_POST);
		} else {
			$rs = M('Issue')->add($_POST);
		}

		if ($rs) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}

	public function status()
	{
		if (APP_DEMO) {
			$this->error('测试站暂时不能修改！');
		}

		if (IS_POST) {
			$id = array();
			$id = implode(',', $_POST['id']);
		} else {
			$id = $_GET['id'];
		}

		if (empty($id)) {
			$this->error('请选择要操作的数据!');
		}

		$where['id'] = array('in', $id);
		$method = $_GET['method'];

		switch (strtolower($method)) {
			case 'forbid':
				$data = array('status' => 0);
				break;

			case 'resume':
				$data = array('status' => 1);
				break;

			case 'delete':
				if (M('Issue')->where($where)->delete()) {
					$this->success('操作成功！');
				} else {
					$this->error('操作失败！');
				}

				break;

			default:
				$this->error('参数非法');
		}

		if (M('Issue')->where($where)->save($data)) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}

	public function log($name = NULL)
	{
		if ($name && check($name, 'username')) {
			$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
		} else {
			$where = array();
		}

		$count = M('IssueLog')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('IssueLog')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
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