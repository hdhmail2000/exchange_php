<?php
namespace Home\Controller;

class IndexController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action = array("index","more");
		if (!in_array(ACTION_NAME,$allow_action)) {
			$this->error("非法操作！");
		}
	}

	public function index()
	{
		$getCoreConfig = getCoreConfig();
		if (!$getCoreConfig) {
			$this->error('核心配置有误');
		}
		$this->assign('jiaoyiqu', $getCoreConfig['indexcat']);
		
		// 轮播图
		$banner = M('Adver')->where(array('look'=>0,'status'=>1))->field('name,subhead,img,onlinetime')->order('id desc')->select();
		$this->assign('banner', $banner);
		
		$this->display();
	}

    public function more(){
        $this->display();
    }
}
?>