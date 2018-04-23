<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class table extends Controller {
	
	function getTableViewOptions(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$data = null;
		if(file_exists('project/base/config/table-view/'.id_role.'/'.$name.'.json')){
			$data = json_decode(file_get_contents('project/base/config/table-view/'.id_role.'/'.$name.'.json'), true);
		} else if(file_exists('project/base/config/table-view/'.$name.'.json')) {
			$data = json_decode(file_get_contents('project/base/config/table-view/'.$name.'.json'), true);
		}
		$this->render->json($data);
	}

	function getData(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$json_data = null;
		if(file_exists('project/base/config/table-view/'.id_role.'/'.$name.'.json')){
			$json_data = json_decode(file_get_contents('project/base/config/table-view/'.id_role.'/'.$name.'.json'), true);
		} else if(file_exists('project/base/config/table-view/'.$name.'.json')){
			$json_data = json_decode(file_get_contents('project/base/config/table-view/'.$name.'.json'), true);
		}
		if(is_string($json_data['query'])){
			$query = $json_data['query'];
			$where = "";
			if(isset($post_data['where'])) $where = $post_data['where'];
			if(is_array($where)){
				$where_key = array();
				$where_value = array();
				foreach($where as $wk=>$wv){
					$where_key[] = '$'.$wk;
					$where_value[] = $wv;
				}
				$query = str_replace($where_key, $where_value, $query);
				$where = "";
			}
			$data['total_rows'] = 0;
			$data['data'] = $this->db->query($query." ".$where);
			if($data['data']) $data['data'] = $data['data']->fetchAll(PDO::FETCH_ASSOC);
			else $data['data'] = array();
		}else{
			$json_data = $json_data['query'];
			$table = $json_data['table'];
			$column = $json_data['column'];
			$where = $post_data['where'];
	
			if(isset($json_data['join'])){
				$join = $json_data['join'];
				$count_where = $where;
				unset($count_where["ORDER"]);
				unset($count_where["LIMIT"]);
				$data['total_rows'] = $this->db->count($table, $join, $column[0], $count_where);
				$data['data'] = $this->db->select($table, $join, $column, $where);
			}else{
				$count_where = $where;
				unset($count_where["ORDER"]);
				unset($count_where["LIMIT"]);
				$data['total_rows'] = $this->db->count($table, $column[0], $count_where);
				$data['data'] = $this->db->select($table, $column, $where);
			}
		}
		$data['log'] = $this->db->log();
		$this->render->json($data);
	}
	
	function submitDelete(){
		$post_data = $this->render->json_post();
		$table = $post_data['table'];
		$where = $post_data['where'];

		$deleted_column = $this->db->query("SHOW COLUMNS FROM ".$table." LIKE 'deleted'")->fetchAll();
		if(count($deleted_column)>0){
			if($this->db->update($table, array("deleted"=>1), $where)){
				$this->set->success_message(true);
			}
		}else{
			if($this->db->delete($table, $where)){
				$this->set->success_message(true);
			}
		}
		$this->set->error_message(true, $this->db->log());
	}

	function recoverDelete(){
		$post_data = $this->render->json_post();
		$table = $post_data['table'];
		$where = $post_data['where'];

		$deleted_column = $this->db->query("SHOW COLUMNS FROM ".$table." LIKE 'deleted'")->fetchAll();
		if(count($deleted_column)>0){
			if($this->db->update($table, array("deleted"=>0), $where)){
				$this->set->success_message(true);
			}
		}
		$this->set->error_message(true, $this->db->log());
	}

	function permanentDelete(){
		$post_data = $this->render->json_post();
		$table = $post_data['table'];
		$where = $post_data['where'];

		if($this->db->delete($table, $where)){
			$this->set->success_message(true);
		}
		$this->set->error_message(true, $this->db->log());
	}
}
?>