<?php
/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */  

add_action( 'wp_enqueue_scripts', 'hello_elementor_child_style' );
				function hello_elementor_child_style() {
					wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
					wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
				}

/**
 * Your code goes below.
 */

function register_product_cpt() {

    $labels = array(
        'name'               => 'Productas',
        'singular_name'      => 'Producta',
        'add_new'            => 'Add New Producta',
        'add_new_item'       => 'Add New Producta',
        'edit_item'          => 'Edit Producta',
        'new_item'           => 'New Producta',
        'view_item'          => 'View Producta',
        'search_items'       => 'Search Productas',
        'not_found'          => 'No products found',
        'menu_name'          => 'Productas'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-cart',
        'supports'           => array(
            'title',
            'editor',          
            'thumbnail'       
        ),
        'rewrite'            => array('slug' => 'productas'),
    );

    register_post_type('producta', $args);
}
add_action('init', 'register_product_cpt');



function register_product_taxonomy() {

    $labels = array(
        'name'              => 'Producta Categories',
        'singular_name'     => 'Producta Category',
        'search_items'      => 'Search Producta Categories',
        'all_items'         => 'All Producta Categories',
        'parent_item'       => 'Parent Category',
        'edit_item'         => 'Edit Category',
        'update_item'       => 'Update Category',
        'add_new_item'      => 'Add New Category',
        'menu_name'         => 'Producta Categories',
    );

    $args = array(
        'hierarchical'      => true, 
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array('slug' => 'producta-category'),
    );

    register_taxonomy('producta_category', array('producta'), $args);
}
add_action('init', 'register_product_taxonomy');

