<?php
namespace Admin\Controller;

class InvitController extends AdminController
{
	private $Model;

	public function __construct()
	{
		parent::__construct();
		$this->Title = '推广记录';
	}

	public function index($name = NULL, $userr = NULL, $status = NULL)
	{
		$where = array();
		
		/* 用户名--条件 */
		if ($name) {
			if ($userr == "") {
				$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
			} else {
				$where['invit'] = M('User')->where(array('username' => $name))->getField('id');
			}
		}
		// 默认状态
		if ($status == 0) {
			$where['status'] = 0;
		}
		/* 状态--条件 */
		if ($status != '99') {
			if ($status) {
				$where['status'] = $status;
			}
		}
		
		// 统计
		$tongji['ydz'] = M('Invit')->where(array('status'=>1))->sum('fee') * 1;
		$tongji['wdz'] = M('Invit')->where(array('status'=>0))->sum('fee') * 1;
		$tongji['heji'] = M('Invit')->sum('fee') * 1;
		$this->assign('tongji', $tongji);
		

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
	
	public function mining($name = NULL, $status = NULL)
	{
		$where = array();
		
		/* 用户名--条件 */
		if ($name) {
			$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
		}
		// 默认状态
		if ($status == 0) {
			$where['status'] = 0;
		}
		/* 状态--条件 */
		if ($status != '99') {
			if ($status) {
				$where['status'] = $status;
			}
		}
		
		// 统计
		$tongji['ydz'] = M('mining')->where(array('status'=>1))->sum('fee') * 1;
		$tongji['wdz'] = M('mining')->where(array('status'=>0))->sum('fee') * 1;
		$tongji['heji'] = M('mining')->sum('fee') * 1;
		$this->assign('tongji', $tongji);
		

		$count = M('mining')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('mining')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
	
	public function recharge($name = NULL, $status = NULL)
	{
		echo '开发中';die();
		$where = array();
		
		/* 用户名--条件 */
		if ($name) {
			$where['userid'] = M('User')->where(array('username' => $name))->getField('id');
		}
		// 默认状态
		if ($status == 0) {
			$where['status'] = 0;
		}
		/* 状态--条件 */
		if ($status != '99') {
			if ($status) {
				$where['status'] = $status;
			}
		}
		
		// 统计
		$tongji['ydz'] = M('mining_recharge')->where(array('status'=>1))->sum('sd_num') * 1;
		$tongji['wdz'] = M('mining_recharge')->where(array('status'=>0))->sum('sd_num') * 1;
		$tongji['heji'] = M('mining_recharge')->sum('sd_num') * 1;
		$this->assign('tongji', $tongji);
		

		$count = M('mining_recharge')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('mining_recharge')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		foreach ($list as $k => $v) {
			$list[$k]['username'] = M('User')->where(array('id' => $v['userid']))->getField('username');
		}

		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
}
?>