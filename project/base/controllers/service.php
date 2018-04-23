<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class service extends Controller {

	function getQueryServiceOptions(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		
		if(file_exists('project/base/config/query-service/'.id_role.'/'.$name.'.json')){
			$data = json_decode(file_get_contents('project/base/config/query-service/'.id_role.'/'.$name.'.json'), true);
		} else if(file_exists('project/base/config/query-service/'.$name.'.json')) {
			$data = json_decode(file_get_contents('project/base/config/query-service/'.$name.'.json'), true);
		}
		$this->render->json($data);
	}

	private function getDataByJson($query, $where) {
		if(is_string($query)){
			$data = $this->db->query($query." ".$where);
			if($data) $data = $data->fetchAll(PDO::FETCH_ASSOC);
		}else{
			$table = $query['table'];
			$column = $query['column'];
	
			if(isset($query['join'])){
				$join = $query['join'];
				if(isset($query['type'])){
					$type = $query['type'];
					switch ($type) {
						case 'sum' : 
							$data = $this->db->sum($table, $join, $column, $where);
							break;
						default:
							$data = $this->db->select($table, $join, $column, $where);
					}
				}else{
					$data = $this->db->select($table, $join, $column, $where);
				}
			}else{
				if(isset($query['type'])){
					$type = $query['type'];
					switch ($type) {
						case 'sum' : 
							$data = $this->db->sum($table, $column, $where);
							break;
						default:
							$data = $this->db->select($table, $column, $where);
					}
				}else{
					$data = $this->db->select($table, $column, $where);
				}
			}
		}
		return $data;
	}

	function getData(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$json_data = false;

		if(file_exists('project/base/config/query-service/'.id_role.'/'.$name.'.json')){
			$json_data = json_decode(file_get_contents('project/base/config/query-service/'.id_role.'/'.$name.'.json'), true);
		} else if(file_exists('project/base/config/query-service/'.$name.'.json')) {
			$json_data = json_decode(file_get_contents('project/base/config/query-service/'.$name.'.json'), true);
		}

		$data['data'] = array();

		if($json_data){
			if(is_array($json_data['query'])){
				$array_keys = array_keys($json_data['query']);
				if($array_keys[0] === 0){
					$query = $json_data['query'];
					foreach($query as $key=>$q){
						if(is_string($q)){
							$where = "";
							if(isset($post_data['where'])){
								$where = $post_data['where'][$key];
								if(is_array($where)){
									$where_key = array();
									$where_value = array();
									foreach($where as $wk=>$wv){
										$where_key[] = '$'.$wk;
										$where_value[] = $wv;
									}
									$q = str_replace($where_key, $where_value, $q);
									$where = "";
								}
							}
							$data['data'][] = $this->getDataByJson($q, $where);
						}else{
							$where = array();
							if(isset($post_data['where'])){
								$where = $post_data['where'][$key];
							}
							$data['data'][] = $this->getDataByJson($q, $where);
						}
					}
				} else {
					$data['data'] = $this->getDataByJson($json_data['query'], array());
				}
			} else {
				$data['data'] = $this->getDataByJson($json_data['query'], "");
			}
			$data['log'] = $this->db->log();
		}
		$this->render->json($data);
	}
	
	function executeMutation(){
		$post_data = $this->render->json_post();
		$name = $post_data['name'];
		$type = $post_data['type'];

		$json_data = json_decode(file_get_contents('project/base/config/mutation-service/'.$name.'.json'), true);

		$table = $json_data['table'];
		$primary_key = $json_data['primary_key'];
		$data = array();

		function getInputData($my_data, $my_fields) {
			$input_data = array();
			foreach($my_fields as $field){
				if(isset($my_data[$field['id']])){
					$input_data[$field['id']] = $my_data[$field['id']];
					if(isset($field['type'])){
						if($field['type']=='password'){
							$input_data[$field['id']] = password_hash($input_data[$field['id']],1);
						}
					}
				}
			}
			return $input_data;
		}
		$data['id'] = false;
		if($type == 'insert') {
			if(in_array(id_role, $json_data['roles']['insert'])){
				if(is_array($post_data['data'])){
					$array_keys = array_keys($post_data['data']);
					if($array_keys[0] === 0){
						foreach($array_keys as $key) {
							$input_data = getInputData($post_data['data'][$key], $json_data['fields']);
							$this->db->insert($table, $input_data);
						}
						$this->set->success_message(true);
					} else {
						$input_data = getInputData($post_data['data'], $json_data['fields']);
						if($this->db->insert($table, $input_data)){
							$id = $this->db->id();
							$this->set->success_message(true, array('id'=>$id));
						}
					}
				}
			}
		}elseif($type == 'update') {
			$input_data = getInputData($post_data['data'], $json_data['fields']);
			if(in_array(id_role, $json_data['roles']['update'])){
				if($this->db->update($table, $input_data, [$primary_key => $post_data['id']])){
					$this->set->success_message(true, $this->db->log());
				}
			}
		}elseif($type == 'delete') {
			if(in_array(id_role, $json_data['roles']['delete'])){
				if($this->db->delete($table, [$primary_key => $post_data['id']])){
					$this->set->success_message(true, $this->db->log());
				}
			}
		}
		$this->set->error_message(true, $this->db->log());
	}
}
?>