function add_product_price_metabox() {
    add_meta_box(
        'producta_price',
        'Producta Price',
        'producta_price_callback',
        'producta',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_product_price_metabox');

function producta_price_callback($post) {
    $price = get_post_meta($post->ID, '_product_price', true);
    ?>
    <label>Price</label>
    <input type="number" step="0.01" name="producta_price" 
           value="<?php echo esc_attr($price); ?>" 
           style="width:100%;" />
    <?php
}


function save_product_price($post_id) {

    if (isset($_POST['producta_price'])) {
        update_post_meta(
            $post_id,
            '_product_price',
            sanitize_text_field($_POST['producta_price'])
        );
    }
}
add_action('save_post', 'save_product_price');


function product_ajax_scripts() {

    wp_enqueue_script(
        'product-filter',
        get_template_directory_uri() . '/js/product-filter.js',
        array('jquery'),
        null,
        true
    );

    wp_localize_script('product-filter', 'product_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'product_ajax_scripts');


function filter_products_ajax() {

    $category = $_POST['category'];

    $args = array(
        'post_type' => 'producta',
        'posts_per_page' => -1,
    );

    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'producta_category',
                'field'    => 'slug',
                'terms'    => $category,
            )
        );
    }

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

    wp_die();
}

add_action('wp_ajax_filter_products', 'filter_products_ajax');
add_action('wp_ajax_nopriv_filter_products', 'filter_products_ajax');




//Enqueue JS & Localize AJAX URL
function enqueue_enquiry_scripts() {
    wp_enqueue_script('jquery');

    wp_localize_script('jquery', 'enquiry_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('enquiry_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_enquiry_scripts');


//  Ajax handler
  
add_action('wp_ajax_submit_product_enquiry', 'handle_product_enquiry');
add_action('wp_ajax_nopriv_submit_product_enquiry', 'handle_product_enquiry');

function handle_product_enquiry() {

    check_ajax_referer('enquiry_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'product_enquiriesz';

    $name       = sanitize_text_field($_POST['name']);
    $email      = sanitize_email($_POST['email']);
    $phone      = sanitize_text_field($_POST['phone']);
    $message    = sanitize_textarea_field($_POST['message']);
    $product_id = intval($_POST['product_id']);

    // if (empty($name) || empty($email) || empty($phone) || empty($message)) {
    //     wp_send_json_error(array(
    //         'message' => 'All fields are required.'
    //     ));
    // }
    $errors = array();

if ( empty($name) ) {
    $errors['name'] = 'Name is required.';
}

if ( empty($email) ) {
    $errors['email'] = 'Email is required.';
} elseif ( ! is_email($email) ) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ( empty($phone) ) {
    $errors['phone'] = 'Phone number is required.';
} elseif ( ! preg_match('/^[0-9]{10}$/', $phone) ) {
    $errors['phone'] = 'Please enter a valid 10-digit phone number.';
}

if ( empty($message) ) {
    $errors['message'] = 'Message is required.';
}

if ( ! empty($errors) ) {
    wp_send_json_error(array(
        'errors' => $errors
    ));
}


    /* ---------------------------
       INSERT INTO CUSTOM TABLE
    ---------------------------- */
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'message'    => $message,
            'product_id' => $product_id,
        ),
        array('%s', '%s', '%s', '%s', '%d')
    );

    if ( ! $inserted ) {
        wp_send_json_error(array(
            'message' => 'Failed to save enquiry in database.'
        ));
    }

    /* ---------------------------
       SEND EMAIL (OPTIONAL)
    ---------------------------- */
    $to = 'ashishm@salttechno.com';
    $subject = 'New Product Enquiry';

   $body = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Product Enquiry</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:20px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:6px;overflow:hidden;">
                
                <!-- Header -->
                <tr>
                    <td style="background:#0d6efd;color:#ffffff;padding:20px;text-align:center;">
                        <h2 style="margin:0;">New Product Enquiry</h2>
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <td style="padding:20px;color:#333;">
                        <p><strong>Name:</strong> '.$name.'</p>
                        <p><strong>Email:</strong> '.$email.'</p>
                        <p><strong>Phone:</strong> '.$phone.'</p>
                        <p><strong>Product ID:</strong> '.$product_id.'</p>

                        <hr style="margin:20px 0;">

                        <p><strong>Message:</strong></p>
                        <p style="background:#f8f8f8;padding:10px;border-radius:4px;">'.$message.'</p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f1f1f1;padding:15px;text-align:center;font-size:12px;color:#777;">
                        © '.date('Y').' Salt Techno. All rights reserved.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>';

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: Salt Techno <ashishm@salttechno.com>',
        'Reply-To: ' . $name . ' <' . $email . '>'
    );

add_filter('wp_mail_content_type', function () {
    return 'text/html';
});

    wp_mail($to, $subject, $body, $headers);

    wp_send_json_success(array(
        'message' => 'Enquiry submitted successfully!'
    ));
}

//  Creating the Custom Table one time
function create_product_enquiry_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'product_enquiriesz';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(20) NOT NULL,
        message text NOT NULL,
        product_id int NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
add_action( 'after_setup_theme', 'create_product_enquiry_table' );


/**
 * Product Enquiries Admin Page with Quick Edit
 */



if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
// add_action('current_screen', function ($screen) {

//     if (
//         $screen->id === 'toplevel_page_product-enquiries' &&
//         isset($_POST['action']) &&
//         $_POST['action'] === 'delete'
//     ) {
//         wp_safe_redirect(admin_url('admin.php?page=product-enquiries&deleted=1'));
//         exit;
//     }
// });


/* =====================================================
   1. REGISTER ADMIN MENU
===================================================== */
add_action('admin_menu', function () {
    add_menu_page(
        'Product Enquiries',
        'Product Enquiries',
        'manage_options',
        'product-enquiries',
        'render_product_enquiry_page',
        'dashicons-email',
        25
    );
});

/* =====================================================
   2. LIST TABLE CLASS
===================================================== */
class Product_Enquiries_Table extends WP_List_Table {

    private $table_name;

    public function __construct() {
        global $wpdb;

        parent::__construct([
            'singular' => 'enquiry',
            'plural'   => 'enquiries',
            'ajax'     => false,
        ]);

        $this->table_name = $wpdb->prefix . 'product_enquiriesz';
    }

    /* ---------- Columns ---------- */
    public function get_columns() {
        return [
            'cb'         => '<input type="checkbox" />',
            'name'       => 'Name',
            'email'      => 'Email',
            'phone'      => 'Phone',
            'message'    => 'Message',
            'product'    => 'Product',
            'created_at' => 'Date',
        ];
    }

    public function get_sortable_columns() {
        return [
            'name'       => ['name', false],
            'email'      => ['email', false],
            'created_at' => ['created_at', true],
        ];
    }

    /* ---------- Checkbox ---------- */
  protected function column_cb($item) {
    return sprintf(
        '<input type="checkbox" name="id[]" value="%d" />',
        $item->id
    );
}


    /* ---------- Name column with row actions ---------- */
    protected function column_name($item) {

        $edit_url = admin_url('admin.php?page=product-enquiries&action=edit&id=' . $item->id);
        $view_url = admin_url('admin.php?page=product-enquiries&action=view&id=' . $item->id);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=product-enquiries&action=delete&id=' . $item->id),
            'delete_enquiry_' . $item->id
        );

        $actions = [
            'edit'   => '<a href="' . esc_url($edit_url) . '">Edit</a>',
            'view'   => '<a href="' . esc_url($view_url) . '">View</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" class="submitdelete">Delete</a>',
        ];

        return sprintf(
            '<strong>%s</strong> %s',
            esc_html($item->name),
            $this->row_actions($actions)
        );
    }

    protected function column_product($item) {
        return $item->product_id
            ? esc_html(get_the_title($item->product_id))
            : '—';
    }

    protected function column_default($item, $column_name) {
        return isset($item->$column_name)
            ? esc_html($item->$column_name)
            : '';
    }

    /* ---------- Bulk Actions ---------- */
   public function get_bulk_actions() {
    return [
        'delete' => 'Delete'
    ];
}

public function process_bulk_action() {

    if ($this->current_action() !== 'delete') {
        return;
    }

    if (
        ! isset($_POST['bulk_enquiries_nonce']) ||
        ! wp_verify_nonce($_POST['bulk_enquiries_nonce'], 'bulk-enquiries')
    ) {
        wp_die('Security check failed');
    }

    if (empty($_POST['id'])) {
        return;
    }

    global $wpdb;

    $ids = array_map('intval', (array) $_POST['id']);

    foreach ($ids as $id) {
        $wpdb->delete($this->table_name, ['id' => $id]);
    }

    // ✅ set flag for redirect
    add_filter('redirect_post_location', function ($location) {
        return add_query_arg('deleted', '1', $location);
    });
}





    /* ---------- Date Filter ---------- */
    protected function extra_tablenav($which) {
        if ($which === 'top') {
            global $wpdb;

            $dates = $wpdb->get_results("
                SELECT DISTINCT DATE(created_at) AS date
                FROM {$this->table_name}
                ORDER BY date DESC
            ");
            ?>
            <div class="alignleft actions">
                <select name="filter_date">
                    <option value="">All dates</option>
                    <?php foreach ($dates as $row): ?>
                        <option value="<?php echo esc_attr($row->date); ?>"
                            <?php selected($_GET['filter_date'] ?? '', $row->date); ?>>
                            <?php echo esc_html(date('F Y', strtotime($row->date))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button('Filter', '', 'filter_action', false); ?>
            </div>
            <?php
        }
    }

    /* ---------- Prepare Items ---------- */
    public function prepare_items() {
           global $wpdb;

    $this->process_bulk_action();

    $per_page     = 10;
    $current_page = $this->get_pagenum();
    $offset       = ($current_page - 1) * $per_page;

    $search      = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $filter_date = $_GET['filter_date'] ?? '';

    $allowed_orderby = ['name', 'email', 'created_at'];
    $orderby = in_array($_GET['orderby'] ?? '', $allowed_orderby)
        ? $_GET['orderby']
        : 'created_at';

    $order = ($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $where = 'WHERE 1=1';

    if ($search) {
        $where .= $wpdb->prepare(
            " AND (name LIKE %s OR email LIKE %s)",
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
        );
    }

    if ($filter_date) {
        $where .= $wpdb->prepare(
            " AND DATE(created_at) = %s",
            $filter_date
        );
    }

    /* 🔥 THIS IS THE MISSING PIECE 🔥 */
    $columns  = $this->get_columns();
    $hidden   = [];
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = [$columns, $hidden, $sortable];
    /* 🔥 END FIX 🔥 */

    $total_items = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$this->table_name} $where"
    );

    $this->items = $wpdb->get_results(
        "SELECT * FROM {$this->table_name}
         $where
         ORDER BY {$orderby} {$order}
         LIMIT {$per_page} OFFSET {$offset}"
    );

    $this->set_pagination_args([
        'total_items' => $total_items,
        'per_page'    => $per_page,
    ]);
}
}
/* =====================================================
   3. RENDER ADMIN PAGE
===================================================== */
if (isset($_GET['deleted'])) {
    echo '<div class="notice notice-success is-dismissible">
            <p>Selected enquiries deleted successfully.</p>
          </div>';
}

function render_product_enquiry_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'product_enquiriesz';

    $action = $_GET['action'] ?? '';
    $id     = intval($_GET['id'] ?? 0);

    /* ---------- EDIT ---------- */
if ($action === 'edit' && $id) {

    if (isset($_POST['update_enquiry'])) {

        $wpdb->update(
            $table_name,
            [
                'name'    => sanitize_text_field($_POST['name']),
                'email'   => sanitize_email($_POST['email']),
                'phone'   => sanitize_text_field($_POST['phone']),
                'message' => sanitize_textarea_field($_POST['message']),
            ],
            ['id' => $id]
        );

        echo '<div class="notice notice-success"><p>Enquiry updated successfully.</p></div>';
    }

    $row = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id=%d", $id)
    );
    ?>
    <div class="wrap">
        <h1>Edit Enquiry</h1>

        <form method="post">
            <table class="form-table">
                <tr>
                    <th>Name</th>
                    <td><input type="text" name="name" value="<?php echo esc_attr($row->name); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><input type="email" name="email" value="<?php echo esc_attr($row->email); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><input type="text" name="phone" value="<?php echo esc_attr($row->phone); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th>Message</th>
                    <td><textarea name="message" class="large-text"><?php echo esc_textarea($row->message); ?></textarea></td>
                </tr>
            </table>

            <p>
                <input type="submit" name="update_enquiry" class="button button-primary" value="Update">
                <a href="<?php echo admin_url('admin.php?page=product-enquiries'); ?>" class="button">Cancel</a>
            </p>
        </form>
    </div>
    <?php
    return;
}

    /* ---------- DELETE ---------- */
    if ($action === 'delete' && $id) {
        check_admin_referer('delete_enquiry_' . $id);
        $wpdb->delete($table_name, ['id' => $id]);
        echo '<div class="notice notice-success"><p>Enquiry deleted successfully.</p></div>';
    }

    /* ---------- VIEW ---------- */
    if ($action === 'view' && $id) {
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id=%d",
            $id
        ));
        ?>
        <div class="wrap">
            <h1>View Enquiry</h1>
            <table class="widefat striped">
                <tr><th>Name</th><td><?php echo esc_html($row->name); ?></td></tr>
                <tr><th>Email</th><td><?php echo esc_html($row->email); ?></td></tr>
                <tr><th>Phone</th><td><?php echo esc_html($row->phone); ?></td></tr>
                <tr><th>Message</th><td><?php echo esc_html($row->message); ?></td></tr>
                <tr><th>Date</th><td><?php echo esc_html($row->created_at); ?></td></tr>
            </table>
            <p>
                <a href="<?php echo admin_url('admin.php?page=product-enquiries'); ?>" class="button">← Back</a>
            </p>
        </div>
        <?php
        return;
    }

    /* ---------- LIST ---------- */
    $table = new Product_Enquiries_Table();
    $table->prepare_items();
    ?>
