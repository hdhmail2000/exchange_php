<?php
namespace Home\Controller;

class HomeController extends \Think\Controller
{
	protected function _initialize()
	{
		$allow_controller=array("Index","Ajax","Api","Article","Finance","Login","Queue","Trade","Backstage","Exchange","Pay","User","Chart","Ptpbc","News","Reward", "Game","Financing","Issue","Vote");
		
		if(!in_array(CONTROLLER_NAME,$allow_controller)){
			$this->error("非法操作");
		}
		
		defined('APP_DEMO') || define('APP_DEMO', 0);
		
		// 链接审查（检查是否需要登录，检查是否开放访问）
		$data_url = (APP_DEBUG ? null : S('closeUrl'));
		if (!$data_url) {
			//$closeUrl = M('daohang')->where('status=1')->field('url')->select();
			$closeUrl = M('daohang')->where(array('url'=>$_SERVER['REQUEST_URI'], 'status'=>1))->find();
			S('closeUrl', $closeUrl);	 

			if (S('closeUrl')['get_login'] == 1) {
				$this->error(L('需要登录后浏览!'), U('Login/index'));exit;
			}
			if (S('closeUrl')['access'] == 1) {
				$this->error(L('禁止访问！'), U('/'));exit;
			}
		}
		
/*      
		// 旧的方法废弃，上面是新的
        $closeUrl = S('closeUrl');
        if (empty($closeUrl)) {
			$list = M('daohang')->field('url')->where('status=1 and get_login=1')->select();	
            foreach($list as $v)
            {
                if ('' != $v['url']) {
                    $closeUrl[] = $v['url'];
                }
            }
            S('closeUrl', $closeUrl);
        } else {
            if (!userid()) {
                foreach ($closeUrl as $v) {
                    if(mb_strripos($_SERVER['REQUEST_URI'], $v) !== false) {
                        //echo $_SERVER['REQUEST_URI'].$v.'<br>';
						$this->error(L('请先登录，继续浏览。'), U('Login/index'));
                    } else {
                        break;
                    }
                }
            }
        }
		*/

		if (!session('userId')) {
			session('userId', 0);
		} else if (CONTROLLER_NAME != 'Login' ) {
/*			$user = D('user')->where('id = ' . session('userId'))->find();
			if (!$user['paypassword']) {
				//未设置交易密码
				redirect('/Login/register1');
			}*/
		}
	}

