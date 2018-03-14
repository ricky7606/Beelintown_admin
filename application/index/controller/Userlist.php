<?php
namespace app\index\controller;
use think\Controller;//引入Controller类
use think\Session;
use app\index\model\Report;
use app\index\model\Transactions;
use app\index\model\QnasReplyDetails;
use app\index\model\Follow;
use app\index\model\Users;
use think\Db;
use think\Request;
use think\Cookie;

class Userlist extends Controller
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
		
		$search = Request::instance()->get('search');
		$sort_type = Request::instance()->get('sort_type');
		$user = new Users;
		$user_count = $user->getUserCount()->user_count;
		if($search != ''){
			$user_list = $user->getSearchUser($search);
		}else{
			if($sort_type == '' || ($sort_type != 1 && $sort_type != 2 && $sort_type != 3 && $sort_type != 4)){
				$sort_type = 1;
			}
			$user_list = $user->getUserSortList($sort_type);
		}
		$this->assign('user_count',$user_count);
		$this->assign('user_list',$user_list);
		$this->assign('sort_type',$sort_type);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch();
    }

}