<div class="wrap">
    <?php
$export_url = add_query_arg(
    [
        'action'      => 'export_product_enquiries_csv',
        's'           => $_GET['s'] ?? '',
        'filter_date' => $_GET['filter_date'] ?? '',
    ],
    admin_url('admin-post.php')
);
?>
<div>
<a href="<?php echo esc_url($export_url); ?>" class="page-title-action">
    Export CSV
</a>
<div>
    <h1 class="wp-heading-inline">Product Enquiries</h1>



    <!-- SEARCH FORM (GET) -->
    <form method="get">
        <input type="hidden" name="page" value="product-enquiries" />
        <?php
        $table->search_box('Search Enquiries', 'search');
        ?>
    </form>

    <!-- TABLE + BULK ACTION FORM (POST) -->
    <form method="post">
        <input type="hidden" name="page" value="product-enquiries" />

        <?php
        wp_nonce_field('bulk-enquiries', 'bulk_enquiries_nonce');

        // Preserve search
        if (!empty($_GET['s'])) {
            echo '<input type="hidden" name="s" value="' . esc_attr($_GET['s']) . '">';
        }

        // Preserve date filter
        if (!empty($_GET['filter_date'])) {
            echo '<input type="hidden" name="filter_date" value="' . esc_attr($_GET['filter_date']) . '">';
        }

        $table->display();
        ?>
    </form>
