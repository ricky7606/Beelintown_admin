<?php
namespace app\index\model;

//导入模型类
use think\Model;

class MessageDetails extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'view_MessageDetails';

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

	public function users()
    {
        return $this->belongsTo('Users','userid');
    }
}