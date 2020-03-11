<?php
namespace Mobile\Controller;

class MorefindController extends MobileController
{
	public function index()
	{

        if (!userid()) {
            redirect('/Login/index.html');
        }

        $CoinList = M('Coin')->where(array('status' => 1))->select();
        $UserCoin = M('UserCoin')->where(array('userid' => userid()))->find();
        $Market = M('Market')->where(array('status' => 1))->select();

        foreach ($Market as $k => $v) {
            $Market[$v['name']] = $v;
        }

        $cny['zj'] = 0;
        foreach ($CoinList as $k => $v) {
            if ($v['name'] == 'cny') {
                $cny['ky'] = round($UserCoin[$v['name']], 2) * 1;
                $cny['dj'] = round($UserCoin[$v['name'] . 'd'], 2) * 1;
                $cny['zj'] = $cny['zj'] + $cny['ky'] + $cny['dj'];
            } else {
                if ($Market[$v['name'].'_'.Anchor_CNY]['new_price']) {
                    $jia = $Market[$v['name'].'_'.Anchor_CNY]['new_price'];
                } elseif($v['name'] == 'mobi'){
                    $exchange_price = M('exchange_config')->where(array('id' => 1))->getField('mycz_uprice');
                    $jia = $exchange_price;
                } else{
                    $jia = 1;
                }

                $cny['zj'] = round($cny['zj'] + (($UserCoin[$v['name']] + $UserCoin[$v['name'] . 'd']) * $jia), 2) * 1;
            }
        }
        $cny['dj'] = sprintf("%.2f", $cny['dj']);
        $cny['ky'] = sprintf("%.2f", $cny['ky']);
        $cny['zj'] = sprintf("%.2f", $cny['zj']);
        $cny['dj'] = number_format($cny['dj'],2);//千分位显示
        $cny['ky'] = number_format($cny['ky'],2);//千分位显示

        $this->assign('cny', $cny);

        $this->display();
	}
}