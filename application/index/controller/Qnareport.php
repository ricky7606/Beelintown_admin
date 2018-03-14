<?php
namespace app\index\controller;
use think\Controller;//引入Controller类
use think\Session;
use app\index\model\Follow;
use app\index\model\UserTagDetails;
use app\index\model\Users;
use think\Db;
use think\Request;
use think\Cookie;
use app\index\model\ReportDetails;
use app\index\model\Report;
use app\index\model\Transactions;
use app\index\model\QnasReplyDetails;
use app\index\model\ReplyAdditionDetails;
use app\index\model\AdminLogs;

class Qnareport extends Controller
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

		$report = new ReportDetails;
		$report_list = $report->getReportList();
		if($report_list){
			foreach($report_list as $report_detail){
				$report_detail->report_type_desc = getReportDesc($report_detail->report_type);
			}
		}
		$this->assign('report_list',$report_list);
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
		
		$report = new ReportDetails;
		$report_list = $report->getReportList('1,2');
		if($report_list){
			foreach($report_list as $report_detail){
				$report_detail->report_type_desc = getReportDesc($report_detail->report_type);
				$report_detail->result_type_desc = getResultDesc($report_detail->result_type);
			}
		}
		$this->assign('report_list',$report_list);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch('processed');
    }
	

	public function details(){
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		$reportid = Request::instance()->get('id');
		if($reportid == ''){
			return $this->redirect('/index/qnareport');
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
		
		$report = new ReportDetails;
		$report_detail = $report->getReportDetails($reportid); 
		if(!$report_detail){
			return $this->redirect('/index/qnareport');
		}
		$report_detail->report_type_desc = getReportDesc($report_detail->report_type);
		if($report_detail->qna_type == 'qna'){
			$follow = new Follow;
			$follow_count = $follow->getFollowCount($report_detail->qnaid);
			$report_detail->followCount = $follow_count->followCount;
			$user_tag = new UserTagDetails;
			$qna_tag_list = $user_tag->getTagListByUserId($report_detail->qna_userid);
			$this->assign('qna_tag_list',$qna_tag_list);
			$userdetail = new Users;
			$qna_userinfo = $userdetail->getUserDetails($report_detail->qna_userid);
			$this->assign('qna_userinfo',$qna_userinfo);
			$report_detail->formatCoins = floatval($report_detail->qna_coins);
		}else{
			$addition = new ReplyAdditionDetails;
			$report_detail->addition = $addition->getReplyAdditions($report_detail->qnaid);
			$user_tag = new UserTagDetails;
			$reply_tag_list = $user_tag->getTagListByUserId($report_detail->reply_userid);
			$this->assign('reply_tag_list',$reply_tag_list);
			$userdetail = new Users;
			$reply_userinfo = $userdetail->getUserDetails($report_detail->reply_userid);
			$this->assign('reply_userinfo',$reply_userinfo);
			$report_detail->formatCoins = floatval($report_detail->reply_coins);
		}
		$this->assign('report_detail',$report_detail);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch('details'); 
	}
	
	public function doReport(){
		$result_type = Request::instance()->post('result_type');
		$result_comment = Request::instance()->post('result_comment');
		$reportid = Request::instance()->post('reportid');
		if($reportid != '' && ($result_type == 1 || $result_type == 2)){
			$report=new Report;
			$result = $report->doReport($reportid, $result_type, $result_comment);
			if($result == "ok"){
				$log = new AdminLogs;
				$ip = Request::instance()->ip();
				$log->saveLog(Cookie::get('adminid'),$ip,3,"作出举报处理，参考ID：".$reportid);
			}
			return $result;
		}else{
			return "数据错误";
		}
	}
}