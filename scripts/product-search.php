<?php
if (empty($_POST['query'])) {
	exit;
}

require_once '../classes/BikmoHttp.php';

$http = new BikmoHttp;

$products = $http->request(array('terms' => $_POST['query'], 'limit' => $_POST['limit']));

if (!$products) {
	exit;
}
?>
<form method="post" action="?<?php echo http_build_query(array('page' => 'bikmo-product-groups', 'action' => 'edit', 'id' => $_GET['id'])); ?>">
	<div class="tablenav">
		<div class="alignleft actions">			
			<input type="submit" value="Add Products" class="button-secondary action">
		</div>
	</div>
	<table class="widefat fixed bikmo">
		<tr class="thead">
			<th>Add</th>
			<th>Image</th>
			<th>Name</th>
			<th>Price</th>			
		</tr>
		<?php foreach ($products as $product) : ?>
			<tr>
				<td>
					<input type="checkbox" name="products[]" value="<?php echo $product['_id']; ?>" />
				</td>
				<td>
					<img src="<?php echo $product['main_image']; ?>" width="100" />
				</td>
				<td>
					<?php echo $product['name']; ?>
				</td>
				<td>
					&pound;<?php echo number_format($product['best_price'], 2); ?>
				</td>				
			</tr>
		<?php endforeach; ?>
	</table>
</form>


