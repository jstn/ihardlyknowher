<?
	final class UserController extends Controller {
		public function photostream() {
			$nsid = Application::getNSID();
			if ($nsid) {	
				$perpage = 10;
				$page = $_GET['page'] ? (int)$_GET['page'] : 1;
				$public = new FlickrPublicPhotos($perpage,$page,$nsid);
			
				$this->page = $public->page();
				$this->pages = $public->pages();
				$this->photos = $public->photos();
				
				$this->userinfo = Flickr::userInfoForID($nsid);
				$this->settings['title'] = $this->userinfo['username'];
				
				$rows = DB::select(array(
					'what' => '*',
					'from' => 'settings',
					'matches' => array(array('nsid','=',$nsid)),
					'limit' => 1
				));

				$this->size = $rows ? $rows[0]['size'] : 'large';
				$this->background = $rows ? $rows[0]['background'] : 'white';
			}
		}
		
		public function callback() {
			$frob = $_GET['frob'];
			if ($frob) {				
				$response = Flickr::call(array(
					'method' => 'flickr.auth.getToken',
					'frob' => $frob
				), 0);
				if ($response['stat'] === 'ok') {
					$this->nsid = $response['auth']['user']['nsid'];
					$this->token = $response['auth']['token']['_content'];
					
					$authcheck = Flickr::call(array(
						'method' => 'flickr.auth.checkToken',
						'auth_token' => $this->token
					), 0);
					
					if ($authcheck['stat'] === 'ok') {
						$this->userinfo = Flickr::userInfoForID($this->nsid);
						
						$rows = DB::select(array(
							'what' => '*',
							'from' => 'settings',
							'matches' => array(array('nsid','=',$this->nsid)),
							'limit' => 1
						));
						
						if ($rows) {
							$this->background = $rows[0]['background'];
							$this->size = $rows[0]['size'];
						} else {
							$this->background = 'white';
							$this->size = 'large';							
						}
					} else {
						$this->error = 'ERROR: authentication failed, ';
						$this->error .= '<a href="'.Flickr::authLink('read').'">try again?</a>';
					}
				} else {
					header('Location: '.Flickr::authLink('read'));
					$this->error = 'ERROR: bad frob, redirecting';
				}
			} else {
				header('Location: '.Flickr::authLink('read'));
				$this->error = 'ERROR: no frob, redirecting';
			}
		} 
		
		public function save() {
			$background = $_POST['background'] === 'black' || $_POST['background'] === 'white' ? $_POST['background'] : 'false';
			$size = $_POST['size'] === 'medium' || $_POST['size'] === 'large' ? $_POST['size'] : false;
			$token = $_POST['token'];
			
			if ($token && $background && $size) {
				$response = Flickr::call(array(
					'method' => 'flickr.auth.checkToken',
					'auth_token' => $token
				), 0);
				
				if ($response['stat'] === 'ok') {
					$nsid = $response['auth']['user']['nsid'];
					$userinfo = Flickr::userInfoForID($nsid);
					
					$rows = DB::select(array(
						'what' => '*',
						'from' => 'settings',
						'matches' => array(array('nsid','=',$nsid)),
						'limit' => 1
					));
					
					if ($rows) {
						$test = DB::update(array(
							'table' => 'settings',
							'what' => array(
								'id' => $rows[0]['id'],
								'nsid' => $nsid,
								'modified' => 'NOW()',
								'background' => $background,
								'size' => $size
							)
						));
					} else {
						DB::insert(array(
							'table' => 'settings',
							'what' => array(
								'nsid' => $nsid,
								'modified' => 'NOW()',
								'background' => $background,
								'size' => $size
							)
						));
					}
					
					header('Location: /'.$userinfo['urlname']);
				} else {
					header('Location: /');
				}
			} else {
				header('Location: /user.settings');
			}
		}	
	}
?>