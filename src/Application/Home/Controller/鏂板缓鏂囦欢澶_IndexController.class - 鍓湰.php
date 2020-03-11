<?php
namespace Home\Controller;

class IndexController extends HomeController
{
	protected function _initialize(){
		parent::_initialize();
		$allow_action=array("index","more");
		if(!in_array(ACTION_NAME,$allow_action)){
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
		
		// 交易币种--------------------S
		$co = array();
		foreach (C('market') as $k => $v) {

			$tendency = json_decode($v['tendency'], true);
			$co[$k]['data'] = $tendency;
			$co[$k]['yprice'] = $v['new_price'];
			$co[$k][0] = $v['title'];
			$co[$k][1] = round($v['new_price'], $v['round']);
			$co[$k][1] = sprintf("%.".$v['round']."f", $co[$k][1]);

			// 处理卖-价与买-价---------------------S
			$buy_qq = M('Trade')->where(array('type' => 1, 'market' => $k, 'status' => 0))->max('price');
			if (empty($buy_qq)) {
				$bbb = M('TradeLog')->where(array('market' => $k, 'status' => 1))->order('addtime desc')->find();
				$buy_qq = $bbb['price'];
			}
			$sell_qq = M('Trade')->where(array('type' => 2, 'market' => $k, 'status' => 0))->min('price');
			if (empty($sell_qq)) {
				$sss = M('TradeLog')->where(array('market' => $k, 'status' => 1))->order('addtime desc')->find();
				$sell_qq = $sss['price'];
			}
			// 处理卖-价与买-价---------------------E

			$co[$k][2] = round($buy_qq, $v['round']);
			$co[$k][3] = round($sell_qq, $v['round']);
			$co[$k][2] = sprintf("%.".$v['round']."f", $co[$k][2]);
			$co[$k][3] = sprintf("%.".$v['round']."f", $co[$k][3]);
			// $co[$k][2] = round($v['buy_price'], $v['round']);
			// $co[$k][3] = round($v['sell_price'], $v['round']);
			$co[$k][4] = round($v['volume'] * $v['new_price'], $v['round']);
			// $co[$k][4] = sprintf("%.".$v['round']."f", $co[$k][4]);
			if ($co[$k][4] > 10000 && $co[$k][4] < 100000000) {
				$co[$k][4] = (intval($co[$k][4] / 10000*100)/100) . "万";
			}
			if ($co[$k][4] > 100000000) {
				$co[$k][4] = (intval($co[$k][4] / 100000000*100)/100) . "亿";
			}
			$co[$k][5] = '';
			$co[$k][6] = round($v['volume'], 2) * 1;
			if ($co[$k][6] > 10000 && $co[$k][6] < 100000000) {
				$co[$k][6] = (intval($co[$k][6] / 10000*100)/100) . "万";
			}
			if ($co[$k][6] > 100000000) {
				$co[$k][6] = (intval($co[$k][6] / 100000000*100)/100) . "亿";
			}
			$co[$k][7] = round($v['change'], 2);
			if($co[$k][7]>0){
				$co[$k][7]='<b style="color:red">+'.$co[$k][7].'%</b>';
			} else {
				$co[$k][7]='<b style="color:#008069">'.$co[$k][7].'%</b>';
			}
			$co[$k][8] = $v['name'];
			$co[$k][9] = $v['xnbimg'];
			$co[$k][10] = '';
		}

		$this->assign('co', $co);
		// 交易币种--------------------E
		
		if(userid()){
			$CoinList = M('Coin')->where(array('status' => 1))->select();
			$UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();
			$Market = M('Market')->where(array('status' => 1))->select();

			foreach ($Market as $k => $v) {
				$Market[$v['name']] = $v;
			}

			$cny['zj'] = 0;

			foreach ($CoinList as $k => $v) {
				if ($v['name'] == Anchor_CNY) {
					$cny['ky'] = round($UserCoin[$v['name']], 2) * 1;
					$cny['dj'] = round($UserCoin[$v['name'] . 'd'], 2) * 1;
					$cny['zj'] = $cny['zj'] + $cny['ky'] + $cny['dj'];
				} else {
					if ($Market[$v['name'] . '_cnc']['new_price']) {
						$jia = $Market[$v['name'] . '_cnc']['new_price'];
					} else {
						$jia = 1;
					}

					$coinList[$v['name']] = array('name' => $v['name'], 'img' => $v['img'], 'title' => $v['title'] . '(' . strtoupper($v['name']) . ')', 'xnb' => round($UserCoin[$v['name']], 6) * 1, 'xnbd' => round($UserCoin[$v['name'] . 'd'], 6) * 1, 'xnbz' => round($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd'], 6), 'jia' => $jia * 1, 'zhehe' => round(($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia, 2));
					
					$cny['zj'] = round($cny['zj'] + (($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia), 2) * 1;
					$cny['zj'] = sprintf("%.2f", $cny['zj']);
				}
			}
			$cny['zj'] = number_format($cny['zj'],2);//千分位显示
			$this->assign('cny', $cny);
		}
		
		$this->display();
		/*if (C('index_html')) {
			$this->display('Index/index');
		}
		else {
			$this->display();
		}*/
	}

    public function more(){
        $this->display();
    }
}

?>