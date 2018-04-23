<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class tree extends Controller {
	function getTreeViewOptions(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$data = json_decode(file_get_contents('project/base/config/tree-view/'.$name.'.json'), true);
		$this->render->json($data);
	}

	function getData(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$json_data = json_decode(file_get_contents('project/base/config/tree-view/'.$name.'.json'), true);
		if(is_string($json_data['query'])){
			$query = $json_data['query'];
			$data['total_rows'] = 0;
			$data['data'] = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
		}else{
			$json_data = $json_data['query'];
			$table = $json_data['table'];
			$column = $json_data['column'];
			$where = $post_data['where'];
	
			if(isset($json_data['join'])){
				$join = $json_data['join'];
				$data['data'] = $this->db->select($table, $join, $column, $where);
			}else{
				$data['data'] = $this->db->select($table, $column, $where);
			}
		}
		$data['log'] = $this->db->log();
		$this->render->json($data);
	}
}
?>