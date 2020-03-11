<?php
namespace Admin\Controller;

class ArticleController extends AdminController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","edit","wenzhangimg","status","type","typeEdit","typeStatus","adver","adverEdit","adverStatus","adverImage","youqing","youqingEdit","youqingStatus");
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
			} else if ($field == 'title') {
				$where['title'] = array('like', '%' . $name . '%');
			} else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('Article')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Article')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['adminid'] = M('Admin')->where(array('id' => $v['adminid']))->getField('username');
			$list[$k]['type'] = M('ArticleType')->where(array('id' => $v['pid']))->getField('title');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function edit($id = NULL, $type = NULL)
	{
		if (empty($_POST)) {
			$list = M('ArticleType')->where(array('pid' => array('neq',0)))->select();
			$this->assign('list', $list);

			if ($id) {
				$this->data = M('Article')->where(array('id' => trim($id)))->find();
			} else {
				$this->data = null;
			}

			$this->display();
		} else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			if ($type == 'images') {
				$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
				$upload = new \Think\Upload();
				$upload->maxSize = 3145728;
				$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
				$upload->rootPath = './Upload/article/';
				$upload->autoSub = false;
				$info = $upload->uploadOne($_FILES['imgFile']);

				if ($info) {
					$data = array('url' => str_replace('./', '/', $upload->rootPath) . $info['savename'], 'error' => 0);
					exit(json_encode($data));
				} else {
					$error['error'] = 1;
					$error['message'] = '';
					exit(json_encode($error));
				}
			} else {
				$upload = new \Think\Upload();
				$upload->maxSize = 3145728;
				$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
				$upload->rootPath = './Upload/article/';
				$upload->autoSub = false;
				$info = $upload->upload();

				if ($info) {
					foreach ($info as $k => $v) {
						$_POST[$v['key']] = $v['savename'];
					}
				}
				
				
				if ($_POST['id']) {
					if ($_POST['addtime']) {
						if (addtime(strtotime($_POST['addtime'])) == '---') {
							$this->error('编辑时间格式错误');
						} else {
							$_POST['addtime'] = strtotime($_POST['addtime']);
						}
					} else {
						$_POST['addtime'] = time();
					}
					
					if ($_POST['endtime']) {
						if (addtime(strtotime($_POST['endtime'])) == '---') {
							$this->error('编辑时间格式错误');
						} else {
							$_POST['endtime'] = strtotime($_POST['endtime']);
						}
					} else {
						$_POST['endtime'] = time();
					}
										
					$rs = M('Article')->save($_POST);
				} else {
					$_POST['addtime'] = time();
					$_POST['endtime'] = time();
					
					$_POST['adminid'] = session('admin_id');
					$rs = M('Article')->add($_POST);
				}

				if ($rs) {
					$this->success('编辑成功！');
				} else {
					$this->error('编辑失败！');
				}
			}
		}
	}

	public function wenzhangimg()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/wenzhang/';
		$upload->autoSub = false;
		$info = $upload->upload();

		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			// echo $path;
			$p = trim($path);
			echo json_encode($p);
			exit();
		}
	}

	public function status($id = NULL, $type = NULL, $mobile = 'Article')
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
			} else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}

	public function type($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else if ($field == 'title') {
				$where['title'] = array('like', '%' . $name . '%');
			} else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}
		
		$where_1 = $where;
		$where_1['pid'] = 0;
		$where_2 = $where;
		
		$list = M('ArticleType')->where($where_1)->order('sort asc')->select();
		foreach ($list as $k => $v) {
			$where_2['pid'] = $v['id'];
			$list[$k]['voo'] = M('ArticleType')->where($where_2)->order('sort asc')->select();
		}

		$this->assign('list', $list);
		$this->display();
	}

	public function typeEdit($id = NULL, $type = NULL)
	{
		$list = M('ArticleType')->where(array('pid' => 0))->select();
		$this->assign('list', $list);

		if (empty($_POST)) {
			if ($id) {
				$this->data = M('ArticleType')->where(array('id' => trim($id)))->find();
			} else {
				$this->data = null;
			}

			$this->display();
		} else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			if ($type == 'images') {
				$baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
				$upload = new \Think\Upload();
				$upload->maxSize = 3145728;
				$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
				$upload->rootPath = './Upload/article/';
				$upload->autoSub = false;
				$info = $upload->uploadOne($_FILES['imgFile']);

				if ($info) {
					$data = array('url' => str_replace('./', '/', $upload->rootPath) . $info['savename'], 'error' => 0);
					exit(json_encode($data));
				} else {
					$error['error'] = 1;
					$error['message'] = '';
					exit(json_encode($error));
				}
			} else {

				if ($_POST['id']) {
					$_POST['endtime'] = time(); //编辑时间

					$rs = M('ArticleType')->save($_POST);
				} else {
					$_POST['addtime'] = time(); //添加时间
					
					$_POST['adminid'] = session('admin_id');
					$rs = M('ArticleType')->add($_POST);
				}


				if ($rs) {
					$this->success('编辑成功！');
				} else {
					$this->error('编辑失败！');
				}
			}
		}
	}

	public function typeStatus($id = NULL, $type = NULL, $mobile = 'ArticleType')
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
			} else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}
	
	// 广告管理
	public function adver($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else if ($field == 'title') {
				$where['title'] = array('like', '%' . $name . '%');
			} else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('Adver')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Adver')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	// 广告管理 新增&编辑
	public function adverEdit($id = NULL)
	{
		if (empty($_POST)) {
			if ($id) {
				$this->data = M('Adver')->where(array('id' => trim($id)))->find();
			} else {
				$this->data = null;
			}

			$this->display();
		} else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			$upload = new \Think\Upload();
			$upload->maxSize = 3145728;
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
			$upload->rootPath = './Upload/ad/';
			$upload->autoSub = false;
			$info = $upload->upload();

			if ($info) {
				foreach ($info as $k => $v) {
					$_POST[$v['key']] = $v['savename'];
				}
			}
			
			if ($_POST['onlinetime']) {
				if (addtime(strtotime($_POST['onlinetime'])) == '---') {
					$this->error('添加时间格式错误');
				} else {
					$_POST['onlinetime'] = strtotime($_POST['onlinetime']);
				}
			} else {
				$_POST['onlinetime'] = time();
			}

			if ($_POST['addtime']) {
				if (addtime(strtotime($_POST['addtime'])) == '---') {
					$this->error('添加时间格式错误');
				} else {
					$_POST['addtime'] = strtotime($_POST['addtime']);
				}
			} else {
				$_POST['addtime'] = time();
			}

			if ($_POST['endtime']) {
				if (addtime(strtotime($_POST['endtime'])) == '---') {
					$this->error('编辑时间格式错误');
				}
				else {
					$_POST['endtime'] = strtotime($_POST['endtime']);
				}
			} else {
				$_POST['endtime'] = time();
			}

			if ($_POST['id']) {
				$rs = M('Adver')->save($_POST);
			} else {
				//$_POST['adminid'] = session('admin_id');
				$rs = M('Adver')->add($_POST);
			}

			if ($rs) {
				$this->success('编辑成功！',U('Article/adver'));
			} else {
				$this->error('编辑失败！');
			}
		}
	}

	public function adverStatus($id = NULL, $type = NULL, $mobile = 'Adver')
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
			} else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}

	public function adverImage()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/ad/';
		$upload->autoSub = false;
		$info = $upload->upload();

		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}

	public function youqing($name = NULL, $field = NULL, $status = NULL)
	{
		$where = array();

		if ($field && $name) {
			if ($field == 'username') {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else if ($field == 'title') {
				$where['title'] = array('like', '%' . $name . '%');
			} else {
				$where[$field] = $name;
			}
		}

		if ($status) {
			$where['status'] = $status - 1;
		}

		$count = M('Link')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('Link')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}

	public function youqingEdit($id = NULL)
	{
		if (empty($_POST)) {
			if ($id) {
				$this->data = M('Link')->where(array('id' => trim($id)))->find();
			} else {
				$this->data = null;
			}

			$this->display();
		} else {
			if (APP_DEMO) {
				$this->error('测试站暂时不能修改！');
			}

			if ($_POST['addtime']) {
				if (addtime(strtotime($_POST['addtime'])) == '---') {
					$this->error('添加时间格式错误');
				} else {
					$_POST['addtime'] = strtotime($_POST['addtime']);
				}
			} else {
				$_POST['addtime'] = time();
			}

			if ($_POST['endtime']) {
				if (addtime(strtotime($_POST['endtime'])) == '---') {
					$this->error('编辑时间格式错误');
				} else {
					$_POST['endtime'] = strtotime($_POST['endtime']);
				}
			} else {
				$_POST['endtime'] = time();
			}

			if ($_POST['id']) {
				$rs = M('Link')->save($_POST);
			} else {
				$rs = M('Link')->add($_POST);
			}

			if ($rs) {
				$this->success('编辑成功！');
			} else {
				$this->error('编辑失败！');
			}
		}
	}

	public function youqingStatus($id = NULL, $type = NULL, $mobile = 'Link')
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
			} else {
				$this->error('操作失败！');
			}

			break;

		default:
			$this->error('操作失败！');
		}

		if (M($mobile)->where($where)->save($data)) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}
}

?>