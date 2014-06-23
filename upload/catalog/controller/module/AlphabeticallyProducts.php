<?php
class ControllerModuleAlphabeticallyProducts extends Controller{
    
    protected function index($setting){
        
		$this->load->model('setting/setting');
        $this->language->load('module/AlphabeticallyProducts');
        $this->load->model('catalog/AlphabeticallyProducts');
        $this->data['heading_title'] = $this->language->get('heading_title');		
		
        $this->data['href'] = HTTP_SERVER . 'index.php?route=product/AlphabeticallyProducts';
		$this->data['title'] = $setting['title'];

		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];
		} else {
			$route = '';
		}

		if (isset($setting['from_current'])) {
			if ((isset($this->request->get['path']) && $id = $this->request->get['path']) || (isset($this->request->get['category']) && $id = $this->request->get['category'])) {
				$this->data['href'] .= '&category='.$id;
				$parts = explode('_', $id);
				$category_id = array_pop($parts);
			} else {
				$category_id = false;
			}
			if ((isset($this->request->get['manufacturer_id']) && $id = $this->request->get['manufacturer_id']) || (isset($this->request->get['manufacturer']) && $id = $this->request->get['manufacturer'])) {
				$this->data['href'] .= '&manufacturer='.$id;
				$manufacturer_id = $id;
			} else {
				$manufacturer_id = false;
			}
			if (isset($this->request->get['filter_name'])) {
				$this->data['href'] .= '&filter_name='.$this->request->get['filter_name'];
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = false;
			}
			if (isset($this->request->get['search'])) {
				$this->data['href'] .= '&search='.$this->request->get['search'];
				$filter_name = $this->request->get['search'];
			} else {
				$filter_name = false;
			}
			if (isset($this->request->get['filter_tag'])) {
				$this->data['href'] .= '&filter_tag='.$this->request->get['filter_tag'];
				$filter_tag = $this->request->get['filter_tag'];
			} else {
				$filter_tag = false;
			}

			//dlya poiska
			if (isset($this->request->get['filter_description'])) {
				$this->data['href'] .= '&filter_description=' . $this->request->get['filter_description'];
				$filter_description = $this->request->get['filter_description'];
			} else {
				$filter_description = false;
			}
			if (isset($this->request->get['filter_category_id'])) {
				$this->data['href'] .= '&filter_category_id=' . $this->request->get['filter_category_id'];
				$category_id = $this->request->get['filter_category_id'];
			}
			if (isset($this->request->get['filter_sub_category'])) {
				$this->data['href'] .= '&filter_sub_category=' . $this->request->get['filter_sub_category'];
				$filter_sub_category = $this->request->get['filter_sub_category'];
			} else {
				$filter_sub_category = false;
			}

			if ($route == 'product/special' || isset($this->request->get['special'])) {
				$this->data['href'] = $this->data['href'].'&special=1';
				$special = true;
			} else {
				$special = false;
			}
		}

		if (isset($this->request->get['sort'])) {
				$this->data['href'] .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$this->data['href'] .= '&order=' . $this->request->get['order'];
			}
	
			if (isset($this->request->get['limit'])) {
				$this->data['href'] .= '&limit=' . $this->request->get['limit'];
			}

		$this->data['href'] .= '&char=';

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

			if (isset($setting['from_current'])) {
				$data = array(
					'filter_category_id' => $category_id,
					'filter_manufacturer_id' => $manufacturer_id,
					'filter_name'         => $filter_name, 
					'filter_tag'          => $filter_tag, 
					'filter_description'  => $filter_description,
					'filter_sub_category' => $filter_sub_category,
					'special'			  => $special
				);
				$allletters = $this->model_catalog_AlphabeticallyProducts->getletters($data);
			} else {
				$allletters = $this->model_catalog_AlphabeticallyProducts->getletters();
			}
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