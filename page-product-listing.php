<?php
/* Template Name: Product Listing */
get_header();
?>

<div class="product-listing-wrapper">
    <h1><?php the_title(); ?></h1>

   
    <div class="product-filters">
        <button class="filter-btn active" data-category="">All</button>

        <?php
        $terms = get_terms(array(
            'taxonomy' => 'product_category',
            'hide_empty' => true,
        ));

        if (!empty($terms)) {
            foreach ($terms as $term) {
                echo '<button class="filter-btn" data-category="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</button>';
            }
        }
        ?>
    </div>

   
    <div id="product-results">
        <?php get_template_part('template-parts/product-loop'); ?>
    </div>
</div>

<?php get_footer(); ?>
