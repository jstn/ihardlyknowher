<?
	abstract class Controller {
		public static function withName($controller_name) {
			require_once(BASE_DIR.'controllers/controller.'.$controller_name.'.php');
			$controller_name .= 'Controller';
			$controller = new $controller_name();
			return $controller;
		}
		
		public function renderView($view_name,$args = NULL) {					
			ob_start();
			
			$this->args = $args;
			$this->$view_name();	
			extract(get_object_vars($this));			
			$controller_name = strtolower(str_replace('Controller','',get_class($this)));
			
			$view_file = BASE_DIR.'views/view.'.$controller_name.'.'.$view_name.'.php';
			if(file_exists($view_file)) require($view_file);
			
			return ob_get_clean();
		}
	}
?>