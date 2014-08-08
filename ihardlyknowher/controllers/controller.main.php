<?
	final class MainController extends Controller {
		public function home() {
			$this->settings['title'] = 'I Hardly Know Her';
		}
		
		public function faq() {
			$this->settings['title'] = 'I Hardly Know Her FAQ';
		}		
	}
?>