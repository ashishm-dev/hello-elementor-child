<?php
/*
Template Name: Product Listing Page
*/
get_header();
?>

<div class="container product-listing-page">
    <h1><?php the_title(); ?></h1>

    <?php
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 2,
        'paged'          => $paged,
    );

    $products = new WP_Query($args);

    if ($products->have_posts()) :
    ?>
        <div class="products-grid">
            <?php while ($products->have_posts()) : $products->the_post(); ?>
                <div class="product-card">

                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail('medium'); ?>
                        <h3><?php the_title(); ?></h3>
                    </a>

                    <?php
                    // Product Categories
                   $terms = get_the_terms(get_the_ID(), 'product_category');
                    if ($terms && !is_wp_error($terms)) {
                        echo '<div class="product-category">';
                        foreach ($terms as $term) {
                            echo '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a> ';
                        }
                        echo '</div>';
                    }

                    // Product Price
                    $price = get_post_meta(get_the_ID(), '_product_price', true);
                    if ($price) :
                    ?>
                        <strong class="price">₹<?php echo esc_html($price); ?></strong>
                    <?php endif; ?>

                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php
            echo paginate_links(array(
                'total' => $products->max_num_pages
            ));
            ?>
        </div>

    <?php
        wp_reset_postdata();
    else :
        echo '<p>No products found.</p>';
    endif;
    ?>
</div>

<?php get_footer(); ?>
