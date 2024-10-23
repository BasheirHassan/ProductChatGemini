<?php
#############################################################
#	Enable Disable Products 1.09 for Opencart 4x by AlexDW	#
#############################################################
namespace Opencart\Admin\Model\Extension\ProductChatGemini;
class Endispro extends \Opencart\System\Engine\Model {

	public function enable_pro(int $product_id): void {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET `status`='1' WHERE product_id = '" . (int)$product_id . "'");
		$this->cache->delete('product');	
	}

	public function disable_pro(int $product_id): void {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET `status`='0' WHERE product_id = '" . (int)$product_id . "'");
		$this->cache->delete('product');	
	}

	public function enable_all(): void {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET `status`='1' ");
		$this->cache->delete('product');
	}

	public function disable_all(): void {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "product SET `status`='0' ");
		$this->cache->delete('product');
	}

}