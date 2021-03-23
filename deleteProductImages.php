<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
require 'app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$productSkus = ['MT07', 'MT07-XL-Gray'];

$removeDisabledImages = false; // Change this value 'true' for remove disabled images of product.

if (count($productSkus) > 0) {
	foreach ($productSkus as $sku) {
		$repositoryProduct = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface')->get($sku);

		$product = $objectManager->create('\Magento\Catalog\Model\Product')->load($repositoryProduct->getId());

		$productGallery = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Gallery');

		if ($removeDisabledImages) {

			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('catalog_product_entity_media_gallery_value');

			$sql = "Select * FROM " . $tableName." WHERE entity_id=".$product->getId()." AND disabled=1";
			$results = $connection->fetchAll($sql); // gives associated array, table fields as key in array.

			if (count($results) > 0) {
			    foreach($results as $image){
			        $productGallery->deleteGallery($image['value_id']);
			    }
			    $product->setMediaGalleryEntries([]);
			    $product->save();
			    echo "Product SKU {$product->getSku()} Images deleted Successfully";
			} else {
				echo "Product SKU {$product->getSku()}: There are no any disabled images found for this products<br/>";
			}


		} else {
			$gallery = $product->getMediaGalleryImages();

			if (count($gallery) > 0) {
			    foreach($gallery as $image){
			    	echo $image->getValueId().'<br/>';
			        $productGallery->deleteGallery($image->getValueId());
			    }
			    $product->setMediaGalleryEntries([]);
			    $product->save();
			    echo "Product SKU {$product->getSku()} Images deleted Successfully";
			} else {
				echo "Product SKU {$product->getSku()}: There are no any images for this products<br/>";
			}
		}
	}
}

