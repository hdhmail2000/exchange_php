<?php
namespace Mobile\Controller;

class IndexController extends MobileController
{

	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("index","indexold","article","coin_list","qq");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("非法操作！");
		}
	}

	public function index()
	{
		$this->redirect('Trade/tradelist');
		$this->display();
	}
	
	// 首页界面
	public function indexold()
	{
		// 幻灯片信息------S
		$indexAdver = (APP_DEBUG ? null : S('mobile_index_indexAdver'));
		if (!$indexAdver) {
			$indexAdver = M('Adver')->where(array('status' => 1,'look'=>1,'lang'=>LANG_SET))->order('sort asc')->select();
			S('mobile_index_indexAdver', $indexAdver);
		}
		$this->assign('indexAdver', $indexAdver);

		// 获取最新公告信息------S
		$indexArticle = (APP_DEBUG ? null : S('mobile_index_indexArticle'));
		if (!$indexArticle) {
			$indexArticle = M('Article')->where(array('type' =>array('like','notice_%'), 'status' => 1, 'index' => 1,'lang'=>LANG_SET))->order('id desc')->find();
			S('mobile_index_indexArticleType', $indexArticle);
		}
		$this->assign('indexArticle', $indexArticle);

		// 获取最新公告信息------E
        $helpArticle = (APP_DEBUG ? null : S('mobile_index_helpArticle'));
        if (!$indexArticle || true) {
            $helpType=M('ArticleType')->where(array('status' => 1, 'footer' => 1, 'shang' => array('like','help_%'),'lang'=>LANG_SET))->order('sort asc ,id desc')->select();
            foreach ($helpType as $k => $v) {
                $second_class= M('ArticleType')->where(array('shang' => $v['name'], 'footer' => 1, 'status' => 1,'lang'=>LANG_SET))->order('id asc')->select();
                if(!empty($second_class)){
                    foreach($second_class as $val){
                        $article_list = M('Article')->where(array('footer'=>1,'index'=>1,'status'=>1,'type'=>$val['name']))->limit(5)->select();
                        if(!empty($article_list)){
                            foreach($article_list as $kk=>$vv){
                                $footerArticle[$v['name']][]=$vv;
                            }
                        }
                    }
                } else {
                    $article_list = M('Article')->where(array('footer'=>1,'index'=>1,'status'=>1,'type'=>$v['name']))->limit(5)->select();
                    if(!empty($article_list)){
                        foreach($article_list as $kk=>$vv){
                            $footerArticle[$v['name']][]=$vv;
                        }
                    }
                }
            }
            $helpArticle=$footerArticle;
            S('mobile_index_helpArticle', $helpArticle);
        }
        $this->assign('helpArticle', $helpArticle);

		$this->display('index');
	}

	// 充提币链接
	public function article()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		$this->display();
	}

	// 充提币链接
	public function coin_list()
	{
		if (!userid()) {
			redirect('/Login/index');
		}
		$this->display();
	}

	// 在线客服
	public function qq()
	{
		$this->display();
	}
}