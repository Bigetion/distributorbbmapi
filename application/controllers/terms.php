<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Terms extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getData(){
        $post_data = $this->render->json_post();
        $type = 'category';
        if(isset($post_data['type'])){
            $type = $post_data['type'];
        }
        $where = array();
        $where["blog_taxonomy.taxonomy"] = $type;

        if(isset($post_data['id'])){
            $where["blog_terms.term_id"] = $post_data["id"];
        }
        $data['data'] = $this->db->select("blog_taxonomy",[
            '[>]blog_terms' => 'term_id'
        ],[
            'blog_terms.term_id(id)',
            'blog_taxonomy.term_taxonomy_id(taxonomy_id)',
            'blog_terms.name(text)',
            'blog_taxonomy.description'
        ], $where);
        $this->render->json($data);
    }

    function submitAdd(){
        $post_data = $this->render->json_post();
        $type = 'category';
        $description = '-';
        if(isset($post_data['type'])){
            $type = $post_data['type'];
        }
        if(isset($post_data['description'])){
            $description = $post_data['description'];
        }
        $data = array(
            'name'  => $post_data['name'],
            'slug'  => $post_data['slug'],
        );
        if($this->db->insert("blog_terms", $data)){
            $id = $this->db->id();
            $data_taxonomy = array(
                'term_id'       => $id,
                'taxonomy'      => $type,
                'description'   => $description
            );
            $this->db->insert("blog_taxonomy", $data_taxonomy);
            $this->set->success_message(true, ["id"=>$id]);
        }
        $this->set->error_message(true);
    }

    function submitEdit(){
        $post_data = $this->render->json_post();
        $type = 'category';
        $id = $post_data['id'];
        $taxonomy_id = $post_data['taxonomyId'];
        if(isset($post_data['type'])){
            $type = $post_data['type'];
        }
        $data = array(
            'name'  => $post_data['name'],
            'slug'  => $post_data['slug'],
        );
        if($this->db->update("blog_terms", $data, ["term_id"=>$id])){
            $data_taxonomy = array(
                'description'  => $post_data['description']
            );
            $this->db->update("blog_taxonomy", $data_taxonomy, ["term_taxonomy_id"=>$taxonomy_id]);
            $this->set->success_message(true);
        }
        $this->set->error_message(true);
    }

    function submitDelete(){
        $post_data = $this->render->json_post();
        $type = 'category';
        if(isset($post_data['type'])){
            $type = $post_data['type'];
        }
        $id = $post_data['id'];
        $taxonomy_id = $post_data['taxonomyId'];
        if($this->db->delete("blog_terms", ["term_id" => $id])){
            $this->db->delete("blog_taxonomy", ["term_taxonomy_id"=>$taxonomy_id]);
            $this->set->success_message(true);
        }
        $this->set->error_message(true);
    }
}    
?>