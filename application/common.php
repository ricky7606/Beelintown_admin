<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//random() 函数返回随机整数。
function random($length = 6 , $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

//获取字符串中第一张图片的地址
function getThumbImg($str){
	$pattern="/<[img|IMG|Img].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/"; 
	preg_match_all($pattern,$str,$match); 
	if(count($match[1])>0){
		return $match[1][0];
	}else{
		return false;
	}
}

//返回截取一定长度后的字符串
function getContentText($str,$length){
	if(mb_strwidth($str, 'utf8')>$length){
		$str = mb_strimwidth($str, 0, $length, '...', 'utf8');
	}
	return $str;
}

//生成唯一的主键ID
function uuid($prefix = '')  
  {  
    $chars = md5(uniqid(mt_rand(), true));  
    $uuid  = substr($chars,0,8) . '-';  
    $uuid .= substr($chars,8,4) . '-';  
    $uuid .= substr($chars,12,4) . '-';  
    $uuid .= substr($chars,16,4) . '-';  
    $uuid .= substr($chars,20,12);  
    return $prefix . $uuid;  
  }    

//返回交易类型说明
function getTransactionDesc($transaction_type){
	switch($transaction_type){
		case 0:
			return "新用户注册奖励";
			break;
		case 1:
			return "问答悬赏冻结";
			break;
		case 2:
			return "问答佣金冻结";
			break;
		case 3:
			return "问答悬赏解冻";
			break;
		case 4:
			return "问答佣金解冻";
			break;
		case 5:
			return "问答悬赏支付";
			break;
		case 6:
			return "问答悬赏佣金扣除";
			break;
		case 7:
			return "问答收入佣金扣除";
			break;
		case 8:
			return "问答分享奖励";
			break;
		case 9:
			return "问答悬赏入账";
			break;
		case 10:
			return "问答仲裁处理解冻";
			break;
		case 11:
			return "问答仲裁处理支付";
			break;
		case 12:
			return "问答仲裁处理入账";
			break;
		case 13:
			return "提现冻结";
			break;
		case 14:
			return "提现成功";
			break;
		case 15:
			return "提现失败，解冻返回";
			break;
		case 99:
			return "系统操作扣除";
			break;
	}
}

//返回提现状态说明
function getExtractDesc($extract_status){
	switch($extract_status){
		case 0:
			return "未处理";
			break;
		case 1:
			return "已受理";
			break;
		case 2:
			return "提现成功";
			break;
		case 3:
			return "提现拒绝";
			break;
	}
}

//获取当前时间精确到微妙
function udate($format = 'u', $utimestamp = null) {
	if (is_null($utimestamp))
	   $utimestamp = microtime(true);
	
	$timestamp = floor($utimestamp);
	$milliseconds = round(($utimestamp - $timestamp) * 1000000);
	
	return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
}

function getReportDesc($report_type){
	$report_desc = '';
	switch($report_type){
		case 1:
			$report_desc = "辱骂、歧视、挑衅、骚扰等语言行为";
			break;
		case 2:
			$report_desc = "内容抄袭";
			break;
		case 3:
			$report_desc = "侵犯个人或企业权益";
			break;
		case 4:
			$report_desc = "垃圾广告信息";
			break;
		case 5:
			$report_desc = "色情、暴力、血腥等违法违规内容";
			break;
		case 6:
			$report_desc = "政治敏感内容";
			break;
		case 7:
			$report_desc = "诱导分享、投票等行为";
			break;
	}
	return $report_desc;
}

function getResultDesc($result_type){
	$result_desc = '';
	switch($result_type){
		case 1:
			$result_desc = "举报不成立";
			break;
		case 2:
			$result_desc = "举报成立，相关内容屏蔽处理";
			break;
	}
	return $result_desc;
}