</div>

    <?php
}

add_action('admin_post_export_product_enquiries_csv', 'export_product_enquiries_csv');

function export_product_enquiries_csv() {

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'product_enquiriesz';

    // Handle search & date filter (same as list table)
    $search      = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $filter_date = $_GET['filter_date'] ?? '';

    $where = 'WHERE 1=1';

    if ($search) {
        $where .= $wpdb->prepare(
            " AND (name LIKE %s OR email LIKE %s)",
            '%' . $wpdb->esc_like($search) . '%',
            '%' . $wpdb->esc_like($search) . '%'
        );
    }

    if ($filter_date) {
        $where .= $wpdb->prepare(
            " AND DATE(created_at) = %s",
            $filter_date
        );
    }

    $results = $wpdb->get_results(
        "SELECT * FROM {$table_name} $where ORDER BY created_at DESC",
        ARRAY_A
    );

    // CSV Headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=product-enquiries.csv');

    $output = fopen('php://output', 'w');

    // CSV Column Headings
    fputcsv($output, [
        'Name',
        'Email',
        'Phone',
        'Message',
        'Product',
        'Date'
    ]);

    foreach ($results as $row) {
        fputcsv($output, [
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['message'],
            $row['product_id'] ? get_the_title($row['product_id']) : '',
            $row['created_at'],
        ]);
    }

    fclose($output);
    exit;
}


