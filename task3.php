<?php
/*
3. Building the Admin Interface
Objective: Develop a user interface within the WordPress admin for the plugin.
Details:
The interface should list WooCommerce products and display the new custom field for each product.
Implement filtering options to allow users to find products based on criteria like category, price, or stock s
tatus.
Each product row should have a checkbox to select that product.
*/

// Создание страницы меню в панели администратора
add_action('admin_menu', 'create_admin_interface');
function create_admin_interface() {
    add_submenu_page(
        'woocommerce', // Родительское меню (WooCommerce)
        __('Product Manager', 'woocommerce'), // Заголовок страницы
        __('Product Manager', 'woocommerce'), // Заголовок меню
        'manage_woocommerce', // Возможность
        'product-manager', // Слаг меню
        'render_product_manager_page' // Функция обратного вызова
    );
}

// Отображение административного интерфейса
function render_product_manager_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Product Manager', 'woocommerce'); ?></h1>
        
        <!-- Форма фильтрации -->
        <form method="get">
            <input type="hidden" name="page" value="product-manager" />
            <label for="category"><?php _e('Category:', 'woocommerce'); ?></label>
            <?php wp_dropdown_categories(array(
                'taxonomy' => 'product_cat',
                'name' => 'category',
                'show_option_all' => __('All Categories', 'woocommerce'),
                'selected' => isset($_GET['category']) ? $_GET['category'] : '',
            )); ?>
            
            <label for="min_price"><?php _e('Min Price:', 'woocommerce'); ?></label>
            <input type="number" name="min_price" value="<?php echo isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : ''; ?>" />
            
            <label for="max_price"><?php _e('Max Price:', 'woocommerce'); ?></label>
            <input type="number" name="max_price" value="<?php echo isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : ''; ?>" />
            
            <label for="stock_status"><?php _e('Stock Status:', 'woocommerce'); ?></label>
            <select name="stock_status">
                <option value=""><?php _e('All', 'woocommerce'); ?></option>
                <option value="instock" <?php selected('instock', isset($_GET['stock_status']) ? $_GET['stock_status'] : ''); ?>><?php _e('In Stock', 'woocommerce'); ?></option>
                <option value="outofstock" <?php selected('outofstock', isset($_GET['stock_status']) ? $_GET['stock_status'] : ''); ?>><?php _e('Out of Stock', 'woocommerce'); ?></option>
            </select>
            
            <button type="submit" class="button button-primary"><?php _e('Filter', 'woocommerce'); ?></button>
        </form>
        
        <!-- Таблица товаров -->
        <form method="post">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" id="select-all" /></th>
                        <th><?php _e('Product', 'woocommerce'); ?></th>
                        <th><?php _e('Price', 'woocommerce'); ?></th>
                        <th><?php _e('Stock', 'woocommerce'); ?></th>
                        <th><?php _e('Category', 'woocommerce'); ?></th>
                        <th><?php _e('Promotional Tag', 'woocommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Получение товаров с учетом фильтров
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                        'post_status' => 'publish',
                    );

                    // Применение фильтров
                    if (!empty($_GET['category'])) {
                        $args['tax_query'][] = array(
                            'taxonomy' => 'product_cat',
                            'field' => 'term_id',
                            'terms' => $_GET['category'],
                        );
                    }
                    if (!empty($_GET['min_price']) || !empty($_GET['max_price'])) {
                        $args['meta_query'][] = array(
                            'key' => '_price',
                            'value' => array((int) $_GET['min_price'], (int) $_GET['max_price']),
                            'type' => 'NUMERIC',
                            'compare' => 'BETWEEN',
                        );
                    }
                    if (!empty($_GET['stock_status'])) {
                        $args['meta_query'][] = array(
                            'key' => '_stock_status',
                            'value' => $_GET['stock_status'],
                        );
                    }

                    $query = new WP_Query($args);

                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();
                            $product = wc_get_product(get_the_ID());
                            $categories = wp_get_post_terms(get_the_ID(), 'product_cat', array('fields' => 'names'));
                            $promotional_tag = get_post_meta(get_the_ID(), '_custom_promotional_tag', true);
                            ?>
                            <tr>
                                <th class="check-column"><input type="checkbox" name="selected_products[]" value="<?php echo get_the_ID(); ?>" /></th>
                                <td><?php the_title(); ?></td>
                                <td><?php echo wc_price($product->get_price()); ?></td>
                                <td><?php echo $product->get_stock_status(); ?></td>
                                <td><?php echo implode(', ', $categories); ?></td>
                                <td><?php echo esc_html($promotional_tag); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="6">' . __('No products found.', 'woocommerce') . '</td></tr>';
                    }
                    wp_reset_postdata();
                    ?>
                </tbody>
            </table>
            <button type="submit" class="button button-secondary"><?php _e('Apply Action', 'woocommerce'); ?></button>
        </form>
    </div>
    <?php
}