<?php
namespace app\index\controller;
use think\Controller;//引入Controller类
use think\Session;
use think\Db;
use think\Request;
use think\Cookie;
use app\index\model\SystemSummary;
use app\index\model\Report;
use app\index\model\Transactions;
use app\index\model\QnasReplyDetails;

class Summary extends Controller
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
		
		$user_count = 0;
		$qna_count = 0;
		$accept_reply_count = 0;
		$arbitrate_count = 0;
		$coins_total = 0;
		$commission_total = 0;
		$extract_total = 0;
		$recharge_total = 0;
		$last_update = '';
		$dates = '';
		$user_nums = '';
		$summary = new SystemSummary;
		$current = $summary->getCurrentData();
		if($current){
			$user_count = $current->user_count;
			$qna_count = $current->qna_count;
			$accept_reply_count = $current->accept_reply_count;
			$arbitrate_count = $current->arbitrate_count;
			$coins_total = $current->coins_total;
			$commission_total = floatval($current->commission_total);
			$extract_total = floatval($current->extract_total);
			$recharge_total = floatval($current->recharge_total);
			$last_update = $current->update_date;
		}
		$data_list = $summary->getSummaryData();
		$dataArr = explode("###",$data_list);
		$this->assign('last_update',$last_update);
		$this->assign('user_count',$user_count);
		$this->assign('qna_count',$qna_count);
		$this->assign('accept_reply_count',$accept_reply_count);
		$this->assign('arbitrate_count',$arbitrate_count);
		$this->assign('coins_total',$coins_total);
		$this->assign('commission_total',$commission_total);
		$this->assign('extract_total',$extract_total);
		$this->assign('recharge_total',$recharge_total);
		$this->assign('Dates',$dataArr[0]);
		$this->assign('userNums',$dataArr[1]);
		$this->assign('qnaNums',$dataArr[2]);
		$this->assign('replyNums',$dataArr[3]);
		$this->assign('arbitrateNums',$dataArr[4]);
		$this->assign('coinsTotal',$dataArr[5]);
		$this->assign('commissionTotal',$dataArr[6]);
		$this->assign('extractTotal',$dataArr[7]);
		$this->assign('rechargeTotal',$dataArr[8]);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch();
    }

}