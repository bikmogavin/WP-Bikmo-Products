<div class="bikmo-products">
    <?php
    $count = 1;
    foreach ($products as $product) :
        if ($count > $limit)
            break;
        ?>
        <div class="bikmo-product">
            <h2><?php echo $product['name']; ?></h2>
            <img width="100" src="<?php echo $product['main_image']; ?>" alt="<?php echo $product['name']; ?>" />
            <p>
                Price : &pound;<?php echo number_format($product['best_price'], 2); ?>
            </p>			
            <p>
                RRP : &pound;<?php echo number_format($product['rrp'], 2); ?>
            </p>
            <?php if ($discount = WP_Bikmo_Products::discount($product['best_price'], $product['rrp'])) : ?>
                <p>
                    Discount : <?php echo $discount; ?>%
                </p>
            <?php endif; ?>
            <p>
                <a href="<?php echo WP_Bikmo_Products::linkify($product['manufacturer'], $product['name']); ?>">
                    More Information
                </a>
            </p> 
            <p>
                <a href="<?php echo WP_Bikmo_Products::deepLink($product['deep_link']); ?>">
                    Buy Cheapest
                </a>               
            </p>          
        </div>
        <?php
        $count++;
    endforeach;
    ?>
</div>
