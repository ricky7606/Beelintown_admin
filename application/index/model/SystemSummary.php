<?php
namespace app\index\model;

//导入模型类
use think\Model;

class SystemSummary extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_system_summary';

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

	public function getSummaryData(){
		$data_text = '';
		$dates = '';
		$user_nums = '';
		$qna_nums = '';
		$reply_nums = '';
		$arbitrate_nums = '';
		$coins_nums = '';
		$commission_nums = '';
		$extract_nums = '';
		$recharge_nums = '';
		$list = $this->order('summary_date','asc')
		->select();
		if($list){
			foreach($list as $data){
				$dates .= '"'.$data->summary_date.'",';
				$user_nums .= $data->user_count.',';
				$qna_nums .= $data->qna_count.',';
				$reply_nums .= $data->accept_reply_count.',';
				$arbitrate_nums .= $data->arbitrate_count.',';
				$coins_nums .= $data->coins_total.',';
				$commission_nums .= floatval($data->commission_total).',';
				$extract_nums .= floatval($data->extract_total).',';
				$recharge_nums .= floatval($data->recharge_total).',';
			}
		}
		$data_text = $dates.'###'.$user_nums.'###'.$qna_nums.'###'.$reply_nums.'###'.$arbitrate_nums.'###'.$coins_nums.'###'.$commission_nums.'###'.$extract_nums.'###'.$recharge_nums;
		return $data_text;
	}
	
	public function getCurrentData(){
		$current = $this->order('summary_date','desc')
		->limit(1)
		->find();
		return $current;
	}
}