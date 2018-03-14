<?php
namespace app\index\model;

//导入模型类
use think\Model;
use app\index\model\Users;
use app\index\model\ReportDetails;
use app\index\model\Message;
use app\index\model\Qnas;
use app\index\model\QnasReply;

class Report extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_report';

	//在子类重写父类的初始化方法initialize()
	protected function initialize(){
		
	  //继承父类中的initialize()
		parent::initialize();
		
	   //初始化数据表字段信息	
	   $this->field = $this->db()->getTableInfo('', 'fields');  
	
	   //初始化数据表字段类型
	   $this->type = $this->db()->getTableInfo('', 'type'); 
	
	   //初始化数据表主键
	   $this->pk = $this->db()->getTableInfo('', 'pk');     
		
		
		}

	public function saveReport($userid, $qnaid, $report_type, $qna_type, $report_comment){
		$result = $this->where('userid', $userid)
		->where('qnaid', $qnaid)
		->limit(1)
		->find();
		if($result){
			return "您已经举报了该文章";
		}else{
			$this->reportid = uuid();
			$this->userid = $userid; 
			$this->qnaid = $qnaid;
			$this->report_type = $report_type;
			$this->qna_type = $qna_type;
			$this->report_comment = $report_comment;
			$this->report_date = date('Y-m-d H:i:s',time());
			$result = $this->isUpdate(false)->save();
			if($result){
				return "ok";
			}else{
				return "发生错误";
			}
		}
	}
	
	public function doReport($reportid, $result_type, $result_comment){
		$error_flag = false;
		$this->startTrans();
		$this->reportid = $reportid;
		$this->result_type = $result_type;
		$this->result_comment = $result_comment;
		$result = $this->isUpdate(true)->save();
		if(!$result){
			$error_flag = true;
		}
		$report = new ReportDetails;
		$detail = $report->where('reportid', $reportid)
		->find();
		if($result_type == 1){
			if($detail->qna_type == 'qna'){
				$message_text = "您对于文章“<a href=\"\\index\\qnadetails?id=".$detail->qnaid."\" target=\"_blank\">".$detail->qna_title."</a>”的举报已经处理完毕，处理结果：举报不成立。";
				if($result_comment != ''){
					$message_text .= "处理说明：“".$result_comment."”";
				}
			}elseif($detail->qna_type == 'reply'){
				$message_text = "您对于文章“<a href=\"\\index\\qnareply?id=".$detail->qnaid."\" target=\"_blank\">".$detail->reply_title."</a>”的举报已经处理完毕，处理结果：举报不成立。";
				if($result_comment != ''){
					$message_text .= "处理说明：“".$result_comment."”";
				}
			}
			$message_text .= " 如仍有疑问可联系平台。";
			$message = new Message;
			$result_message = $message->saveNewMessage($detail->userid, $message_text);
			if(!$result_message){
				$error_flag = true;
			}
		}elseif($result_type == 2){
			if($detail->qna_type == 'qna'){
				$qna = new Qnas;
				$qna->qnaid = $detail->qnaid;
				$qna->report_disabled = 1;
				$qna_result = $qna->isUpdate(true)->save();
				if(!$qna_result){
					$error_flag = true;
				}
				$message_text1 = "您对于文章“".$detail->qna_title."”的举报已经处理完毕，处理结果：举报成立，内容被屏蔽。";
				$message_text2 = "您的文章“".$detail->qna_title."”被举报，举报理由：".getReportDesc($detail->report_type)."，已经处理完毕，处理结果：举报成立，内容被屏蔽。";
				if($result_comment != ''){
					$message_text1 .= "处理说明：“".$result_comment."”";
					$message_text2 .= "处理说明：“".$result_comment."”";
				}
				$article_userid = $detail->qna_userid;
			}elseif($detail->qna_type == 'reply'){
				$reply = new QnasReply;
				$reply->replyid = $detail->qnaid;
				$reply->report_disabled = 1;
				$reply_result = $reply->isUpdate(true)->save();
				if(!$reply_result){
					$error_flag = true;
				}
				$message_text1 = "您对于文章“".$detail->reply_title."”的举报已经处理完毕，处理结果：举报成立，内容被屏蔽。";
				$message_text2 = "您的文章“".$detail->reply_title."”被举报，举报理由：".getReportDesc($detail->report_type)."，已经处理完毕，处理结果：举报成立，内容被屏蔽。";
				if($result_comment != ''){
					$message_text1 .= "处理说明：“".$result_comment."”";
					$message_text2 .= "处理说明：“".$result_comment."”";
				}
				$article_userid = $detail->reply_userid;
			}
			$message_text1 .= " 如仍有疑问可联系平台。";
			$message_text2 .= " 如仍有疑问可联系平台。";
			$message = new Message;
			$result_message = $message->saveNewMessage($detail->userid, $message_text1);
			$result_message = $message->saveNewMessage($article_userid, $message_text2);
			if(!$result_message){
				$error_flag = true;
			}
		}
		if(!$error_flag){
			$this->commit();
			return "ok";
		}else{
			$this->rollBack();
			return "发生错误";
		}
	}
	
	public function getPendingReportCount(){
		$report_count = $this->where('result_type', 0)
		->field('count(*) as report_count')
		->find();
		return $report_count;
	}

	public function users()
    {
        return $this->belongsTo('Users','userid');
    }
}