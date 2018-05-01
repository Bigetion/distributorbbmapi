<?php  if ( ! defined('INDEX')) exit('No direct script access allowed');
class upload extends Controller {

	function image(){
		$post_data = $this->render->json_post();

		$images = $post_data['images'];
		try {
			foreach ($images as $image) {
				$path = 'application/images';
				if(isset($image['path'])) $path = 'application/images/'.$image['path'];
				$this->dir->create_dir($path);
				$name = $image['name'];
				$data = $image['base64'];

				if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
					$data = substr($data, strpos($data, ',') + 1);
					$type = strtolower($type[1]);
			
					if (!in_array($type, [ 'jpg', 'jpeg', 'png' ])) {
						$this->set->error_message(true, ['message'=> 'invalid image type']);
					}
			
					$data = base64_decode($data);
			
					if ($data === false) {
						$this->set->error_message(true, ['message'=> 'base64_decode failed']);
					}
				} else {
					$this->set->error_message(true, ['message'=> 'did not match data URI with image data']);
				}
				if($type == 'jpeg') $type = 'jpg';
				$this->dir->delete_file($path.'/'.$name.'.jpg');
				$this->dir->delete_file($path.'/'.$name.'.png');
				file_put_contents( $path.'/'.$name.'.'.$type, $data);
			}
			$this->set->success_message(true);
		} catch (Exception $e) {
			$this->set->error_message(true, ['message'=> $e]);
		}
	}
}
?>