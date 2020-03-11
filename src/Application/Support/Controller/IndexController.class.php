<?php
namespace Support\Controller;

class IndexController extends SupportController
{
	protected function _initialize()
	{
		parent::_initialize();
		$allow_action=array("index","search","categories","sections","articles");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error(L("非法操作！"));
		}
	}

	public function index()
	{
		$where['pid'] = 0;
		$where['status'] = 1;
		$classify = M('ArticleType')->where($where)->order('sort asc')->select();
		$this->assign('classify', $classify);
		
		
		$where_2['status'] = 1;
		$classlist = M('article')->where($where_2)->order('addtime desc')->limit(5)->select();
		foreach ($classlist as $k => $v) {
			$fenlei = M('ArticleType')->where(array('id'=>$v['pid']))->find();
			$classlist[$k]['classname'] = $fenlei['title'];
		}
		
		$this->assign('empty','<li style="color:#c1c1c1;">'.L('没有数据').'</li>');
		$this->assign('classlist', $classlist);
		
		$this->display();
	}
	
	// 搜索页面
	public function search($so = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($so)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
        if (empty($so)) {
            $this->error("关键词不能为空！请重新输入！");
        }
		$this -> assign("keyword", $so);
		
		$where['title'] = array('like',"%$so%");
		
		$mo = M('Article');
		$count = $mo->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		
		$list = $mo->where($where)->order('endtime desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $v) {
			$fenlei = M('ArticleType')->where(array('id'=>$v['pid']))->find();
			$list[$k]['classname'] = $fenlei['title'];
			$list[$k]['abstract'] = ReStrLen( DeleteHtml( strip_tags($v['content']) ),100);
		}
		
		$this->assign('empty','<li style="color:#c1c1c1;">'.L('没有数据').'</li>');
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->assign('count', $count);
		
		$this->display();
	}
	
	public function categories($cid = null)
	{
		// 过滤非法字符----------------S
		if (checkstr($cid)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (empty($cid)) {
			$id = 1;
		}
		if (!check($cid, 'd')) {
			$id = 1;
		}
		
		$info['cid'] = $cid;
		$this->assign('info', $info);
		
		$where['pid'] = trim($cid);
		$where['status'] = 1;
		$classify = M('ArticleType')->where($where)->order('sort asc')->select();
		
		foreach ($classify as $k => $v) {
			$where_2['pid'] = $v['id'];
			$classify[$k]['voo'] = M('article')->where($where_2)->order('sort,endtime desc')->limit(6)->select();
		}
		$this->assign('empty','<li style="color:#c1c1c1;">'.L('没有数据').'</li>');
		$this->assign('classify', $classify);
		
		$classinfo = M('ArticleType')->where(array('id' => trim($cid)))->find();
		$this->assign('classinfo', $classinfo);
		
		$this->display();
	}
	
	public function sections($id = NULL,$cid = null)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($cid)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (empty($id)) {
			$id = 1;
		}
		if (!check($id, 'd')) {
			$id = 1;
		}
		
		$info['id'] = $id;
		$info['cid'] = $cid;
		$this->assign('info', $info);
		
		$where['pid'] = trim($id);
		$where['status'] = 1;
		
		
		$mo = M('Article');
		$count = $mo->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		
		$classify = $mo->where($where)->order('sort,endtime desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

		$this->assign('empty','<li style="color:#c1c1c1;">'.L('没有数据').'</li>');
		$this->assign('classify', $classify);
		$this->assign('page', $show);
		
		
		$classinfo = M('ArticleType')->where(array('id' => trim($id)))->find();
		$this->assign('classinfo', $classinfo);
		
		$this->display();	
	}
	
	// 文章详情页
	public function articles($id = NULL,$cid = NULL)
	{
		// 过滤非法字符----------------S
		if (checkstr($id) || checkstr($cid)) {
			$this->error(L('您输入的信息有误！'));
		}
		// 过滤非法字符----------------E
		
		if (empty($id)) {
			$id = 1;
		}
		if (!check($id, 'd')) {
			$id = 1;
		}
		
		$info['id'] = $id;
		$info['cid'] = $cid;
		
		$configs = M('Config')->where(array('id'=>1))->find();
		$info['jieweibs'] = $configs['web_identification'];
		
		$this->assign('info', $info);
		
		
		$data = M('Article')->where(array('id' => $id))->find();
		$a_info = M('ArticleType')->where(array('name'=>$data['type']))->find();
		$a_id = $a_info['id'];

		$this->assign('a_id', $a_id);
		$this->assign('data', $data);
		
		$lists = M('Article')->where(array('pid' => $cid,'id' => array('neq',$id)))->order('endtime desc')->limit(9)->select();
		$this->assign('lists', $lists);
		$this->display();
	}
}
