<div class="wrap">
	<?php if (!empty($group)) : ?>

		<h1>Add Products - <?php echo $group['name']; ?></h1>

		<form style="margin-bottom: 20px;" id="product-search-form" method="post" action="<?php echo plugins_url('scripts/product-search.php', __DIR__); ?>?id=<?php echo $group['id']; ?>">
			<table class="form-table bikmo" style="width:450px;">  
				<tr valign="top">
					<th scope="row">
						<label for="query">Search Query</label>
					</th>
					<td align="left">
						<input type="text" name="query" id="product-search" placeholder="Product Search" />
					</td>
					<td align="left">
						<select id="limit" name="limit">
							<option value="10">Limit - 10</option>
							<option value="20" selected>Limit - 20</option>
							<option value="50">Limit - 50</option>
							<option value="100">Limit - 100</option>
						</select>
					</td>
					<td>
						<img id="ajax-loader" src="<?php echo plugins_url('images/ajax-loader.gif', __DIR__); ?>" />
					</td>
				</tr>			
			</table>	
		</form>

		<div id="search-results">

		</div>

	<?php else: ?>
		<p>The group you have selected doesn't exist.</p>
	<?php endif; ?>
</div>
