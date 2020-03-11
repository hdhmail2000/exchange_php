<?php
namespace Mobile\Controller;

class QueueController extends MobileController
{
    public function index()
    {
    }
	
	//匹配交易
    public function checkDapan()
    {
        // 处理开盘闭盘交易时间===开始
        $times = date('G', time());
        $minute = date('i', time());
        $minute = intval($minute);

        foreach (C('market') as $k => $v) {
            if (($times <= $v['start_time'] && $minute < intval($v['start_minute'])) || ($times > $v['stop_time'] && $minute >= intval($v['stop_minute']))) {
                continue;
            }
            if (($times < $v['start_time']) || $times > $v['stop_time']) {
                continue;
            } else {
                if ($times == $v['start_time']) {
                    if ($minute < intval($v['start_minute'])) {
                        continue;
                    }
                } elseif ($times == $v['stop_time']) {
                    if (($minute > $v['stop_minute'])) {
                        continue;
                    }
                }
            }
            // 处理周六周日是否可交易===开始
            $weeks = date('N', time());
            if (!$v['agree6']) {
                if ($weeks == 6) {
                    continue;
                }
            }
            if (!$v['agree7']) {
                if ($weeks == 7) {
                    continue;
                }
            }
            //处理周六周日是否可交易===结束
            A('Trade')->matchingTradeall($v['name']);//匹配交易
        }
    }
}
?>