	public function __construct()
	{
		parent::__construct();
		
		if (userid()) {
			$userCoin_top = M('UserCoin')->where(array('userid' => userid()))->find();
/*			 $userCoin_top['cny'] = round($userCoin_top['cny'], 2);
			 $userCoin_top['cnyd'] = round($userCoin_top['cnyd'], 2);*/
			$userCoin_top[Anchor_CNY] = sprintf("%.2f", $userCoin_top[Anchor_CNY]);
			$userCoin_top[Anchor_CNY.'d'] = sprintf("%.2f", $userCoin_top[Anchor_CNY.'d']);
			$this->assign('userCoin_top', $userCoin_top);
		}

		if (isset($_GET['invit'])) {
			session('invit', $_GET['invit']);
		}

		$config = (APP_DEBUG ? null : S('home_config'));
		if (!$config) {
			$config = M('Config')->where(array('id' => 1))->find();
			S('home_config', $config);
		}
		
		// 检查是否关闭站点
		if (!session('web_close')) {
			if (!$config['web_close']) {
				$conf = array();
				
				if(LANG_SET == "zh-cn"){
					$conf['langs_close_cause'] = $config['web_close_cause'];
				} else {
					$conf['langs_close_cause'] = $config['web_close_cause_en'];
				}
				
				$this->assign('conf', $conf);
				$this->display('Index/maintain');
				exit;
			}
		}

		C($config);
/*		C('contact_qq', explode('|', C('contact_qq')));
		C('contact_qqun', explode('|', C('contact_qqun')));
		C('contact_bank', explode('|', C('contact_bank')));*/
		
		$coin = (APP_DEBUG ? null : S('home_coin'));
		if (!$coin) {
			$coin = M('Coin')->where(array('status' => 1))->select();
			S('home_coin', $coin);
		}

		$coinList = array();
		foreach ($coin as $k => $v) {
			$coinList['coin'][$v['name']] = $v;
			if ($v['name'] != Anchor_CNY) {
				$coinList['coin_list'][$v['name']] = $v;
			}
			if ($v['type'] == 'rmb') {
				$coinList['rmb_list'][$v['name']] = $v;
			} else {
				$coinList['xnb_list'][$v['name']] = $v;
			}
			if ($v['type'] == 'rgb') {
				$coinList['rgb_list'][$v['name']] = $v;
			}
			if ($v['type'] == 'qbb') {
				$coinList['qbb_list'][$v['name']] = $v;
			}
		}
		C($coinList);

		$market = (APP_DEBUG ? null : S('home_market'));
		if (!$market) {
			$market = M('Market')->where(array('status' => 1))->order('sort asc')->select();
			S('home_market', $market);
		}
		foreach ($market as $k => $v) {
			$v['new_price'] = round($v['new_price'], $v['round']);
			$v['buy_price'] = round($v['buy_price'], $v['round']);
			$v['sell_price'] = round($v['sell_price'], $v['round']);
			$v['min_price'] = round($v['min_price'], $v['round']);
			$v['max_price'] = round($v['max_price'], $v['round']);
			$v['xnb'] = explode('_', $v['name'])[0];
			$v['rmb'] = explode('_', $v['name'])[1];
			$v['xnbimg'] = C('coin')[$v['xnb']]['img'];
			$v['rmbimg'] = C('coin')[$v['rmb']]['img'];
			$v['volume'] = $v['volume'] * 1;
			$v['change'] = $v['change'] * 1;
			$v['title'] = C('coin')[$v['xnb']]['title'] . '(' . strtoupper($v['xnb']) . '/' . strtoupper($v['rmb']) . ')';

			$v['title_n'] = C('coin')[$v['xnb']]['title'];
			$v['title_ns'] = '(' . strtoupper($v['xnb']) . '/' . strtoupper($v['rmb']) . ')';
			$v['title_nsm'] = strtoupper($v['xnb']);

			$marketList['market'][$v['name']] = $v;
		}
		C($marketList);
		
		$C = C();
		foreach ($C as $k => $v) {
			$C[strtolower($k)] = $v;
		}
		$this->assign('C', $C);
		
/*		if (!S('daohang_aa')) {
			$tables = M()->query('show tables');
			$tableMap = array();

			foreach ($tables as $table) {
				$tableMap[reset($table)] = 1;
			}

			if (!isset($tableMap['tw_daohang'])) {
				M()->execute("\r\n" . 'CREATE TABLE `tw_daohang` (' . "\r\n" . ' `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'自增id\',' . "\r\n" . ' `name` VARCHAR(255) NOT NULL COMMENT \'名称\',' . "\r\n" . ' `title` VARCHAR(255) NOT NULL COMMENT \'名称\',' . "\r\n" . ' `url` VARCHAR(255) NOT NULL COMMENT \'url\',' . "\r\n" . ' `sort` INT(11) UNSIGNED NOT NULL COMMENT \'排序\',' . "\r\n" . ' `addtime` INT(11) UNSIGNED NOT NULL COMMENT \'添加时间\',' . "\r\n" . ' `endtime` INT(11) UNSIGNED NOT NULL COMMENT \'编辑时间\',' . "\r\n" . ' `status` TINYINT(4)  NOT NULL COMMENT \'状态\',' . "\r\n" . ' PRIMARY KEY (`id`)' . "\r\n\r\n" . ' )' . "\r\n" . 'COLLATE=\'gbk_chinese_ci\'' . "\r\n" . 'ENGINE=MyISAM' . "\r\n" . 'AUTO_INCREMENT=1' . "\r\n" . ';' . "\r\n\r\n\r\n\r\n" . 'INSERT INTO `tw_daohang` (`name`,`title`, `url`, `sort`, `status`) VALUES (\'finance\',\'财务中心\', \'Finance/index\', 1, 1);' . "\r\n" . 'INSERT INTO `tw_daohang` (`name`,`title`, `url`, `sort`, `status`) VALUES (\'user\',\'安全中心\', \'User/index\', 2, 1);' . "\r\n" . 'INSERT INTO `tw_daohang` (`name`, `title`,`url`, `sort`, `status`) VALUES (\'game\',\'应用中心\', \'Game/index\', 3, 1);' . "\r\n" . 'INSERT INTO `tw_daohang` (`name`, `title`,`url`, `sort`, `status`) VALUES (\'article\',\'帮助中心\', \'Article/index\', 4, 1);' . "\r\n\r\n\r\n" . ' ');
			}
			S('daohang_aa', 1);
		}*/
		
		// 顶部导航--------------------S
		if (!S('daohang_'.LANG_SET)) {
			$this->daohang = M('Daohang')->where(array('status' => 1,'lang'=>LANG_SET))->order('sort asc')->select();
			S('daohang_'.LANG_SET, $this->daohang);
		} else {
			$this->daohang = S('daohang_'.LANG_SET);
		}
		// 顶部导航--------------------E
		
		// 页脚导航--------------------S
		if (!S('footer_'.LANG_SET)) {
			$this->footer = M('footer')->where(array('status' => 1,'lang'=>LANG_SET))->order('sort asc')->select();
			S('footer_'.LANG_SET, $this->footer);
		} else {
			$this->footer = S('footer_'.LANG_SET);
		}
		// 页脚导航--------------------E
		
		$footerArticleType = (APP_DEBUG ? null : S('footer_indexArticleType'));
		if (!$footerArticleType || true) {
			$footerArticleType = M('ArticleType')->where(array('status' => 1, 'footer' => 1, 'shang' => array('like','help_%'),'lang'=>LANG_SET))->order('sort asc ,id desc')->limit(5)->select();
			S('footer_indexArticleType', $footerArticleType);
		}

		$this->assign('footerArticleType', $footerArticleType);
		$footerArticle = (APP_DEBUG ? null : S('footer_indexArticle'));
		if (!$footerArticle) {
			foreach ($footerArticleType as $k => $v) {
				 $second_class = M('ArticleType')->where(array('shang' => $v['name'], 'footer' => 1, 'status' => 1,'lang'=>LANG_SET))->order('id asc')->select();
				 if (!empty($second_class)) {
					 foreach ($second_class as $val){
						 $article_list = M('Article')->where(array('footer'=>1,'index'=>1,'status'=>1,'type'=>$val['name']))->limit(5)->select();
						 if (!empty($article_list)) {
							 foreach ($article_list as $kk=>$vv) {
								 $footerArticle[$v['name']][] = $vv;
							 }
						 }
					 }
				 } else {
					 $article_list = M('Article')->where(array('footer'=>1,'index'=>1,'status'=>1,'type'=>$v['name']))->limit(5)->select();
					 if (!empty($article_list)) {
						 foreach ($article_list as $kk=>$vv) {
							 $footerArticle[$v['name']][] = $vv;
						 }
					 }
				 }
			}
			S('footer_indexArticle', $footerArticle);
		}
		$this->assign('footerArticle', $footerArticle);
		
		// 底部友情链接--------------------S
		$footerindexLink = (APP_DEBUG ? null : S('index_indexLink'));
		if (!$footerindexLink) {
			$footerindexLink = M('Link')->where(array('status' => 1,'look_type'=>1))->order('sort asc ,id desc')->select();
		}
		$this->assign('footerindexLink', $footerindexLink);
		// 底部友情链接--------------------E

		// 官方公告 ----------------------S
		$news_list1 = M('Article')->where(array('status'=>1))->order('sort,endtime desc')->limit(3)->select();
		$this->assign('notice_list', $news_list1);
		// 官方公告 ----------------------n
		
		// 交易币种列表--------------------S
		$data = array();
		foreach (C('market') as $k => $v) {
			$v['xnb'] = explode('_', $v['name'])[0];
			$v['rmb'] = explode('_', $v['name'])[1];
			$data[$k]['name'] = $v['name'];
			$data[$k]['img'] = $v['xnbimg'];
			$data[$k]['title'] = $v['title'];
		}
		$this->assign('market_ss', $data);
		// 交易币种列表--------------------E

		//注册协议
		//$this->assign('registerAgreement',((LANG_SET=='zh-cn')?'/Article/detail/id/54.html':'/Article/detail/id/150.html'));
		$this->assign('registerAgreement','/Support/index/articles/cid/7/id/18.html');
		
		// 踢出内容中的标签
		//$notice_info['content'] = strip_tags($notice_info['content']);
	}
}
?>