<?php
class ModelExtensionFeedGoogleMerchantCenter extends Model {
	public function getTaxonomy($category_id) {
		$query = $this->db->query("SELECT `".DB_PREFIX."feed_manager_taxonomy`.taxonomy_id,`".DB_PREFIX."feed_manager_taxonomy`.name FROM `".DB_PREFIX."feed_manager_category` RIGHT JOIN `".DB_PREFIX."feed_manager_taxonomy` ON `".DB_PREFIX."feed_manager_category`.taxonomy_id=`".DB_PREFIX."feed_manager_taxonomy`.taxonomy_id WHERE `".DB_PREFIX."feed_manager_category`.category_id LIKE '".$category_id."' OR `".DB_PREFIX."feed_manager_taxonomy`.status LIKE '1' ORDER BY LENGTH(`".DB_PREFIX."feed_manager_taxonomy`.name) DESC LIMIT 1;");
		return $query->row;
	}

	public function getProductExtra($product_id,$attribute_id,$lang,$attribute_is_option=false) {
		if ($attribute_id=='-1' || $attribute_is_option){
			$query = $this->db->query("SELECT gtp.color,gtp.age_group,gtp.gender FROM `".DB_PREFIX."feed_manager_product` gtp WHERE gtp.product_id LIKE '".$product_id."';");
		} else {
			$query = $this->db->query("SELECT pa.text as color, gtp.age_group,gtp.gender FROM `".DB_PREFIX."product_attribute` pa LEFT JOIN `".DB_PREFIX."feed_manager_product` gtp ON (gtp.product_id LIKE '".$product_id."') WHERE pa.product_id LIKE '".$product_id."' AND pa.attribute_id LIKE '".$attribute_id."' AND pa.language_id LIKE '".$lang."' LIMIT 1;");
		}
		if ($query->rows)
			return $query->row;
	}

	public function getProductExtraType($product_id,$attribute_id,$lang) {
		$query = $this->db->query("SELECT pa.text FROM `".DB_PREFIX."product_attribute` pa WHERE pa.product_id LIKE '".$product_id."' AND pa.attribute_id LIKE '".$attribute_id."' AND pa.language_id LIKE '".$lang."'  LIMIT 1;");
		if ($query->rows)
			return $query->row['text'];
	}

	public function isApparel($taxonomy_id) {
		$query = $this->db->query("SELECT count(*) AS count FROM `".DB_PREFIX."feed_manager_taxonomy` WHERE ".DB_PREFIX."feed_manager_taxonomy.taxonomy_id LIKE '".$taxonomy_id."' AND ".DB_PREFIX."feed_manager_taxonomy.name LIKE 'Apparel & Accessories%';");
		return $query->row['count'];
	}

	public function getProductOptions($product_id,$option_id,$lang) {

		$product_option_query = $this->db->query("SELECT ".DB_PREFIX."option_value_description.name,".DB_PREFIX."product_option_value.price,".DB_PREFIX."product_option_value.price_prefix,".DB_PREFIX."product_option_value.quantity FROM ".DB_PREFIX."product_option_value LEFT JOIN ".DB_PREFIX."option_value_description ON ".DB_PREFIX."option_value_description.option_value_id=".DB_PREFIX."product_option_value.option_value_id WHERE ".DB_PREFIX."product_option_value.product_id LIKE '".$product_id."' AND ".DB_PREFIX."product_option_value.option_id LIKE '".$option_id."' AND ".DB_PREFIX."option_value_description.language_id LIKE '" . (int)$lang . "' AND (".DB_PREFIX."product_option_value.subtract LIKE '0' OR ".DB_PREFIX."product_option_value.quantity > 0) GROUP BY ".DB_PREFIX."option_value_description.name;");
		return $product_option_query->rows;
	}

	public function getTax() {
		$query = $this->db->query("SELECT iso_code_2,rate FROM ".DB_PREFIX."tax_rate tr LEFT JOIN ".DB_PREFIX."zone_to_geo_zone tgz ON tr.geo_zone_id=tgz.geo_zone_id  RIGHT JOIN ".DB_PREFIX."country c ON c.country_id=tgz.country_id WHERE type LIKE 'P' GROUP BY c.iso_code_2;");
		return $query->rows;
	}

	public function getShipping() {
		$query = $this->db->query("SELECT c.iso_code_2 FROM ".DB_PREFIX."zone_to_geo_zone ztgz LEFT JOIN ".DB_PREFIX."country c ON (c.country_id=ztgz.country_id) GROUP BY c.iso_code_2;");
		return $query->rows;
	}

	public function getLangID($lang_code) {
		$query = $this->db->query("SELECT language_id FROM ".DB_PREFIX."language WHERE code LIKE '".$lang_code."';");
		return $query->row['language_id'];
	}

	public function getProducts($lang,$store, $data = array()) {
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

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$lang . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$store . "'";

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
			$product_data[$result['product_id']] = $this->getProduct($result['product_id'],$lang,$store);
		}

		return $product_data;
	}

	public function getProduct($product_id,$lang,$store) {
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT date_start FROM " . DB_PREFIX . "product_special ds WHERE ds.product_id = p.product_id AND ds.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ds.date_start = '0000-00-00' OR ds.date_start < NOW()) AND (ds.date_end = '0000-00-00' OR ds.date_end > NOW())) ORDER BY ds.priority ASC, ds.price ASC LIMIT 1) AS date_start, (SELECT date_end FROM " . DB_PREFIX . "product_special de WHERE de.product_id = p.product_id AND de.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((de.date_start = '0000-00-00' OR de.date_start < NOW()) AND (de.date_end = '0000-00-00' OR de.date_end > NOW())) ORDER BY de.priority ASC, de.price ASC LIMIT 1) AS date_end, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$lang . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$lang . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$lang . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$lang . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$store . "'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'date_start'       => $query->row['date_start'],
				'date_end'         => $query->row['date_end'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}

	public function getCategory($category_id,$lang,$store) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$lang. "' AND c2s.store_id = '" . (int)$store . "' AND c.status = '1'");

		return $query->row;
	}
}
?>
