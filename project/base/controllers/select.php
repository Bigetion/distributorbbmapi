<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class select extends Controller {

	function getSelectViewOptions(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$data = json_decode(file_get_contents('project/base/config/select-view/'.$name.'.json'), true);
		$this->render->json($data);
	}

	function getData(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$json_data = json_decode(file_get_contents('project/base/config/select-view/'.$name.'.json'), true);
		if(is_string($json_data['query'])){
			$query = $json_data['query'];
			$data['total_rows'] = 0;
			$data['data'] = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
		}else{
			$json_data = $json_data['query'];
			$table = $json_data['table'];
			$column = $json_data['column'];
			$where = null;
			if(isset($post_data['where'])) $where = $post_data['where'];
	
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
}
?>