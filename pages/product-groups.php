<div class="wrap">
	<h1>Product Groups</h1>
	<h2 class="bikmo-sub">Add Product Group</h2>
	<form method="post" action="?<?php echo http_build_query(array('page' => 'bikmo-product-groups', 'pagenum' => $pageNum)); ?>">
		<table class="form-table" style="width: 320px; margin-bottom:15px;">  
			<tr valign="top">
				<th scope="row">
					<label for="name">Name</label>
				</th>
				<td align="right">
					<input type="text" name="name" id="name" value="" placeholder="Group Name" />
				</td>
				<td colspan="2" align="right">
					<input type="submit" class="button button-primary" value="Add Product Group" />
				</td>
			</tr>			
		</table>	
	</form>
	<h2 class="bikmo-sub">Product Groups</h2>
	<?php if (!empty($productGroups)) : ?>
		<?php
		$pagination = paginate_links(array(
			'base' => add_query_arg('pagenum', '%#%'),
			'format' => '',
			'prev_text' => 'prev',
			'next_text' => 'next',
			'total' => ceil($count / $limit),
			'current' => $pageNum
		));
		?>
		<?php if ($pagination) : ?>
			<div class = "tablenav">
				<div class = "tablenav-pages" style = "margin: 1em 0">
					<?php echo $pagination; ?>
				</div>
			</div>		
		<?php endif; ?>
		<table class="widefat fixed bikmo">
			<tr class="thead">
				<th>Name</th>
				<th>Product Count</th>
				<th>Actions</th>
			</tr>
			<?php foreach ($productGroups as $group) : ?>
				<tr>
					<td><?php echo $group['name']; ?></td>
					<td><?php echo $group['product_count']; ?></td>
					<td>
						<ul>
							<li>[bikmo_products id="<?php echo $group['id']; ?>"]</li>
							<li><a href="?<?php echo http_build_query(array('page' => 'add-bikmo-product', 'group-id' => $group['id'])); ?>">Add Products</a></li>
							<li><a href="?<?php echo http_build_query(array_merge($_GET, array('action' => 'edit', 'id' => $group['id']))); ?>">Edit Group</a></li>				
							<li><a href="?<?php echo http_build_query(array_merge($_GET, array('action' => 'delete', 'id' => $group['id']))); ?>">Delete Group</a></li>								
						</ul>
					</td>			
				</tr>
			<?php endforeach; ?>
		</table>
	<?php else : ?>
		<p>No product groups have been added yet.</p>
	<?php endif; ?>
</div>