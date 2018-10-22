<?php 
	namespace Admin\Model;
	use Think\Model;
	class AdminMenuModel extends Model{
		protected $tableName = 'admin_menu';
		protected $fields = array("id","title","url","p_id"," ","hide");

		//自动验证 
		protected $_validate = array(
			array('title','',"标题名称以存在",'1','unique','1')
		);

		public function addMenu($data){
			$ret = M("admin_menu")->add($data);
			return $ret;
		}
		
		public function saveMenu($data,$id){
			$map['id']=$id;
			$ret =  M("admin_menu")->where($map)->save($data);
			if($ret === false){
				return false;
			}else{
				return true;
			}
		}
		
		public function getNextMenuList($id){
			$sql = "SELECT A.sort,A.hide, A.url, A.id, A.title,B.`title` AS p_title FROM mc_admin_menu A LEFT JOIN mc_admin_menu B ON A.`p_id` = B.`id` WHERE A.`p_id` = %d";
			$sqldata = M()->query($sql,$id);
			return $sqldata;
		}

		public function getFirstMenuList(){
			$sql = "SELECT A.sort,A.hide,A.url,A.id,A.title,IFNULL(B.`title`,'一级目录') AS p_title FROM mc_admin_menu A LEFT JOIN mc_admin_menu B ON A.`p_id` = B.`id` WHERE A.`p_id` = 0";
			$sqldata = M()->query($sql);
			return $sqldata;
		}
		public function getFirstMenuConfig(){
			$sql = "SELECT sort,id,title FROM mc_admin_menu WHERE p_id = 0";
			$data = array(array("id"=>"0","title"=>"一级目录"));
			$sqldata = M()->query($sql);
			return array_merge($data,$sqldata);
		}

		public function getThreeFun($id){
           return $this->where(array("p_id"=>$id,"level"=>3))->select();
        }

		public function getMenuInfo($id){
			$sql = "SELECT * FROM mc_admin_menu WHERE id = %d";
			$sqldata = M()->query($sql,$id);
			return $sqldata;
		}
	}
?>