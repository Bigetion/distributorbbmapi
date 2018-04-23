<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class Posts extends Main {
    function __construct() {
        $this->auth->permission();
    }

    function getData(){
        $data['data'] = $this->db->select("blog_posts","*",["ORDER"=>["created"=>"DESC"]]);
        $this->render->json($data);
    }

    function setFeaturedImage(){
        $post_data = $this->render->json_post();
        $data = array(
            'thumbnail' => $post_data['thumbnail']
        );
        if($this->db->update("blog_posts", $data, ["id_post" => $post_data['idPost']])){
            $this->set->success_message(true);
        }
    }

    function submitAdd(){
        $post_data = $this->render->json_post();
        $data = array(
            'author_id'         => id_user,   
            'post_title'        => $post_data['postTitle'],
            'post_title_slug'   => $post_data['postTitleSlug'],
            'post_content'      => $post_data['postContent'],
            'description'       => $post_data['description'],
            'post_categories'   => $post_data['postCategories'],
            'post_tags'         => $post_data['postTags']  
        );
        if($this->db->insert("blog_posts", $data)){
            $id = $this->db->id();
            $this->set->success_message(true, array('id'=>$id));
        }
        $this->set->error_message(true, $this->db->log());
    }

    function submitEdit(){
        $post_data = $this->render->json_post();
        $data = array(
            'author_id'         => id_user,   
            'post_title'        => $post_data['postTitle'],
            'post_title_slug'   => $post_data['postTitleSlug'],
            'post_content'      => $post_data['postContent'],
            'description'       => $post_data['description'],
            'post_categories'   => $post_data['postCategories'],
            'post_tags'         => $post_data['postTags'] 
        );
        if($this->db->update("blog_posts", $data, ["id_post" => $post_data['idPost']])){
            $this->set->success_message(true);
        }
    }

    function submitDelete(){
        $post_data = $this->render->json_post();
        if($this->db->delete("blog_posts", ["id_post" => explode(',',$post_data['idPost'])])){
            $this->set->success_message(true);
        }
    }
}    
?>