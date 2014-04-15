<div class="wrap">
	<h1>Group - <?php echo $group['name']; ?></h1>

	<p><a class="button-secondary action" href='?<?php echo http_build_query(array('page' => 'add-bikmo-product', 'group-id' => $group['id'])); ?>'>Add products</a></p>

	<h2>Products</h2>

	<?php if (!empty($products)) : ?>
		<table class="widefat fixed bikmo" cellspacing="0">
			<thead>
				<tr class="thead">
					<th>Image</th>
					<th>Name</th>
					<th>Price</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($products as $product) : ?>
					<tr>
						<td>
							<img src="<?php echo $product['main_image']; ?>" width="100" />
						</td>
						<td>
							<?php echo $product['name']; ?>
						</td>
						<td>
							&pound;<?php echo number_format($product['best_price'], 2); ?>
						</td>
						<td>
							<ul>
								<li>
									<a href="?<?php echo http_build_query(array_merge($_GET, array('sub-action' => 'delete', 'sub-id' => $product['_id']))); ?>">Delete</a>
								</li>
							</ul>
						</td>
					</tr>
				<?php endforeach; ?>	
			</tbody>
		</table>
	<?php else: ?>
		<p>No products have been added to this group yet.</p>
	<?php endif; ?>
</div>

