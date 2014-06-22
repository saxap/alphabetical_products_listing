<?php
class ModelCatalogAlphabeticallyProducts extends model{    


	public function getProduct($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
				
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$customer_group_id . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		if ($query->num_rows) {
			$query->row['price'] = ($query->row['discount'] ? $query->row['discount'] : $query->row['price']);
			$query->row['rating'] = (int)$query->row['rating'];
			
			return $query->row;
		} else {
			return false;
		}
	}

	public function getProducts($data = array()) {
		if(empty($data['char'])) return array();

		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		
		$cache = md5(http_build_query($data));
		
		$product_data = $this->cache->get('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache);
	
		if (!$product_data) {
			$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)"; 

			if (!empty($data['filter_tag'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_tag pt ON (p.product_id = pt.product_id)";			
			}
						
			if (!empty($data['filter_category_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
			}
			
			
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"; 
			
			if(!empty($data['char'])){ 
				if ($data['char'] == '0-9') {
					$sql .= " AND (name LIKE '1%' OR name LIKE '2%' OR name LIKE '3%' OR name LIKE '3%' OR name LIKE '4%' OR name LIKE '5%' OR name LIKE '6%' OR name LIKE '7%' OR name LIKE '8%' OR name LIKE '9%' OR name LIKE '0%')";
				} else {
					$sql .= " AND (name LIKE '".mb_strtoupper($data['char'])."%' OR name LIKE '".mb_strtolower($data['char'])."%')";
				}
			} else { 
			$sql .= ""; }
			
			
			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";
											
				if (!empty($data['filter_name'])) {
					$implode = array();
					
					$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));
					
					foreach ($words as $word) {
						if (!empty($data['filter_description'])) {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						} else {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						}				
					}
					
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				
				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}
				
				if (!empty($data['filter_tag'])) {
					$implode = array();
					
					$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_tag'])));
					
					foreach ($words as $word) {
						$implode[] = "LCASE(pt.tag) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
					}
					
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . " AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "'";
					}
				}
			
				$sql .= ")";
			}
						
			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();
					
					$implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
					
					$this->load->model('catalog/category');
					
					$categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);
										
					foreach ($categories as $category_id) {
						$implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
					}
								
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";			
				} else {
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
			}		
					
			if (!empty($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
			}
			
			$sql .= " GROUP BY p.product_id";
			
			$sort_data = array(
				'pd.name',
				'p.model',
				'p.quantity',
				'p.price',
				'rating',
				'p.sort_order',
				'p.date_added'
			);	
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
					$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
				} else {
					$sql .= " ORDER BY " . $data['sort'];
				}
			} else {
				$sql .= " ORDER BY p.sort_order";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC, LCASE(pd.name) DESC";
			} else {
				$sql .= " ASC, LCASE(pd.name) ASC";
			}
		
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}				
	
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
			
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
		
			
			
			$product_data = array();
					
			$query = $this->db->query($sql);
		
			foreach ($query->rows as $result) {
			
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}
			
			$this->cache->set('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache, $product_data);
	
		
		}

		return $product_data;
	}

	public function getProductsSpecials($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		
		if(!empty($data['char'])){ 
			if ($data['char'] == '0-9') {
				$sqlforchars = " AND (name LIKE '1%' OR name LIKE '2%' OR name LIKE '3%' OR name LIKE '3%' OR name LIKE '4%' OR name LIKE '5%' OR name LIKE '6%' OR name LIKE '7%' OR name LIKE '8%' OR name LIKE '9%' OR name LIKE '0%')";
				} else {
				$sqlforchars = " AND (name LIKE '".mb_strtoupper($data['char'])."%' OR name LIKE '".mb_strtolower($data['char'])."%')";
			}
		} else { 
			$sqlforchars = ""; }

		$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ".$sqlforchars." GROUP BY ps.product_id";


		$sort_data = array(
			'pd.name',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}
	
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $result) { 		
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}
		
		return $product_data;
	}


