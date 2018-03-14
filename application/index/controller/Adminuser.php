<?php
namespace app\index\controller;
use think\Controller;//引入Controller类
use think\Session;
use app\index\model\AdminUsers;
use app\index\model\Report;
use app\index\model\Transactions;
use app\index\model\QnasReplyDetails;
use app\index\model\AdminLogs;
use think\Db;
use think\Request;
use think\Cookie;
use PHPExcel_IOFactory;
use PHPExcel;
use PHPExcel_Writer_Excel5;

class Adminuser extends Controller
{
    public function index()
    {
		if(!Cookie::has('adminid') || Cookie::get('admin_type')!='1'){
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
		
		$admin = new AdminUsers;
		$admin_list = $admin->getAdminList();
		$this->assign('admin_list',$admin_list);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch();
    }
	
	public function changePassword(){
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
		
		$admin = new AdminUsers;
		$detail = $admin->getUserDetails(Cookie::get('adminid'));
		if($detail){
			$realname = $detail->realname;
			$username = $detail->username;
			$admin_type = $detail->admin_type;
			$enable= $detail->enable;
		}
		$this->assign('adminid',Cookie::get('adminid'));
		$this->assign('realname',$realname);
		$this->assign('username',$username);
		$this->assign('admin_type',$admin_type);
		$this->assign('enable',$enable);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
		return $this->fetch('changepassword');
	}
	
	public function updatePassword(){
		if(!Cookie::has('adminid')){
			return $this->redirect('/index');
		}
		$old_password = Request::instance()->post('old_password');
		$new_password = Request::instance()->post('new_password');
		$admin = new AdminUsers;
		$result = $admin->changePassword(Cookie::get('adminid'), $old_password, $new_password);
		if($result == "ok"){
			$log = new AdminLogs;
			$ip = Request::instance()->ip();
			$log->saveLog(Cookie::get('adminid'),$ip,6,"修改账户密码");
		}
		return $result;
	}

    public function details()
    {
		if(!Cookie::has('adminid') || Cookie::get('admin_type')!='1'){
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
		
		$adminid = Request::instance()->get('id');
		$realname = '';
		$username = '';
		$admin_type = '';
		$enable = '';
		if($adminid != ''){
			$admin = new AdminUsers;
			$detail = $admin->getUserDetails($adminid);
			if($detail){
				$realname = $detail->realname;
				$username = $detail->username;
				$admin_type = $detail->admin_type;
				$enable= $detail->enable;
			}
		}
		$this->assign('adminid',$adminid);
		$this->assign('realname',$realname);
		$this->assign('username',$username);
		$this->assign('user_admin_type',$admin_type);
		$this->assign('enable',$enable);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
        return $this->fetch('details');
    }
	
	public function update_user()
	{
		if(!Cookie::has('adminid') || Cookie::get('admin_type')!='1'){
			return $this->redirect('/index');
		}
		$adminid = Request::instance()->post('adminid');
		$realname = Request::instance()->post('realname');
		$username = Request::instance()->post('username');
		$password = Request::instance()->post('password');
		$admin_type = Request::instance()->post('admin_type');
		$enable = Request::instance()->post('enable');
		$admin = new AdminUsers;
		if($adminid != ''){
			$result = $admin->updateUser($adminid, $realname, $username, $password, $admin_type, $enable);
			if($result == "ok"){
				$log = new AdminLogs;
				$ip = Request::instance()->ip();
				$log->saveLog(Cookie::get('adminid'),$ip,6,"更新账户，账户用户名：".$username);
			}
			return $result;
		}else{
			$result = $admin->createUser($realname, $username, $password, $admin_type, $enable);
			if($result == "ok"){
				$log = new AdminLogs;
				$ip = Request::instance()->ip();
				$log->saveLog(Cookie::get('adminid'),$ip,6,"创建账户，账户用户名：".$username);
			}
			return $result;
		}
	}
	
	public function logs(){
		if(!Cookie::has('adminid') || Cookie::get('admin_type')!='1'){
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
		
		$adminid = Request::instance()->get('adminid');
		$log = new AdminLogs;
		$logs = $log->getLogs($adminid, false);
		$this->assign('admin_type',Cookie::get('admin_type'));
		$this->assign('admin_realname',Cookie::get('admin_realname'));
		$this->assign('admin_logs',$logs);
		$this->assign('adminid',$adminid);
        return $this->fetch('logs');
	}
	
	public function exportLogs(){
		if(!Cookie::has('adminid') || Cookie::get('admin_type')!='1'){
			return $this->redirect('/index');
		}
		
		$log = new AdminLogs;
		$ip = Request::instance()->ip();
		$log->saveLog(Cookie::get('adminid'),$ip,6,"下载管理操作日志");
		
		$adminid = Request::instance()->get('adminid');
		$log = new AdminLogs;
		$logs = $log->getLogs($adminid, true);
		
		require_once "/../vendor/phpexcel/Classes/PHPExcel.php";  
		require_once '/../vendor/phpexcel/Classes/PHPExcel/IOFactory.php';  
		require_once '/../vendor/phpexcel/Classes/PHPExcel/Writer/Excel5.php'; 
		
		//新建 
		$resultPHPExcel = new PHPExcel(); 
		//设置参数 
		//设值 
		$resultPHPExcel->getActiveSheet()->setCellValue('A1', '管理员姓名'); 
		$resultPHPExcel->getActiveSheet()->setCellValue('B1', '管理员用户名'); 
		$resultPHPExcel->getActiveSheet()->setCellValue('C1', '操作时间'); 
		$resultPHPExcel->getActiveSheet()->setCellValue('D1', 'IP'); 
		$resultPHPExcel->getActiveSheet()->setCellValue('E1', '操作类型'); 
		$resultPHPExcel->getActiveSheet()->setCellValue('F1', '操作说明'); 
		
		if($logs){
			$i=2;
			foreach($logs as $log){
				$resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, $log->realname); 
				$resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $log->username); 
				$resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, $log->log_date); 
				$resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, $log->log_ip); 
				$resultPHPExcel->getActiveSheet()->setCellValue('E' . $i, $log->log_type_desc); 
				$resultPHPExcel->getActiveSheet()->setCellValue('F' . $i, $log->log_detail); 
				$i++;
			}
		}else{
			$resultPHPExcel->getActiveSheet()->setCellValue('A3', '目前没有操作日志数据'); 
		}
		
		//设置导出文件名 
		$filename = "AdminLogs_".date('YmdHis').".xls";//文件名
		$objWriter = \PHPExcel_IOFactory::createWriter($resultPHPExcel, 'Excel5');  
		header('Content-Type: application/vnd.ms-excel');//告诉浏览器将要输出excel03文件  
		header('Content-Disposition: attachment;filename="'.$filename.'"');//告诉浏览器将输出文件的名称(文件下载)  
		header('Cache-Control: max-age=0');//禁止缓存  
		$objWriter->save("php://output");  	}
}