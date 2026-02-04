<?php
$args = array(
    'post_type' => 'product',
    'posts_per_page' => -1,
);

$query = new WP_Query($args);

if ($query->have_posts()) :
    echo '<div class="products-grid">';
    while ($query->have_posts()) : $query->the_post();

        $price = get_post_meta(get_the_ID(), '_product_price', true);
        ?>
        <div class="product-card">
            <?php the_post_thumbnail('medium'); ?>
            <h3><?php the_title(); ?></h3>
            <p><?php echo wp_trim_words(get_the_content(), 15); ?></p>
            <strong>₹<?php echo esc_html($price); ?></strong>
        </div>
    <?php endwhile;
    echo '</div>';
    wp_reset_postdata();
else :
    echo '<p>No products found.</p>';
endif;
