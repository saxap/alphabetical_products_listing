<?php
class ControllerModuleAlphabeticallyProducts extends Controller{
    
    protected function index(){
        
		$this->load->model('setting/setting');
        $this->language->load('module/AlphabeticallyProducts');
        $this->load->model('catalog/AlphabeticallyProducts');
        $this->data['heading_title'] = $this->language->get('heading_title');

	
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_setting_setting->editSetting('lat', $this->request->post);		
			$this->model_setting_setting->editSetting('cyr', $this->request->post);	
			$this->model_setting_setting->editSetting('only', $this->request->post);

		if (isset($this->request->post['lat'])) {
			$this->data['lat'] = $this->request->post['lat'];
		} else {
			$this->data['lat'] = $this->config->get('lat');
		}	

		if (isset($this->request->post['cyr'])) {
			$this->data['cyr'] = $this->request->post['cyr'];
		} else {
			$this->data['cyr'] = $this->config->get('cyr');
		}	
		
		if (isset($this->request->post['only'])) {
			$this->data['only'] = $this->request->post['only'];
		} else {
			$this->data['only'] = $this->config->get('only');
		}	
		
		if (isset($this->request->post['num'])) {
			$this->data['num'] = $this->request->post['num'];
		} else {
			$this->data['num'] = $this->config->get('num');
		}	
		
		if (isset($this->request->post['line'])) {
			$this->data['line'] = $this->request->post['line'];
		} else {
			$this->data['line'] = $this->config->get('line');
		}	
		
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		
        $this->data['href'] = HTTP_SERVER . 'index.php?route=product/AlphabeticallyProducts&char=';
		
		if ( $this->config->get('only') == 0 ) {
			
			$this->data['lat_letters'] = array( 0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G',
												7 => 'H', 8 => 'I', 9 => 'J', 10 => 'K', 11 => 'L', 12 => 'M', 13 => 'N',
												14 => 'O', 15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U',
												21 => 'W', 22 => 'X', 23 => 'Y', 24 => 'Z'
											);
			$this->data['cyr_letters'] = array( 0 => 'А', 1 => 'Б', 2 => 'В', 3 => 'Г', 4 => 'Д', 5 => 'Е', 6 => 'Ж',
												7 => 'З', 8 => 'И', 9 => 'К', 10 => 'Л', 11 => 'М', 12 => 'Н', 13 => 'О',
												14 => 'П', 15 => 'Р', 16 => 'С', 17 => 'Т', 18 => 'У', 19 => 'Ф', 20 => 'Х',
												21 => 'Ц', 22 => 'Ч', 23 => 'Ш', 24 => 'Щ', 25 => 'Э', 26 => 'Ю', 27 => 'Я'
											);
			$this->data['numbers'] = '0-9';
		
		} else {
			$allletters = $this->model_catalog_AlphabeticallyProducts->getletters();
			if (isset($allletters['lat'])) {
			$this->data['lat_letters'] = $allletters['lat'];
			} else {
			$this->data['lat_letters'] = array();
			}
			if (isset($allletters['cyr'])) {
			$this->data['cyr_letters'] = $allletters['cyr'];
			} else {
			$this->data['cyr_letters'] = array();
			}
			//print_r($allletters);
			if (isset($allletters['number']) && (count($allletters['number']) != 0)) {		
			$this->data['numbers'] = '0-9';		
			} else {
			$this->data['numbers'] = '';	
			}
			
		}
            
            if(file_exists(DIR_TEMPLATE.$this->config->get('config_template').'template/module/AlphabeticallyProducts.tpl')){
                $this->template = $this->config->get('config_template').'template/module/AlphabeticallyProducts.tpl';
            } else {
                $this->template = 'default/template/module/AlphabeticallyProducts.tpl';
            }
            $this->render();
        }
		
}
?>