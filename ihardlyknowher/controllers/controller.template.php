<?
	final class TemplateController extends Controller {
		public function standard() {
			$this->title = $this->args['settings']['title'] ? $this->args['settings']['title'] : 'I Hardly Know Her';
			$this->js = $this->args['settings']['js'] ? $this->args['settings']['js'] : array();
			$this->css = $this->args['settings']['css'] ? $this->args['settings']['css'] : array();			
			$this->content = $this->args['content'];
		}
	}
?>