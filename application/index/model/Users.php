<?php
namespace app\index\model;

//导入模型类
use think\Model;
use think\Cookie;
use app\index\model\Transactions;
use app\index\model\UserTags;
use app\index\model\QnasPending;
use app\index\model\QnaPendingDetails;
use app\index\model\Qnas;

class Users extends Model {
	
	// 设置当前模型对应的完整数据表名称
    protected $table = 'BLT_user';
	
	//protected $resultSetType = 'collection';

	//在子类重写父类的初始化方法initialize()
	protected function initialize(){
		
	  //继承父类中的initialize()
		parent::initialize();
		
	   //初始化数据表字段信息	
	   $this->field = $this->db()->getTableInfo('', 'fields');  
	
	   //初始化数据表字段类型
	   $this->type = $this->db()->getTableInfo('', 'type'); 
	
	   
	   $this->pk = $this->db()->getTableInfo('', 'pk');     
		}

     public function qnas()
    {
        return $this->hasMany('Qnas','userid');//对应多条问答记录
    }
	
	public function getUserCount(){
		$user = $this->field('count(*) as user_count')
		->find();
		return $user;
	}

	public function getMonthUserCount(){
		$new_user = $this->where('','exp','YEAR(reg_date)=YEAR(CURDATE()) and MONTH(reg_date)=MONTH(CURDATE())')
		->field('count(*) as user_count')
		->find();
		return $new_user;
	}
	
	public function getUserSortList($sort_type){
		$qna = new Qnas;
		$pending = new QnasPending;
		switch($sort_type){
			case 1:
				$user_list = $this->order('reg_date','desc')
				->paginate(20,false,['query'=>request()->param()]);
				if($user_list){
					foreach($user_list as $user){
						$qna_count = $qna->where('userid', $user->userid)
						->field('count(*) as qna_count')
						->find();
						$user->qna_count = $qna_count->qna_count;
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status','in','3,4,5,6,7,8')
						->field('count(*) as reply_count')
						->find();
						$user->reply_count = $reply_count->reply_count;
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status','4')
						->field('count(*) as accept_reply_count')
						->find();
						$user->accept_reply_count = $reply_count->accept_reply_count;
						$user->formatCoins = floatval($user->coins);
						$user->formatFrozen_coins = floatval($user->frozen_coins);
					}
				}
				return $user_list;
				break;
			case 2:
				$user_list = $this->order('coins','desc')
				->paginate(20,false,['query'=>request()->param()]);
				if($user_list){
					foreach($user_list as $user){
						$qna_count = $qna->where('userid', $user->userid)
						->field('count(*) as qna_count')
						->find();
						$user->qna_count = $qna_count->qna_count;
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status','in','3,4,5,6,7,8')
						->field('count(*) as reply_count')
						->find();
						$user->reply_count = $reply_count->reply_count;
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status','4')
						->field('count(*) as accept_reply_count')
						->find();
						$user->accept_reply_count = $reply_count->accept_reply_count;
						$user->formatCoins = floatval($user->coins);
						$user->formatFrozen_coins = floatval($user->frozen_coins);
					}
				}
				return $user_list;
				break;
			case 3:
				$user_list = $qna->field('userid, count(*) as qna_count')
				->group('userid')
				->order('count(*)','desc')
				->paginate(20,false,['query'=>request()->param()]);
				if($user_list){
					foreach($user_list as $user){
						$user_detail = $this->where('userid', $user->userid)
						->field('mobile,username,coins,frozen_coins,reg_date')
						->find();
						$user->mobile = $user_detail['mobile'];
						$user->username = $user_detail['username'];
						$user->reg_date = $user_detail['reg_date'];
						$user->formatCoins = floatval($user_detail['coins']);
						$user->formatFrozen_coins = floatval($user_detail['frozen_coins']);
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status','in','3,4,5,6,7,8')
						->field('count(*) as reply_count')
						->find();
						$user->reply_count = $reply_count->reply_count;
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status',4)
						->field('count(*) as accept_reply_count')
						->find();
						$user->accept_reply_count = $reply_count->accept_reply_count;
					}
				}
				return $user_list;
				break;
			case 4:
				$user_list = $pending->where('status','in','3,4,5,6,7,8')
				->group('pending_userid')
				->field('count(*) as reply_count,pending_userid as userid')
				->order('count(*)','desc')
				->paginate(20,false,['query'=>request()->param()]);
				if($user_list){
					foreach($user_list as $user){
						$user_detail = $this->where('userid', $user->userid)
						->field('mobile,username,coins,frozen_coins,reg_date')
						->find();
						$user->mobile = $user_detail['mobile'];
						$user->username = $user_detail['username'];
						$user->reg_date = $user_detail['reg_date'];
						$user->formatCoins = floatval($user_detail['coins']);
						$user->formatFrozen_coins = floatval($user_detail['frozen_coins']);
						$qna_count = $qna->where('userid', $user->userid)
						->field('count(*) as qna_count')
						->find();
						$user->qna_count = $qna_count->qna_count;
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status',4)
						->field('count(*) as accept_reply_count')
						->find();
						$user->accept_reply_count = $reply_count->accept_reply_count;
					}
				}
				return $user_list;
				break;
			default:
				$user_list = $this->order('reg_date','desc')
				->paginate(20,false,['query'=>request()->param()]);
				if($user_list){
					foreach($user_list as $user){
						$qna_count = $qna->where('userid', $user->userid)
						->field('count(*) as qna_count')
						->find();
						$user->qna_count = $qna_count->qna_count;
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status','in','3,4,5,6,7,8')
						->field('count(*) as reply_count')
						->find();
						$user->reply_count = $reply_count->reply_count;
						$reply_count = $pending->where('pending_userid', $user->userid)
						->where('status','4')
						->field('count(*) as accept_reply_count')
						->find();
						$user->accept_reply_count = $reply_count->accept_reply_count;
						$user->formatCoins = floatval($user->coins);
						$user->formatFrozen_coins = floatval($user->frozen_coins);
					}
				}
				return $user_list;
				break;
		}
	}

