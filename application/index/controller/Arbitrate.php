<?php
namespace app\index\controller;
use think\Controller;//引入Controller类
use think\Session;
use app\index\model\Report;
use app\index\model\Transactions;
use app\index\model\QnasReplyDetails;
use app\index\model\Follow;
use app\index\model\UserTagDetails;
use app\index\model\Users;
use think\Db;
use think\Request;
use think\Cookie;
use app\index\model\ReplyAdditionDetails;
use app\index\model\AdminLogs;

class Arbitrate extends Controller
{
    public function index()
    {
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		$report = new Report;
		$report_count = $report->getPendingReportCount();
		$this->assign('pending_report_count',$report_count->report_count);
		$trans = new Transactions;
		$trans_count = $trans->getPendingExtractCount();
		$this->assign('pending_extract_count',$trans_count->extract_count);
		$arbitrate = new QnasReplyDetails;
		$arbitrate_count = $arbitrate->getPendingArbitrateCount();
		$this->assign('pending_arbitrate_count',$arbitrate_count->arbitrate_count);
		$this->assign('total_pending_count',$arbitrate_count->arbitrate_count+$trans_count->extract_count+$report_count->report_count);

		$arbitrate_list = $arbitrate->getArbitrateList(7);
		$this->assign('arbitrate_list',$arbitrate_list);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch();
    }

    public function processed()
    {
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		$report = new Report;
		$report_count = $report->getPendingReportCount();
		$this->assign('pending_report_count',$report_count->report_count);
		$trans = new Transactions;
		$trans_count = $trans->getPendingExtractCount();
		$this->assign('pending_extract_count',$trans_count->extract_count);
		$arbitrate = new QnasReplyDetails;
		$arbitrate_count = $arbitrate->getPendingArbitrateCount();
		$this->assign('pending_arbitrate_count',$arbitrate_count->arbitrate_count);
		$this->assign('total_pending_count',$arbitrate_count->arbitrate_count+$trans_count->extract_count+$report_count->report_count);
		$arbitrate_list = $arbitrate->getArbitrateList(8);
		if($arbitrate_list){
			foreach($arbitrate_list as $arbitrate){
				$arbitrate->user_pay = floatval($arbitrate->arbitrate_qnauser_coins);
				$arbitrate->system_pay = floatval(bcsub($arbitrate->arbitrate_coins, $arbitrate->arbitrate_qnauser_coins, 8));
			}
		}
		$this->assign('arbitrate_list',$arbitrate_list);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch('processed');
    }

	public function details(){
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		$replyid = Request::instance()->get('id');
		if($replyid == ''){
			return $this->redirect('/index/arbitrate');
		}

		$report = new Report;
		$report_count = $report->getPendingReportCount();
		$this->assign('pending_report_count',$report_count->report_count);
		$trans = new Transactions;
		$trans_count = $trans->getPendingExtractCount();
		$this->assign('pending_extract_count',$trans_count->extract_count);
		$arbitrate = new QnasReplyDetails;
		$arbitrate_count = $arbitrate->getPendingArbitrateCount();
		$this->assign('pending_arbitrate_count',$arbitrate_count->arbitrate_count);
		$this->assign('total_pending_count',$arbitrate_count->arbitrate_count+$trans_count->extract_count+$report_count->report_count);

		$reply = new QnasReplyDetails;
		$reply_detail = $reply->getTopReplyDetailsByReplyId($replyid); 
		if(!$reply_detail){
			return $this->redirect('/index/arbitrate');
		}
		$addition = new ReplyAdditionDetails;
		$reply_detail->addition = $addition->getReplyAdditions($reply_detail->replyid);
		$follow = new Follow;
		$follow_count = $follow->getFollowCount($reply_detail->qnaid);
		$reply_detail->followCount = $follow_count->followCount;
		$user_tag = new UserTagDetails;
		$qna_tag_list = $user_tag->getTagListByUserId($reply_detail->qna_userid);
		$reply_tag_list = $user_tag->getTagListByUserId($reply_detail->userid);
		$this->assign('qna_tag_list',$qna_tag_list);
		$this->assign('reply_tag_list',$reply_tag_list);
		$userdetail = new Users;
		$qna_userinfo = $userdetail->getUserDetails($reply_detail->qna_userid);
		$reply_userinfo = $userdetail->getUserDetails($reply_detail->userid);
		$this->assign('qna_userinfo',$qna_userinfo);
		$this->assign('reply_userinfo',$reply_userinfo);
		$reply_detail['formatCoins'] = floatval($reply_detail->qna_coins);
		$this->assign('reply_detail',$reply_detail);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch('details'); 
	}
	
	public function doArbitrate(){
		$user_pay = Request::instance()->post('user_pay');
		$system_pay = Request::instance()->post('system_pay');
		$comment = Request::instance()->post('comment');
		$replyid = Request::instance()->post('replyid');
		if($replyid != ''){
			$reply=new QnasReplyDetails;
			$result = $reply->doArbitrate($replyid, floatval($user_pay), floatval($system_pay), $comment);
			if($result == "ok"){
				$log = new AdminLogs;
				$ip = Request::instance()->ip();
				$log->saveLog(Cookie::get('adminid'),$ip,2,"作出仲裁处理，参考ID：".$replyid);
			}
			return $result;
		}else{
			return "数据错误";
		}
	}
}