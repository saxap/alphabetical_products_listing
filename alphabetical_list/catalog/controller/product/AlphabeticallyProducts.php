<?php
class ControllerProductAlphabeticallyProducts extends Controller{
        
    public function index(){
        
        $this->language->load('product/category');       
        $this->language->load('product/AlphabeticallyProducts');
        $this->load->model('catalog/AlphabeticallyProducts');
        $this->load->model('tool/image');
        

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else { 
			$page = 1;
		}	
							
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}
					
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
       		'separator' => false
   		);	
			
	
		if(isset($this->request->get['char'])) {
   			$char = $this->request->get['char'];                   
            if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else { 
				$page = 1;
			}
            
	    
	    if ($char == '' ){
            $title = str_replace('%char',mb_strtoupper($char),$this->language->get('bad_char'));
        } else {
            $title = str_replace('%char',mb_strtoupper($char),$this->language->get('heading_title'));
        }                
            
        $this->document->setTitle($title);
            
        $this->data['heading_title'] = $title;              
            
        $this->data['breadcrumbs'] = array();
        $this->data['href'] = HTTP_SERVER;
                

		$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),			
				'separator' => false
		);

		$url = '';

		//КАТЕГОРИИ
		if (isset($this->request->get['category'])) {
			$path = '';
			$this->load->model('catalog/category');
			$parts = explode('_', (string)$this->request->get['category']);
		
			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}
									
				$category_info = $this->model_catalog_category->getCategory($path_id);
				
				if ($category_info) {
	       			$this->data['breadcrumbs'][] = array(
   	    				'text'      => $category_info['name'],
						'href'      => $this->url->link('product/category', 'path=' . $path),
        				'separator' => $this->language->get('text_separator')
        			);
				}
			}		
		
			$category_id = array_pop($parts);
		} else {
			$category_id = false;
		}

		// ПРОИЗВОДИТЕЛЬ
		if (isset($this->request->get['manufacturer'])) {

			$this->load->model('catalog/manufacturer');
			$this->language->load('product/manufacturer');
			$manufacturer_id = $this->request->get['manufacturer'];

	   		
			$this->data['breadcrumbs'][] = array( 
	       		'text'      => $this->language->get('text_brand'),
				'href'      => $this->url->link('product/manufacturer'),
	      		'separator' => $this->language->get('text_separator')
	   		);

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

			if ($manufacturer_info) {
				$this->data['breadcrumbs'][] = array(
	       			'text'      => $manufacturer_info['name'],
					'href'      => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer']),
	      			'separator' => $this->language->get('text_separator')
	   			);
	        }

        } else {
        	$manufacturer_id = false;
        }

        // ПОИСК
        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_tag'])) {
        	$this->language->load('product/search');
	        if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			} else {
				$filter_name = '';
			} 			
			if (isset($this->request->get['filter_tag'])) {
				$filter_tag = $this->request->get['filter_tag'];
				$url .= '&filter_tag=' . urlencode(html_entity_decode($this->request->get['filter_tag'], ENT_QUOTES, 'UTF-8'));
			} elseif (isset($this->request->get['filter_name'])) {
				$filter_tag = $this->request->get['filter_name'];
				$url .= '&filter_tag=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			} else {
				$filter_tag = '';
			} 					
			if (isset($this->request->get['filter_description'])) {
				$filter_description = $this->request->get['filter_description'];
				$url .= '&filter_description=' . $this->request->get['filter_description'];
			} else {
				$filter_description = '';
			} 				
			if (isset($this->request->get['filter_category_id'])) {
				$category_id = $this->request->get['filter_category_id'];
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			} else {
				$category_id = false;
			} 	
			if (isset($this->request->get['filter_sub_category'])) {
				$filter_sub_category = $this->request->get['filter_sub_category'];
				$url .= '&filter_sub_category=' . $this->request->get['filter_sub_category'];
			} else {
				$filter_sub_category = '';
			} 

			$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('product/search', $url),
      		'separator' => $this->language->get('text_separator')
   			);

		} else {
			$filter_name = false;
			$filter_tag = false;
			$filter_description = false;
			$filter_sub_category = false;
		}

		// АКЦИИ
		if (isset($this->request->get['special'])) {
			
			$this->language->load('product/special');

			$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('product/special', $url),
      		'separator' => $this->language->get('text_separator')
   			);
			$special = true;
		} else {
			$special = false;
		}

            $this->data['breadcrumbs'][] = array(
        		'href'      => $this->url->link('product/AlphabeticallyProducts&char='.$char),
        		'text'      => $title,
        		'separator' => $this->language->get('text_separator')
      		);		
						
      		$this->data['heading_title'] = $title;
      		
      		$this->data['button_continue'] = $this->language->get('button_continue');
			
			$this->data['continue'] = (HTTP_SERVER . 'index.php?route=common/home');
            $this->data['text_error'] = $this->language->get('text_error');
			$this->data['text_refine'] = $this->language->get('text_refine');
			$this->data['text_empty'] = $this->language->get('text_empty');			
			$this->data['text_quantity'] = $this->language->get('text_quantity');
			$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$this->data['text_model'] = $this->language->get('text_model');
			$this->data['text_price'] = $this->language->get('text_price');
			$this->data['text_tax'] = $this->language->get('text_tax');
			$this->data['text_points'] = $this->language->get('text_points');
			$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
			$this->data['text_display'] = $this->language->get('text_display');
			$this->data['text_list'] = $this->language->get('text_list');
			$this->data['text_grid'] = $this->language->get('text_grid');
			$this->data['text_sort'] = $this->language->get('text_sort');
			$this->data['text_limit'] = $this->language->get('text_limit');
			$this->data['button_cart'] = $this->language->get('button_cart');
			$this->data['button_wishlist'] = $this->language->get('button_wishlist');
			$this->data['button_compare'] = $this->language->get('button_compare');
			$this->data['button_continue'] = $this->language->get('button_continue');              
              
              
                
			$this->data['products'] = array();
			
			$data = array(
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit,
				'filter_category_id' => $category_id,
				'filter_manufacturer_id' => $manufacturer_id,
				'filter_name'         => $filter_name, 
				'filter_tag'          => $filter_tag, 
				'filter_description'  => $filter_description,
				'filter_sub_category' => $filter_sub_category,  
				'char'				 => $char
			);
			if (!$special) {
				$results = $this->model_catalog_AlphabeticallyProducts->getProducts($data);
				$product_total = $this->model_catalog_AlphabeticallyProducts->getTotalProducts($data); 
			} else {	
				$results = $this->model_catalog_AlphabeticallyProducts->getProductsSpecials($data);
				$product_total = $this->model_catalog_AlphabeticallyProducts->getTotalProductsSpecials($data);
			}		
			


			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height'));
				} else {
					$image = false;
				}
				
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}
				
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$special = false;
				}	
				
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
				} else {
					$tax = false;
				}				
				
				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}	
				$this->data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $result['rating'],
					'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'])
				);
	
			}
	
			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
							
			$this->data['sorts'] = array();
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=p.sort_order&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=pd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=pd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=p.price&order=ASC' . $url)
			); 

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=p.price&order=DESC' . $url)
			); 
			
			if ($this->config->get('config_review_status')) {
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=rating&order=DESC' . $url)
				); 
				
				$this->data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=rating&order=ASC' . $url)
				);
			}
			
			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=p.model&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, '&sort=p.model&order=DESC' . $url)
			);
			
			$url = '';
	
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			$this->data['limits'] = array();
			
			$this->data['limits'][] = array(
				'text'  => $this->config->get('config_catalog_limit'),
				'value' => $this->config->get('config_catalog_limit'),
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, $url . '&limit=' . $this->config->get('config_catalog_limit'))
			);
						
			$this->data['limits'][] = array(
				'text'  => 25,
				'value' => 25,
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, $url . '&limit=25')
			);
			
			$this->data['limits'][] = array(
				'text'  => 50,
				'value' => 50,
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, $url . '&limit=50')
			);

			$this->data['limits'][] = array(
				'text'  => 75,
				'value' => 75,
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, $url . '&limit=75')
			);
			
			$this->data['limits'][] = array(
				'text'  => 100,
				'value' => 100,
				'href'  => $this->url->link('product/AlphabeticallyProducts&char='.$char, $url . '&limit=100')
			);
						
			$url = '';
	
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
	
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
					
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('product/AlphabeticallyProducts&char='.$char, $url . '&page={page}');
		

			$this->data['pagination'] = $pagination->render();
		
			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;
		
			$this->data['continue'] = $this->url->link('common/home');

			



			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/AlphabeticallyProducts.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/product/AlphabeticallyProducts.tpl';
			} else {
				$this->template = 'default/template/product/AlphabeticallyProducts.tpl';
			}
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
				
			$this->response->setOutput($this->render());										
    	} else {
			$url = '';
												
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}	

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
				
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_error'),
				'href'      => $this->url->link('product/AlphabeticallyProducts&char=', $url),
				'separator' => $this->language->get('text_separator')
			);
				
			$this->document->setTitle($this->language->get('text_error'));

      		$this->data['heading_title'] = $this->language->get('text_error');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');

      		$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
					
			$this->response->setOutput($this->render());

        }
    }
}
?>