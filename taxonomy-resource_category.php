<?php get_header(); ?>

<section class="resource-category-archive">
    <div class="container">
        <h1><?php single_term_title(); ?></h1>

        <?php if (have_posts()) : ?>
            <div class="resource-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article class="resource-card">
                        <a href="<?php the_permalink(); ?>">
                            <h2><?php the_title(); ?></h2>
                            <p><?php the_excerpt(); ?></p>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p>No resources found in this category.</p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
