<?php
/* Ӧ�� - �ֺ����� */
namespace Home\Controller;

class FenhongController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","log");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("�Ƿ�������");
		}
	}
	
	public function index()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$this->assign('prompt_text', D('Text')->get_content('game_fenhong'));
		$coin_list = D('Coin')->get_all_xnb_list();

		foreach ($coin_list as $k => $v) {
			$list[$k]['img'] = D('Coin')->get_img($k);
			$list[$k]['title'] = $v;
			$list[$k]['quanbu'] = D('Coin')->get_sum_coin($k);
			$list[$k]['wodi'] = D('Coin')->get_sum_coin($k, userid());
			$list[$k]['bili'] = round(($list[$k]['wodi'] / $list[$k]['quanbu']) * 100, 2) . '%';
		}

		$this->assign('list', $list);
		$this->display();
	}

	public function log()
	{
		if (!userid()) {
			redirect('/Login/index');
		}

		$this->assign('prompt_text', D('Text')->get_content('game_fenhong_log'));
		$where['userid'] = userid();
		$Model = M('FenhongLog');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
}

?>