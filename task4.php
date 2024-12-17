<?php
/*
Bulk Update Feature
Objective: Enable bulk updating of the custom field for selected products.
Details:
Add buttons or actions for ’Update All’, ’Update Selected’, and ’Update Single’ functionalities.
When a user applies a bulk action, the custom field should be updated for all chosen products.
*/

add_filter('bulk_actions-edit-product', 'add_bulk_update_actions');
function add_bulk_update_actions($actions) {
    $actions['update_all'] = __('Update All', 'woocommerce');
    $actions['update_selected'] = __('Update Selected', 'woocommerce');
    $actions['update_single'] = __('Update Single', 'woocommerce');
    return $actions;
}

add_action('admin_footer', 'add_bulk_update_modal');
function add_bulk_update_modal() {
    ?>
    <script type="text/javascript">
jQuery(document).ready(function($) {
    var currentAction = null;

    // Автовыбор всех чекбоксов для "Update All"
    $('select[name="action"], select[name="action2"]').on('change', function() {
        var selectedAction = $(this).val();

        if (selectedAction === 'update_all') {
            $('input[id^="cb-select-"]').prop('checked', true);
            currentAction = 'update_all';
        } 
        else if (selectedAction === 'update_selected') {
            currentAction = 'update_selected';
        }
        else if (selectedAction === 'update_single') {
            $('input[id^="cb-select-"]').prop('checked', false);
            currentAction = 'update_single';
        }
        else {
            currentAction = null;
        }
    });

    // Логика для update_single
    $(document).on('change', 'input[id^="cb-select-"]', function() {
        if (currentAction === 'update_single') {
            if ($(this).is(':checked')) {
                $('input[id^="cb-select-"]').not(this).prop('checked', false);
            } else {
                $('input[id^="cb-select-"]').prop('checked', false);
            }
        }
    });

    // Открытие модального окна
    $('#doaction, #doaction2').on('click', function(e) {
        if (currentAction === 'update_all' || currentAction === 'update_selected' || currentAction === 'update_single') {
            e.preventDefault();

            if ($('input[id^="cb-select-"]:checked').length === 0) {
                alert('Пожалуйста, выберите хотя бы один товар.');
                return;
            }

            $('#bulk-update-modal').fadeIn();
        }
    });

    // Закрытие модального окна
    $('#bulk-update-modal .close-modal').on('click', function() {
        $('#bulk-update-modal').fadeOut();
    });

    // Применение изменений
    $('#apply-changes').on('click', function() {
        var field = $('#custom-field').val();
        var value = $('#custom-value').val();
        var productIds = [];

        // Получаем ID всех выбранных товаров
        $('input[id^="cb-select-"]:checked').each(function() {
            productIds.push($(this).val());
        });

        if (!value) {
            alert('Введите значение для изменения.');
            return;
        }

        // Отправка AJAX-запроса
        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            method: 'POST',
            data: {
                action: 'bulk_update_products',
                product_ids: productIds,
                field: field,
                value: value
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Ошибка: ' + response.data.message);
                }
            },
            error: function() {
                alert('Ошибка выполнения запроса.');
            }
        });

        $('#bulk-update-modal').fadeOut();
    });
});
    </script>

    <div id="bulk-update-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); width:50%; background:white; border:1px solid #ccc; padding:20px; z-index:10000; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <h2>Изменить выбранные товары</h2>
        <button class="close-modal" style="position:absolute; top:10px; right:10px;">&times;</button>
        
        <form id="bulk-update-form">
            <div>
                <label for="custom-field">Изменить поле:</label>
                <select id="custom-field" name="custom-field">
                    <option value="price">Цена</option>
                </select>
            </div>
            <div>
                <label for="custom-value">Новое значение:</label>
                <input type="text" id="custom-value" name="custom-value" required>
            </div>
            <button type="button" id="apply-changes">Применить</button>
        </form>
    </div>
    <?php
}

add_action('wp_ajax_bulk_update_products', 'bulk_update_products');
function bulk_update_products() {
    // Проверяем права пользователя
    if (!current_user_can('edit_products')) {
        wp_send_json_error(['message' => 'Недостаточно прав']);
    }

    // Получаем данные из AJAX-запроса
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];
    $field = sanitize_text_field($_POST['field']);
    $value = sanitize_text_field($_POST['value']);

    if (empty($product_ids) || empty($field) || empty($value)) {
        wp_send_json_error(['message' => 'Неверные данные']);
    }

    // Обновляем каждое поле у выбранных товаров
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);

        if (!$product) {
            continue;
        }

        // Применяем обновления
        switch ($field) {
            case 'price':
                $product->set_price($value);
                $product->set_regular_price($value);
                break;
        }
        $product->save();
    }

    wp_send_json_success(['message' => 'Обновление завершено']);

    
}











