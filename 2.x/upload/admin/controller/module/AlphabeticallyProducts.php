<?php
class ControllerModuleAlphabeticallyProducts extends Controller{
    private $error = array();
    
    public function index(){
        
        $this->load->language('module/AlphabeticallyProducts');
        $this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_extension_module->addModule('AlphabeticallyProducts', $this->request->post);
			} else {
				$this->model_extension_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], true));
		}
				
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_content_top'] = $this->language->get('text_content_top');
		$data['text_content_bottom'] = $this->language->get('text_content_bottom');
		$data['text_column_left'] = $this->language->get('text_column_left');
		$data['text_column_right'] = $this->language->get('text_column_right');
		
		$data['entry_layout'] = $this->language->get('entry_layout');
		$data['entry_limit'] = $this->language->get('entry_limit');
		$data['entry_position'] = $this->language->get('entry_position');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_add_module'] = $this->language->get('button_add_module');
		$data['button_remove'] = $this->language->get('button_remove');

		$data['lattxt'] = $this->language->get('lattxt');  
		$data['cyrtxt'] = $this->language->get('cyrtxt');  
		$data['onlytxt'] = $this->language->get('onlytxt');  
		$data['settxt'] = $this->language->get('settxt');
		$data['numtxt'] = $this->language->get('numtxt');  
		$data['linetxt'] = $this->language->get('linetxt'); 

		$data['from_currenttxt'] = $this->language->get('from_currenttxt'); 
		$data['from_currenttxt2'] = $this->language->get('from_currenttxt2'); 
		$data['title'] = $this->language->get('title'); 

		$data['copy'] = $this->language->get('copy');		
		
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
       		'text'      => $this->language->get('text_home'),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/AlphabeticallyProducts', 'token=' . $this->session->data['token'], 'SSL'),
   		);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('module/AlphabeticallyProducts', 'token=' . $this->session->data['token'], true);
		} else {
			$data['action'] = $this->url->link('module/AlphabeticallyProducts', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		
		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		$data['modules'] = array();
		
		if (isset($this->request->post['AlphabeticallyProducts_module'])) {
			$data['modules'] = $this->request->post['AlphabeticallyProducts_module'];
		} elseif ($this->config->get('AlphabeticallyProducts_module')) { 
			$data['modules'] = $this->config->get('AlphabeticallyProducts_module');
		}		
				
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_extension_module->getModule($this->request->get['module_id']);
		}

		$data['token'] = $this->session->data['token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['lat'])) {
			$data['lat'] = $this->request->post['lat'];
		} elseif (!empty($module_info)) {
			$data['lat'] = isset($module_info['lat']) ? 1 : 0;
		} else {
			$data['lat'] = 1;
		}

		if (isset($this->request->post['cyr'])) {
			$data['cyr'] = $this->request->post['cyr'];
		} elseif (!empty($module_info)) {
			$data['cyr'] = isset($module_info['cyr']) ? 1 : 0;
		} else {
			$data['cyr'] = 1;
		}

		if (isset($this->request->post['num'])) {
			$data['num'] = $this->request->post['num'];
		} elseif (!empty($module_info)) {
			$data['num'] = isset($module_info['num']) ? 1 : 0;
		} else {
			$data['num'] = 1;
		}

		if (isset($this->request->post['only'])) {
			$data['only'] = $this->request->post['only'];
		} elseif (!empty($module_info)) {
			$data['only'] = isset($module_info['only']) ? 1 : 0;
		} else {
			$data['only'] = 1;
		}

		if (isset($this->request->post['line'])) {
			$data['line'] = $this->request->post['line'];
		} elseif (!empty($module_info)) {
			$data['line'] = isset($module_info['line']) ? 1 : 0;
		} else {
			$data['line'] = 1;
		}

		if (isset($this->request->post['from_current'])) {
			$data['from_current'] = $this->request->post['from_current'];
		} elseif (!empty($module_info)) {
			$data['from_current'] = isset($module_info['from_current']) ? 1 : 0;
		} else {
			$data['from_current'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/AlphabeticallyProducts.tpl', $data));
        
    }
    
    private function validate() {
		if (!$this->user->hasPermission('modify', 'module/AlphabeticallyProducts')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>