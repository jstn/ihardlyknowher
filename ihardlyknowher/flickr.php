<?
	include_once('cache.php');
	
	abstract class Flickr {
		public static function call($args,$cache_duration = 300) {
			$args['api_key'] = FLICKR_API_KEY;
			$args['format'] = 'php_serial';
			ksort($args);
			
			$sig = FLICKR_API_SECRET;
			$params = array();
			foreach($args as $k => $v) {
				$sig .= ($k.$v);
				$params[] = urlencode($k).'='.urlencode($v);
			}
			$params[] = 'api_sig='.md5($sig);

			$url = 'https://api.flickr.com/services/rest/?'.implode('&',$params);
			
			if(class_exists('Cache')) {
				$key = 'ihkh.flickr.'.md5($url);
				
				if($cache_duration) {
					$serial = Cache::get($key);
					$object = unserialize($serial);
				} else {
					Cache::delete($key);
				}
				
				if(!$serial) {
					$serial = file_get_contents($url);
					$object = unserialize($serial);
					if($object && $object['stat'] === 'ok' && $cache_duration >= 0) {					
						Cache::set($key,$serial,$cache_duration);
					}
				}
			} else {
				$serial = file_get_contents($url);
				$object = unserialize($serial);
			}
			
			return $object ? $object : false;
		}
		
		public static function authLink($perms = 'read') {
			$api_sig = md5(FLICKR_API_SECRET.'api_key'.FLICKR_API_KEY.'perms'.$perms);
			return 'https://flickr.com/services/auth/?api_key='.FLICKR_API_KEY.'&perms='.$perms.'&api_sig='.$api_sig;
		}
		
		public static function userInfoForID($nsid) {
			$response = self::call(array(
				'method' => 'flickr.people.getInfo',
				'user_id' => $nsid
			),86400);
			
			if($response['stat'] === 'ok') {
				preg_match('/^https:\/\/www.flickr.com\/photos\/(.+)\/$/',$response['person']['photosurl']['_content'],$urlmatches);
				return array(
					'username' => $response['person']['username']['_content'],
					'realname' => $response['person']['realname']['_content'],
					'urlname' => $urlmatches[1],
					'location' => $response['person']['location']['_content'],
					'photosurl' => $response['person']['photosurl']['_content'],
					'profileurl' => $response['person']['profileurl']['_content'],
					'mobileurl' => $response['person']['mobileurl']['_content'],
					'firstdatetaken'  => $response['person']['photos']['firstdatetaken']['_content'],
					'firstdate' => $response['person']['photos']['firstdate']['_content'],
					'count' => $response['person']['photos']['count']['_content']
				);
			} else {
				return false;
			}
		}
		
		public static function licenseForCode($code) {
			$response = self::call(array(
				'method' => 'flickr.photos.licenses.getInfo'
			),86400);
			
			$licenses = array();
			foreach($response['licenses']['license'] as $license) {
				$id = (int)$license['id'];
				$licenses[$id] = $license['name'];
			}
			
			return $licenses[$code] ? $licenses[$code] : 'All Rights Reserved';
		}
		
		public static function NSIDforUsername($username) {
			$response = self::call(array(
				'method' => 'flickr.people.findByUsername',
				'username' => $username
			),86400);

			return $response['stat'] === 'ok' ? $response['user']['nsid'] : false;
		}
		
		public static function NSIDforURLname($urlname) {
			$response = self::call(array(
				'method' => 'flickr.urls.lookupUser',
				'url' => "https://www.flickr.com/photos/$urlname/"
			),86400);
			
			return $response['stat'] === 'ok' ? $response['user']['id'] : false;
		}
			
		public static function URLnameForNSID($nsid) {
			$userinfo = self::userInfoForID($nsid);
			return $userinfo['urlname'];
		}	
	}
	
	final class FlickrPhotoSet {
		private $_id, $_primary, $_secret, $_server, $_farm, $_photo_count, $_video_count, $_title, $_description, $_photos;
		
		public function __construct($args) {
			$this->_id = $args['id'];
			$this->_primary = $args['primary'];
			$this->_secret = $args['secret'];
			$this->_server = $args['server'];
			$this->_farm = (float)$args['farm'];
			$this->_photo_count = (int)$args['photos'];
			$this->_video_count = (int)$args['videos'];
			$this->_title = $args['title']['_content'];
			$this->_description = $args['title']['_content'];
		}
		
		public static function withID($id) {
			$response = Flickr::call(array(
				'method' => 'flickr.photosets.getInfo',
				'photoset_id' => $id
			));
			
			return new FlickrPhotoSet($response['photoset']);
		}

		public function id() {
			return $this->_id;
		}
		
		public function title() {
			return $this->_title;
		}
		
		public function total() {
			return $this->_photo_count + $this->_video_count;
		}
		
		public function photos($per_page = 10, $page = 1) {
			if($this->_photo_count && !$this->_photos) {
				$response = Flickr::call(array(
					'method' => 'flickr.photosets.getPhotos',
					'photoset_id' => $this->_id,
					'privacy_filter' => 1,
					'page' => $page,
					'per_page' => $per_page					
				));

				$this->_photos = array();			
				foreach($response['photoset']['photo'] as $p) {
					$this->_photos[] = FlickrPhoto::withID($p['id']);
				}
			}
			
			return $this->_photos;
		}
	}
	
	final class FlickrPublicPhotos {
		private $_photos, $_page, $_pages, $_perpage, $_total;
		
		public function __construct($per_page = 10, $page = 1, $nsid) {
			$response = Flickr::call(array(
				'method' => 'flickr.people.getPublicPhotos',
				'page' => $page,
				'per_page' => $per_page,
				'user_id' => $nsid,
				'extras' => 'date_taken,date_upload,original_format,tags,license'
			));

			$this->_photos = array();			
			foreach($response['photos']['photo'] as $args) {
				$this->_photos[] = FlickrPhoto::withPublic($args);
			}
			
			$this->_page = $response['photos']['page'];
			$this->_page = $response['photos']['page'];
			$this->_pages = $response['photos']['pages'];
			$this->_perpage = $response['photos']['perpage'];							
			$this->_total = $response['photos']['total'];
		}
		
		public function photos() {
			return $this->_photos;
		}
		
		public function page() {
			return $this->_page;
		}
		
		public function pages() {
			return $this->_pages;
		}		
		
		public function total() {
			return $this->_total;
		}
	}

	final class FlickrPhoto {
		private $_exif, $_id, $_tags, $_secret, $_server, $_farm, $_stamp, $_sizes, $_license_code,
				$_owner, $_originalsecret, $_originalformat, $_title, $_date_taken, $_date_uploaded;

		public static function withID($id) {
			$response = Flickr::call(array(
				'method' => 'flickr.photos.getInfo',
				'photo_id' => $id
			));
			$r = $response['photo'];
			
			$photo = new FlickrPhoto();
			
			$photo->_id = $r['id'];
			$photo->_secret = $r['secret'];
			$photo->_server = $r['server'];
			$photo->_farm = (float)$r['farm'];
			$photo->_title = $r['title']['_content'];
			$photo->_license_code = (int)$r['license'];
			$photo->_date_taken = $r['dates']['taken'];
			$photo->_date_uploaded = (int)$r['dateuploaded'];
			$photo->_owner = $r['owner']['nsid'];

			if($r['originalsecret'] && $r['originalformat']) {
				$photo->_originalsecret = $r['originalsecret'];
				$photo->_originalformat = $r['originalformat'];
			}
			
			if(empty($r['tags'])) {
				$photo->_tags = false;
			} else {
				$photo->_tags = array();
				foreach($r['tags']['tag'] as $tag) {					
					$photo->_tags[] = $tag['_content'];
				}
			}
			
			return $photo;
		}
		
		public static function withPublic($args) {
			$photo = new FlickrPhoto();

			$photo->_id = $args['id'];
			$photo->_secret = $args['secret'];
			$photo->_server = $args['server'];
			$photo->_farm = (float)$args['farm'];
			$photo->_title = $args['title'];
			$photo->_license_code = (int)$args['license'];
			$photo->_date_taken = $args['datetaken'];
			$photo->_date_uploaded = (int)$args['dateupload'];
			$photo->_owner = $args['owner'];
			$photo->_tags = empty($args['tags']) ? false : explode(' ',$args['tags']);
			if($args['originalsecret'] && $args['originalformat']) {
				$photo->_originalsecret = $args['originalsecret'];
				$photo->_originalformat = $args['originalformat'];
			}
			
			return $photo;
		}
	
		public function id() {
			return $this->_id;
		}
		
		public function tags() {
			return $this->_tags;
		}
		
		public function owner() {
			return $this->_owner;
		}
		
		public function license() {
			return Flickr::licenseForCode($this->_license_code);
		}		
		
		public function stamp() {
			if(!$this->_stamp) {
				$dt = explode(' ',$this->_date_taken);							
				$date = explode('-',$dt[0]);
				$time = explode(':',$dt[1]);
				$this->_stamp = mktime($time[0],$time[1],$time[2],$date[1],$date[2],$date[0]);
			}
			return $this->_stamp;
		}
	
		public function imageURLforSuffix($suffix = '') {
			$secret = $suffix == 'o' ? $this->_originalsecret : $this->_secret;
			$format = $suffix == 'o' ? $this->_originalformat : 'jpg';
			if($suffix !== '') $suffix = '_' . $suffix;
			$url = "http://farm{$this->_farm}.static.flickr.com/{$this->_server}/{$this->_id}_{$secret}{$suffix}.{$format}";
			return $url;
		}
		
		public function imageInfoForSize($label = 'Medium') {
			if(!$this->_sizes[$label]) {
				$response = Flickr::call(array(
					'method' => 'flickr.photos.getSizes',
					'photo_id' => $this->_id
				));
				
				foreach($response['sizes']['size'] as $size) {
					$l = $size['label'];
					$this->_sizes[$l] = array(
						'width' => (int)$size['width'],
						'height' => (int)$size['height'],
						'source' => $size['source'],
						'url' => $size['url'],
						'media' => $size['media']
					);
				}
			}
			
			return $this->_sizes[$label] ? $this->_sizes[$label] : false;
		}
		
		public function exif($type = false) {
			if(!$this->_exif) {
				$response = Flickr::call(array(
					'method' => 'flickr.photos.getExif',
					'photo_id' => $this->_id,
					'secret' => $this->_secret
				),86400);
				
				$exifs = $response['photo']['exif'];

				if($exifs) {
					foreach($exifs as $ex) {
						if($ex['tag'] === 'FNumber' && $ex['tagspace'] === 'ExifIFD') {
							$this->_exif['aperture'] = (float)$ex['raw']['_content'];
						} elseif(!$this->_exif['aperture'] && $ex['tag'] === 33437) {
							$a = explode('/',$ex['clean']['_content']);
							$this->_exif['aperture'] = (float)$a[1];							
						}
						
						if($ex['tag'] === 'ExposureTime' && $ex['tagspace'] === 'ExifIFD') {
							$this->_exif['exposure'] = $ex['raw']['_content'];
						} elseif(!$this->_exif['exposure'] && $ex['tag'] === 33434) {
							$this->_exif['exposure'] = $ex['raw']['_content'];
						}
						
						if($ex['tag'] === 'FocalLength' && $ex['tagspace'] === 'ExifIFD') {
							$a = explode(' ',$ex['raw']['_content']);
							$this->_exif['focal_length'] = (float)$a[0];
						} elseif(!$this->_exif['focal_length'] && $ex['tag'] === 37386) {
							$a = explode(' ',$ex['clean']['_content']);
							$this->_exif['focal_length'] = (float)$a[0];
						}						
						
						if($ex['tag'] === 'ISO' && $ex['tagspace'] === 'ExifIFD') {
							$this->_exif['iso'] = (int)$ex['raw']['_content'];
						} elseif(!$this->_exif['iso'] && $ex['tag'] === 34855) {
							$this->_exif['iso'] = (int)$ex['raw']['_content'];
						}
					}
				}
			}
			
			if(!$type) {
				return ($this->_exif['aperture'] || $this->_exif['exposure'] || $this->_exif['focal_length'] || $this->_exif['iso']);
			}
		
			return $this->_exif[$type] ? $this->_exif[$type] : false;
		}
	}
?>