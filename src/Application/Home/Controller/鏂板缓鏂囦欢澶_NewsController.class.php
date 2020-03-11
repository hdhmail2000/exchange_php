<?php
namespace Home\Controller;

class NewsController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
/*		$allow_action=array("index","detail","type");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}*/
	}
	
	public function index($id = null)
	{
		// 过滤非法字符----------------S
		if (checkstr($id)) {
			$this->error('您输入的信息有误！');
		}
		// 过滤非法字符----------------E

		$news_list = M('Article')->where(array('type' => 'news', 'status' => 1))->order('id desc')->limit(2)->select();
		$this->assign('news_list', $news_list);
		
		
		$count = M('Article')->where(array('type' => 'news', 'status' => 1))->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		
		$this->assign('page', $show);
		$this->display();
	}
}

?>