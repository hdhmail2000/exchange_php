<?php
namespace Support\Controller;

class PagesController extends SupportController
{
	protected function _initialize()
	{
		parent::_initialize();
/*		$allow_action=array("index","index1","article","coin_list","qq");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}*/
	}

	public function api()
	{
		$this->display();
	}
	
	public function fee($so = NULL)
	{
		$where['name'] = array('neq',Anchor_CNY);
		
		if ($so) {
			// 过滤非法字符----------------S
			if (checkstr($so)) {
				$this->error('您输入的信息有误！');
			}
			// 过滤非法字符----------------E
			
			$where['name'] = array('like',$so);
		}
		$this -> assign("keyword", $so);
		
		$where['status'] = 1;
		$coin_data = M('Coin')->where($where)->order('sort asc')->select();
		$this->assign('coin_data', $coin_data);
		
		$this->display();
	}

}
