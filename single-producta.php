<?php get_header(); ?>

<div class="container single-product">
    <?php while (have_posts()) : the_post(); ?>

        <div class="single-product-wrapper">
            <div class="product-image">
                <?php the_post_thumbnail('large'); ?>
            </div>

            <div class="product-details">
                <h1><?php the_title(); ?></h1>

                <?php
                $price = get_post_meta(get_the_ID(), '_product_price', true);
                $terms = get_the_terms(get_the_ID(), 'producta_category');
                ?>

                <?php if ($price) : ?>
                    <p class="price">₹<?php echo esc_html($price); ?></p>
                <?php endif; ?>

                <?php if (!empty($terms)) : ?>
                    <p class="category">
                        Category: <?php echo esc_html($terms[0]->name); ?>
                    </p>
                <?php endif; ?>

                <div class="product-content">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>

    <?php endwhile; ?>
</div>
<form id="product-enquiry-form" class="enquiry-form">
    <h3>Product Enquiry</h3>

    <div class="form-group">
        <input type="text" name="name" placeholder="Your Name">
        <span class="error error-name"></span>
    </div>

    <div class="form-group">
        <input type="email" name="email" placeholder="Your Email">
        <span class="error error-email"></span>
    </div>

    <div class="form-group">
        <input type="text" name="phone" placeholder="Phone Number">
        <span class="error error-phone"></span>
    </div>

    <div class="form-group">
        <textarea name="message" placeholder="Your Message"></textarea>
        <span class="error error-message"></span>
    </div>

    <!-- <input type="hidden" name="product_id" value="23"> -->
<input type="hidden" name="product_id" value="<?php echo get_the_ID(); ?>">

    <button type="submit" class="btn-submit">Send Enquiry</button>

    <p class="form-response"></p>
</form>


<?php get_footer(); ?>