	public function getSearchUser($search){
		$qna = new Qnas;
		$pending = new QnasPending;
        $user_list = $this->where('username','like','%'.$search.'%')
		->whereOr('mobile','like','%'.$search.'%')
		->order('reg_date','desc')
		->paginate(20,false,['query'=>request()->param()]);         // 查询用户
		if($user_list){
			foreach($user_list as $user){
				$qna_count = $qna->where('userid', $user->userid)
				->field('count(*) as qna_count')
				->find();
				$user->qna_count = $qna_count->qna_count;
				$reply_count = $pending->where('pending_userid', $user->userid)
				->where('status','in','3,4,5,6,7,8')
				->field('count(*) as reply_count')
				->find();
				$user->reply_count = $reply_count->reply_count;
				$reply_count = $pending->where('pending_userid', $user->userid)
				->where('status','4')
				->field('count(*) as accept_reply_count')
				->find();
				$user->accept_reply_count = $reply_count->accept_reply_count;
				$user->formatCoins = floatval($user->coins);
				$user->formatFrozen_coins = floatval($user->frozen_coins);
			}
		}
		return $user_list;
	}
	
	public function getAttUsers($userid){
        $att = new AttentionDetails;
		$att_users = $att->where('userid', $userid)
		->field('attention_userid,attention_username')
		->order('attention_date','desc')
		->select();          // 查询随机用户
        if (empty($att_users)) {                 // 判断是否出错
            return false;
        }
        return $att_users;   // 返回修改后的数据
	}
	
	public function getUserIdByUsername($username){
        $search_users = $this->where(function ($query) use ($username){
		$query->where('username',$username)
		->field('userid');
		})->find();          // 查询用户
        if (empty($search_users)) {                 // 判断是否出错
            return false;
        }
        return $search_users->userid;   // 返回修改后的数据		
	}
	
	public function getUserDetails($userid){
        $users = $this->where('userid',$userid)
		->field('*,pending_reminder+reply_apply_reminder+answer_reminder+attention_reminder+transaction_reminder+message_reminder as message_count')
		->find();          
        if (empty($users)) {                 // 判断是否出错
            return false;
        }
        return $users;   // 返回修改后的数据
	}
	
	public function chkMobile($mobile){
		$chkMobile = $this->where(function ($query) use ($mobile){
			$query->where('mobile',$mobile);
		})->find();
		if($chkMobile)
		{
			return "exists";
		}else{
			return "none";
		}
	}
	
	public function chkUsername($username){
		$chkUsername = $this->where(function ($query) use ($username){
			$query->where('username',$username);
		})->find();
		if($chkUsername)
		{
			return "exists";
		}else{
			return "none";
		}
	}

	public function addNewTransactionReminder($userid, $num = 1){
		$this->where('userid', $userid)->setInc('transaction_reminder',$num);
	}
}