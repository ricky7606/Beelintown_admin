<?php
namespace app\index\controller;
use think\Controller;//引入Controller类
use think\Session;
use app\index\model\Report;
use app\index\model\Transactions;
use app\index\model\QnasReplyDetails;
use think\Db;
use think\Request;
use think\Cookie;
use app\index\model\AdminLogs;

class Extractcoins extends Controller
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
		
		$tran_list = $trans->getExtractCoinsList('0,1');
		if($tran_list){
			foreach ($tran_list as $n=>$tran){ 
				$tran_list[$n]['statusDesc'] = getExtractDesc($tran->extract_status);
				$tran_list[$n]['formatCoins'] = floatval($tran->coins);
				$tran_list[$n]['user_coins'] = floatval($tran->user_coins);
				$tran_list[$n]['user_frozen_coins'] = floatval($tran->user_frozen_coins);
			}
		}
		$this->assign('tran_list',$tran_list);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch();
    }
	
	public function updateExtract($transactionid,$status,$serial_number){
		if($transactionid != '' and ($status == 1 || $status == 2 || $status == 3)){
			$transaction = new Transactions;
			$result_tran = $transaction->updateExtract($transactionid,$status,$serial_number);
			if($result_tran=="ok"){
				$log = new AdminLogs;
				$ip = Request::instance()->ip();
				$tmpStr = '';
				switch($status){
					case 1:
						$tmpStr = "受理提现申请，";
						break;
					case 2:
						$tmpStr = "通过提现申请，";
						break;
					case 3:
						$tmpStr = "拒绝提现申请，";
						break;
				}
				$log->saveLog(Cookie::get('adminid'),$ip,4,$tmpStr."参考ID：".$transactionid);
				return "ok";
			}else{
				return $result_tran;
			}
		}
	}

    public function updatedExtract()
    {

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
		
		$tran_list = $trans->getExtractCoinsList('2,3');
		if($tran_list){
			foreach ($tran_list as $n=>$tran){ 
				$tran_list[$n]['statusDesc'] = getExtractDesc($tran->extract_status);
				$tran_list[$n]['formatCoins'] = floatval($tran->coins);
			}
		}
		$this->assign('tran_list',$tran_list);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch();
    }
}