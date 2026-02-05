<?php get_header(); ?>

<div class="container product-archive">
    <h1 class="page-title"><?php post_type_archive_title(); ?></h1>

    <?php if (have_posts()) : ?>
        <div class="products-grid">
            <?php while (have_posts()) : the_post(); ?>
                <div class="product-card">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium'); ?>
                        <?php endif; ?>

                        <h3><?php the_title(); ?></h3>

                        <?php
                        $price = get_post_meta(get_the_ID(), '_product_price', true);
                        $terms = get_the_terms(get_the_ID(), 'producta_category');
                        ?>

                        <?php if (!empty($terms)) : ?>
                            <p class="product-category">
                                <?php echo esc_html($terms[0]->name); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($price) : ?>
                            <strong class="price">₹<?php echo esc_html($price); ?></strong>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination demo">
            <?php the_posts_pagination(); ?>
        </div>

    <?php else : ?>
        <p>No products found.</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
