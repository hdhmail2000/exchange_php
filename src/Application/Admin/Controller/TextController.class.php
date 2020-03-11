<?php
namespace Admin\Controller;

class TextController extends AdminController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("index","edit","status");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("页面不存在！");
		}
	}
	
	public function index($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('Text')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Text')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) { }

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function edit($id = NULL)
	{
		if (empty($_POST)) {
			if ($id) {
				$this->data = M('Text')->where(array('id' => trim($id)))->find();
			} else {
				$this->data = null;
			}

			$this->display();
		} else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}
			
			if ($_POST['id']) {
				$_POST['endtime'] = time();
				
				$rs = M('Text')->save($_POST);
			} else {
				$_POST['addtime'] = time();
				$_POST['endtime'] = time();
				
				if (M('Text')->where(array('name' => $_POST['name']))->find()) {
					$this->error('提示标识已存在！');
				}
				
				$rs = M('Text')->add($_POST);
			}

			if ($rs) {
				$this->success('编辑成功！');
			} else {
				$this->error('编辑失败！');
			}
		}
	}

	public function status($id = NULL, $type = NULL, $mobile = 'text')
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
			//$this->error('请选择要操作的数据!');
		}

		$where['id'] = array('in', $id);
		$method = $_GET['type'];

		switch (strtolower($method)) {
			case 'forbid':
				$data = array('status' => 0);
				break;

			case 'resume':
				$data = array('status' => 1);
				break;
				
			case 'repeal':
				$data = array('status' => 2, 'endtime' => time());
				break;
				
			case 'delt':
				if (M($mobile)->where($where)->delete()) {
					$this->success('操作成功！');
				} else {
					$this->error('操作失败！');
				}
				break;

			default:
				$this->error('非法参数！'.$_GET['type']);
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}
}

?>