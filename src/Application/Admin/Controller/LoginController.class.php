<?php
namespace Admin\Controller;

class LoginController extends \Think\Controller
{
	public function chexiao()
	{
		$trade = M()->query('select * from tw_trade where status=0 order by id desc ;');
		//$trade = M('Trade')->where(array('status' =>0))->select();
		//var_dump($trade);exit;
		$rs =array();
		foreach ($trade as $k => $v) {
			$rs[] = D('Trade')->chexiao($v['id']);
		}

		if (check_arr($rs)) {
			echo '撤销成功！';
		} else {
			echo '撤销失败！';
		}
	}
	
	public function index($username = NULL, $password = NULL, $verify = NULL, $urlkey = NULL)
	{
		if (IS_POST) {
			if (!check_verify($verify)) {
				$this->error('验证码输入错误！');
			}

			$admin = M('Admin')->where(array('username' => $username))->find();

			if ($admin['password'] != md5($password)) {
				$this->error('用户名或密码错误！');
			} else {
				$uids = $admin['id'];
				$admin_auth = M('AuthGroupAccess')->where(array('uid' => $uids))->find();
				if(!$admin_auth){
					$this->error('用户暂未分组！');
				}

				$group_id = $admin_auth['group_id'];
				$admin_gid = M('AuthGroup')->where(array('id' => $group_id))->find();
				if(!$admin_gid){
					$this->error('用户所在分组不存在！');
				}
				
				M('Admin')->where(array('username' => $username))->save(array('last_login_time' => time(), 'last_login_ip' => get_client_ip()));
				
				session('admin_id', $admin['id']);
				S('5df4g5dsh8shnfsf', $admin['id']);
				session('admin_username', $admin['username']);
				session('admin_password', $admin['password']);
				$this->success('登陆成功!', U('Index/index'));
			}
		} else {
			defined('ADMIN_KEY') || define('ADMIN_KEY', '');

			if (ADMIN_KEY && ($urlkey != ADMIN_KEY)) {
				$this->redirect('Home/Index/index');
			}
			if (session('admin_id')) {
				$this->redirect('Admin/Index/index');
			}

			$this->display();
		}
	}

	public function loginout()
	{
		session(null);
		S('5df4g5dsh8shnfsf', null);
		$this->redirect('Login/index');
	}

	public function lockScreen()
	{
		if (!IS_POST) {
			$this->display();
		} else {
			$pass = trim(I('post.pass'));

			if ($pass) {
				session('LockScreen', $pass);
				session('LockScreenTime', 3);
				$this->success('锁屏成功,正在跳转中...');
			} else {
				$this->error('请输入一个锁屏密码');
			}
		}
	}

	public function unlock()
	{
		if (!session('admin_id')) {
			session(null);
			$this->error('登录已经失效,请重新登录...', '/Admin/login');
		}
		if (session('LockScreenTime') < 0) {
			session(null);
			$this->error('密码错误过多,请重新登录...', '/Admin/login');
		}

		$pass = trim(I('post.pass'));
		if ($pass == session('LockScreen')) {
			session('LockScreen', null);
			$this->success('解锁成功', '/Admin/index');
		}

		$admin = M('Admin')->where(array('id' => session('admin_id')))->find();
		if ($admin['password'] == md5($pass)) {
			session('LockScreen', null);
			$this->success('解锁成功', '/Admin/index');
		}

		session('LockScreenTime', session('LockScreenTime') - 1);
		$this->error('用户名或密码错误！');
	}

	public function queue()
	{
		$file_path = DATABASE_PATH . '/check_queue.json';
		$time = time();
		$timeArr = array();

		if (file_exists($file_path)) {
			$timeArr = file_get_contents($file_path);
			$timeArr = json_decode($timeArr, true);
		}

		array_unshift($timeArr, $time);
		$timeArr = array_slice($timeArr, 0, 3);

		if (file_put_contents($file_path, json_encode($timeArr))) {
			exit('exec ok[' . $time . ']' . "\n");
		} else {
			exit('exec fail[' . $time . ']' . "\n");
		}
	}
}
?>