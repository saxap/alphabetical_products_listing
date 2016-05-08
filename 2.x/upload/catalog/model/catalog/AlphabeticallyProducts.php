<?php
class ModelCatalogAlphabeticallyProducts extends model{    


	public function getProducts($data = array()) {
		if(empty($data['char'])) return array();

		$this->load->model('catalog/product');

		$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		if ($data['special'])	{
			$sql .= " INNER JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id)";	
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if(!empty($data['char'])) { 
			if ($data['char'] == '0-9') {
				$sql .= " AND (name LIKE '1%' OR name LIKE '2%' OR name LIKE '3%' OR name LIKE '3%' OR name LIKE '4%' OR name LIKE '5%' OR name LIKE '6%' OR name LIKE '7%' OR name LIKE '8%' OR name LIKE '9%' OR name LIKE '0%')";
			} else {
				$sql .= " AND (name LIKE '".mb_strtoupper($data['char'])."%' OR name LIKE '".mb_strtolower($data['char'])."%')";
			}
		}


		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
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
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
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
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $product_data;

	}












	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		if ($data['special'])	{
			$sql .= " INNER JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id)";	
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if(!empty($data['char'])) { 
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

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			$sql .= ")";
		}

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
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
		


		$sql = "SELECT SUBSTRING(UPPER(pd.name), 1, 1) as 'letter' FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";

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
		$sql .= " GROUP BY letter HAVING letter IN ('". implode('\',\'', $lat_letters) ."')";

		$query = $this->db->query($sql);	

		foreach ($query->rows as $row) {
			$letters['lat'][] = $row['letter'];
		}


		$sql = "SELECT SUBSTRING(UPPER(pd.name), 1, 1) as 'letter' FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
		if ($data['special']) {
			$sql .= " INNER JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id)";	
		}
		if (!empty($data['filter_category_id'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
		}	
		if (!empty($data['filter_tag'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_tag pt ON (p.product_id = pt.product_id)";			
		}				
		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
					
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

		$sql .= " GROUP BY letter HAVING letter IN ('". implode('\',\'', $cyr_letters) ."')";

		$query = $this->db->query($sql);	

		foreach ($query->rows as $row) {
				$letters['cyr'][] = $row['letter'];
		}


		$sql = "SELECT SUBSTRING(pd.name, 1, 1) as 'letter' FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";
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

		$sql .= " GROUP BY letter HAVING letter IN ('". implode('\',\'', $numbers) ."')";

		$query = $this->db->query($sql);	

		foreach ($query->rows as $row) {
			$letters['number'][] = $row['letter'];
		}

		return $letters;
	}
}
?>