//  Blog Purpose We are using the cpt

function register_custom_post_types() {

    // 1. Source CPT
    register_post_type('source', array(
        'labels' => array(
            'name'          => 'Sources',
            'singular_name' => 'Source',
        ),
        'public'        => true,
        'has_archive'   => true,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-database',
        'supports'      => array('title', 'editor', 'thumbnail'),
        'rewrite'       => array('slug' => 'source'),
    ));

    // 2. Books CPT
    register_post_type('books', array(
        'labels' => array(
            'name'          => 'Books',
            'singular_name' => 'Book',
        ),
        'public'        => true,
        'has_archive'   => true,
        'menu_position' => 6,
        'menu_icon'     => 'dashicons-book',
        'supports'      => array('title', 'editor', 'thumbnail'),
        'rewrite'       => array('slug' => 'books'),
    ));

    // 3. News CPT
    register_post_type('news', array(
        'labels' => array(
            'name'          => 'News',
            'singular_name' => 'News Item',
        ),
        'public'        => true,
        'has_archive'   => true,
        'menu_position' => 7,
        'menu_icon'     => 'dashicons-megaphone',
        'supports'      => array('title', 'editor', 'thumbnail'),
        'rewrite'       => array('slug' => 'news'),
    ));

}
add_action('init', 'register_custom_post_types');
