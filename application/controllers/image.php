<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');

class image extends Main {
    function __construct() {
        
    }

    function getAll(){
        $this->auth->permission();
        $post_data = $this->render->json_post();
        $path = 'application/images/featured/thumbs';
        if(isset($post_data['path'])){
            $path = 'application/images/'.$post_data['path'];
            $this->dir->create_dir($path);
            $path = $path.'/thumbs';
            $this->dir->create_dir($path);
        }
        $images = load_recursive($path, 0, array('jpg','jpeg','png'));

        $data['images'] = array();
        foreach($images as $image){
			$image = pathinfo($image);
            $data['images'][] = $image['basename'];
        }
        $this->render->json($data);
    }

    function uploadImage(){
        $this->auth->permission();
		$allowedExts = array("jpeg", "jpg", "png");
		$temp = explode(".", $_FILES["image"]["name"]);
		
		$extension = end($temp);
		
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
        $this->dir->create_dir('application/images/featured');
        $this->dir->create_dir('application/images/featured/thumbs');
		if ((($mime == "image/gif")
			|| ($mime == "image/jpeg")
			|| ($mime == "image/pjpeg")
			|| ($mime == "image/x-png")
			|| ($mime == "image/png"))
			&& in_array($extension, $allowedExts)) {
			$name = sha1(microtime()) . "." . $extension;
			if(move_uploaded_file($_FILES["image"]["tmp_name"], "application/images/featured/" . $name)){
                try {
                    $this->imageresize->fromFile("application/images/featured/" . $name)->resize(250)->toFile("application/images/featured/thumbs/" . $name, 'image/jpeg');
                }
                catch(Exception $err){
                    $this->set->error_message($err);
                }
            }
		}
	}

    function deleteImage(){
        $this->auth->permission();
        $post_data = $this->render->json_post();
        $path = 'application/images/featured';
        $path_thumb = $path.'/thumbs';
        if(isset($post_data['path']) && isset($post_data['img'])){
            $path = 'application/images/'.$post_data['path'];
            $path_thumb = $path.'/thumbs';
            $img = $post_data['img'];
            unlink($path.'/'.$img);
            unlink($path_thumb.'/'.$img);
        }
    }
    
    function get(){
        $id_image = subsegment(-1);
        $base_path = explode('/', str_replace('://','',base_url));
        $path = subsegment(count($base_path)+1,-1);
        $path = 'application/images/'.$path;
        if (file_exists($path."/default.png")) {
            $fileOut = $path."/default.png";
        }else{
            $fileOut = "application/images/default.png";
        }
        if (is_dir($path)){
            $images = load_recursive($path, 0, array('jpg','jpeg','gif','png'));
            foreach($images as $image){
                $path_info = pathinfo($image);
                $basename = $path_info['basename'];
                $filename = $path_info['filename'];
                if($filename == $id_image || $basename == $id_image){
                    $fileOut = $image;
                }
            }
        } 
        $this->render->image($fileOut);
    }

    function getBase64(){
        $id_image = subsegment(-1);
        $base_path = explode('/', str_replace('://','',base_url));
        $path = subsegment(count($base_path)+1,-1);
        $path = 'application/images/'.$path;
        if (file_exists($path."/default.png")) {
            $fileOut = $path."/default.png";
        }else{
            $fileOut = "application/images/default.png";
        }
        if (is_dir($path)){
            $images = load_recursive($path, 0, array('jpg','jpeg','gif','png'));
            foreach($images as $image){
                $path_info = pathinfo($image);
                $basename = $path_info['basename'];
                $filename = $path_info['filename'];
                if($filename == $id_image || $basename == $id_image){
                    $fileOut = $image;
                }
            }
        }
        $type = pathinfo($fileOut, PATHINFO_EXTENSION);
        $img = file_get_contents($fileOut);
        $data['base64'] = 'data:image/' . $type . ';base64,' . base64_encode($img);
        $this->render->json($data);
    }
}    
?>