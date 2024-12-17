<?php

/*
2. Adding a Custom Field to WooCommerce Products
Objective: Introduce a new custom field for WooCommerce products.
Details:
The custom field should be something relevant for WooCommerce products, like "Promotional Tag" or "E
xtra Discount".
Ensure this field is visible and editable in the WooCommerce product edit screen.
*/

// Добавление кастомного поля в редактор продукта
add_action('woocommerce_product_options_general_product_data', 'add_custom_field_to_products');
function add_custom_field_to_products() {
    woocommerce_wp_text_input(array(
        'id'          => '_custom_promotional_tag', // ID поля
        'label'       => __('Promotional Tag', 'woocommerce'), // Название
        'description' => __('Add a custom promotional tag for the product.', 'woocommerce'), // Описание
        'desc_tip'    => 'true', // Показ подсказки
    ));
}

// Сохранение значения кастомного поля
add_action('woocommerce_process_product_meta', 'save_custom_field_to_products');
function save_custom_field_to_products($post_id) {
    $custom_field_value = isset($_POST['_custom_promotional_tag']) ? sanitize_text_field($_POST['_custom_promotional_tag']) : '';
    update_post_meta($post_id, '_custom_promotional_tag', $custom_field_value);
}

// Отображение кастомного поля на странице продукта (например, на витрине)
add_action('woocommerce_single_product_summary', 'display_custom_field_on_product_page', 25);
function display_custom_field_on_product_page() {
    global $product;
    $custom_field_value = get_post_meta($product->get_id(), '_custom_promotional_tag', true);
    if ($custom_field_value) {
        echo '<p class="custom-promotional-tag">' . esc_html($custom_field_value) . '</p>';
    }
}