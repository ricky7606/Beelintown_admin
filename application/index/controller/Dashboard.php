<?php
namespace app\index\controller;
use think\Controller;//引入Controller类
use think\Session;
use app\index\model\Qnas;
use app\index\model\QnasPending;
use app\index\model\Users;
use app\index\model\Report;
use app\index\model\Transactions;
use app\index\model\QnasReplyDetails;
use think\Db;
use think\Request;
use think\Cookie;

class Dashboard extends Controller
{
    public function index()
    {
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		$qna = new Qnas;
		$pending = new QnasPending;
		$user = new Users;
		$new_qna = $qna->getMonthQnaCount()->qna_count;
		$new_accept = $pending->getMonthAcceptCount()->accept_count;
		$new_arbitrate = $pending->getMonthArbitrateCount()->arbitrate_count;
		$new_user = $user->getMonthUserCount()->user_count;

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
		
		$this->assign('qna_count',$new_qna);
		$this->assign('accept_count',$new_accept);
		$this->assign('arbitrate_count',$new_arbitrate);
		$this->assign('user_count',$new_user);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch();
    }
	
}