public function getTotalProducts($data = array()) {

		if(empty($data['char'])) return 0;

		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";

		if (!empty($data['filter_category_id'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
		}
		
		if (!empty($data['filter_tag'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_tag pt ON (p.product_id = pt.product_id)";			
		}
					
		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		
		if(!empty($data['char'])){ 
			if ($data['char'] == '0-9') {
				$sql .= " AND (name LIKE '1%' OR name LIKE '2%' OR name LIKE '3%' OR name LIKE '3%' OR name LIKE '4%' OR name LIKE '5%' OR name LIKE '6%' OR name LIKE '7%' OR name LIKE '8%' OR name LIKE '9%' OR name LIKE '0%')";
			} else {
				$sql .= " AND (name LIKE '".mb_strtoupper($data['char'])."%' OR name LIKE '".mb_strtolower($data['char'])."%')";
			}
		}			
		
		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";
								
			if (!empty($data['filter_name'])) {
				$implode = array();
				
				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));
				
				foreach ($words as $word) {
					if (!empty($data['filter_description'])) {
						$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
					} else {
						$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
					}				
				}
				
				if ($implode) {
					$sql .= " " . implode(" OR ", $implode) . "";
				}
			}
			
			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}
			
			if (!empty($data['filter_tag'])) {
				$implode = array();
				
				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_tag'])));
				
				foreach ($words as $word) {
					$implode[] = "LCASE(pt.tag) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
				}
				
				if ($implode) {
					$sql .= " " . implode(" OR ", $implode) . " AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "'";
				}
			}
		
			$sql .= ")";
		}
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$implode_data = array();
				
				$implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				
				$this->load->model('catalog/category');
				
				$categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);
					
				foreach ($categories as $category_id) {
					$implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
				}
							
				$sql .= " AND (" . implode(' OR ', $implode_data) . ")";			
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}
		}		
		
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}

	public function getTotalProductsSpecials($data) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}		
		
		if(!empty($data['char'])){ 
			if ($data['char'] == '0-9') {
				$sqlforchars = " AND (name LIKE '1%' OR name LIKE '2%' OR name LIKE '3%' OR name LIKE '3%' OR name LIKE '4%' OR name LIKE '5%' OR name LIKE '6%' OR name LIKE '7%' OR name LIKE '8%' OR name LIKE '9%' OR name LIKE '0%')";
				} else {
				$sqlforchars = " AND (name LIKE '".mb_strtoupper($data['char'])."%' OR name LIKE '".mb_strtolower($data['char'])."%')";
			}
		} else { 
			$sqlforchars = ""; }

		$query = $this->db->query("SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))".$sqlforchars);
		
		if (isset($query->row['total'])) {
			return $query->row['total'];
		} else {
			return 0;	
		}
	}	

	
	public function getletters($data = false) {
		$letters = array();

		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		
		$lat_letters = array( 0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G',
								7 => 'H', 8 => 'I', 9 => 'J', 10 => 'K', 11 => 'L', 12 => 'M', 13 => 'N',
								14 => 'O', 15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U',
								21 => 'W', 22 => 'X', 23 => 'Y', 24 => 'Z'
							);
		$cyr_letters = array( 0 => 'А', 1 => 'Б', 2 => 'В', 3 => 'Г', 4 => 'Д', 5 => 'Е', 6 => 'Ж',
								7 => 'З', 8 => 'И', 9 => 'К', 10 => 'Л', 11 => 'М', 12 => 'Н', 13 => 'О',
								14 => 'П', 15 => 'Р', 16 => 'С', 17 => 'Т', 18 => 'У', 19 => 'Ф', 20 => 'Х',
								21 => 'Ц', 22 => 'Ч', 23 => 'Ш', 24 => 'Щ', 25 => 'Э', 26 => 'Ю', 27 => 'Я'
							);
		$numbers = array( 0 => '1', 1 => '2', 2 => '3', 3 => '4', 4 => '5', 5 => '6', 6 => '7', 7 => '8', 8 => '9', 9 => '0' );
		
		foreach ( $lat_letters as $lat_letter ) {
			//$sql = "SELECT `name` FROM `" . DB_PREFIX . "product_description` WHERE name LIKE '".mb_strtoupper($lat_letter)."%' OR name LIKE '".mb_strtolower($lat_letter)."%'";
			$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
			if ($data['special'])	{
				$sql .= " INNER JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id)";	
			}
			if (!empty($data['filter_category_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
			}	
			if (!empty($data['filter_tag'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_tag pt ON (p.product_id = pt.product_id)";			
			}			
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			
			$sql .= " AND (name LIKE '".mb_strtoupper($lat_letter)."%' OR name LIKE '".mb_strtolower($lat_letter)."%')";			
			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";						
				if (!empty($data['filter_name'])) {
					$implode = array();		
					$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));			
					foreach ($words as $word) {
						if (!empty($data['filter_description'])) {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						} else {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						}				
					}			
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}
				if (!empty($data['filter_tag'])) {
					$implode = array();	
					$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_tag'])));
					foreach ($words as $word) {
						$implode[] = "LCASE(pt.tag) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
					}
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . " AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "'";
					}
				}
				$sql .= ")";
			}	
			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();
					$implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
					$this->load->model('catalog/category');
					$categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);
					foreach ($categories as $category_id) {
						$implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
					}	
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";			
				} else {
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
			}		
			if (!empty($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
			}
			if ($data['special']) {
				$sql .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) AND ps.customer_group_id = '" . (int)$customer_group_id . "'";
			}
			//die($sql);
			$sql .= " LIMIT 0,1";
			$query = $this->db->query($sql);	
			if ($query->row['total']) {
				$letters['lat'][] = $lat_letter;
			}
		}
		foreach ( $cyr_letters as $cyr_letter ) {
			$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
			if ($data['special'])	{
				$sql .= " INNER JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id)";	
			}
			if (!empty($data['filter_category_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
			}	
			if (!empty($data['filter_tag'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_tag pt ON (p.product_id = pt.product_id)";			
			}				
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			
			$sql .= " AND (name LIKE '".mb_strtoupper($cyr_letter)."%' OR name LIKE '".mb_strtolower($cyr_letter)."%')";			
			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";						
				if (!empty($data['filter_name'])) {
					$implode = array();		
					$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));			
					foreach ($words as $word) {
						if (!empty($data['filter_description'])) {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						} else {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						}				
					}			
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}
				if (!empty($data['filter_tag'])) {
					$implode = array();	
					$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_tag'])));
					foreach ($words as $word) {
						$implode[] = "LCASE(pt.tag) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
					}
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . " AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "'";
					}
				}
				$sql .= ")";
			}	
			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();
					$implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
					$this->load->model('catalog/category');
					$categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);
					foreach ($categories as $category_id) {
						$implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
					}	
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";			
				} else {
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
			}		
			if (!empty($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
			}
			if ($data['special']) {
				$sql .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) AND ps.customer_group_id = '" . (int)$customer_group_id . "'";
			}
			$sql .= "LIMIT 0,1";
			$query = $this->db->query($sql);	
			if ($query->row['total']) {
				$letters['cyr'][] = $cyr_letter;
			}
		}

		foreach ( $numbers as $number ) {

			$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
			if ($data['special'])	{
				$sql .= " INNER JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id)";	
			}
			if (!empty($data['filter_category_id'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
			}	
			if (!empty($data['filter_tag'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_tag pt ON (p.product_id = pt.product_id)";			
			}				
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			
			$sql .= " AND (name LIKE '".mb_strtoupper($number)."%' OR name LIKE '".mb_strtolower($number)."%')";			
			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql .= " AND (";						
				if (!empty($data['filter_name'])) {
					$implode = array();		
					$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));			
					foreach ($words as $word) {
						if (!empty($data['filter_description'])) {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						} else {
							$implode[] = "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
						}				
					}			
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . "";
					}
				}
				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}
				if (!empty($data['filter_tag'])) {
					$implode = array();	
					$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_tag'])));
					foreach ($words as $word) {
						$implode[] = "LCASE(pt.tag) LIKE '%" . $this->db->escape(utf8_strtolower($word)) . "%'";
					}
					if ($implode) {
						$sql .= " " . implode(" OR ", $implode) . " AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "'";
					}
				}
				$sql .= ")";
			}	
			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();
					$implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
					$this->load->model('catalog/category');
					$categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);
					foreach ($categories as $category_id) {
						$implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
					}	
					$sql .= " AND (" . implode(' OR ', $implode_data) . ")";			
				} else {
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
			}		
			if (!empty($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
			}
			if ($data['special']) {
				$sql .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) AND ps.customer_group_id = '" . (int)$customer_group_id . "'";
			}
			$sql .= "LIMIT 0,1";
			$query = $this->db->query($sql);	
			if ($query->row['total']) {
				$letters['number'][] = $number;
			}
		}
		return $letters;
	}
}
?>