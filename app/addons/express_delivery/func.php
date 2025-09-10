<?php

use Tygh\Registry;

ini_set('display_errors', 0); // Отключаем вывод ошибок на экран
ini_set('log_errors', 1); // Включаем логирование
ini_set('error_log', 'express_delivery_error.log'); // Указываем путь к логу
error_reporting(E_ALL); // Записываем ВСЕ ошибки

function fn_express_delivery_calculate_cart_content_after_shipping_calculation(&$cart, &$auth, &$params, $lang_code)
{   
    if (empty($cart['product_groups'])) {
        return;
    }

    // Проверяем, находимся ли мы в админке на странице редактирования заказа
    $is_admin_order_edit = (defined('AREA') && AREA == 'A') && 
                          (!empty($_REQUEST['dispatch']) && 
                          ($_REQUEST['dispatch'] == 'order_management.update' || 
                           $_REQUEST['dispatch'] == 'order_management.place_order.save'));

    // Получаем все экспресс доставки по company_id
    $shippings = db_get_hash_array(
        "SELECT * FROM ?:shippings WHERE is_express_delivery = 'Y' AND status = 'A'",
        'company_id'
    );

    foreach ($cart['product_groups'] as $group_key => &$product_group) {
        // Пропускаем, если нет доставки
        if (empty($product_group['shippings'])) {
            continue;
        }

        $remove_shipping_ids = [];

        foreach ($product_group['products'] as $cart_id => $product) {
            $product_id = (int) $product['product_id'];

            // Получаем company_id товара
            if (empty($product['company_id'])) {
                $product['company_id'] = (int) db_get_field(
                    "SELECT company_id FROM ?:products WHERE product_id = ?i",
                    $product_id
                );
            }

            $company_id = $product['company_id'];
            $shipping = $shippings[$company_id] ?? null;

            if (empty($shipping['shipping_id'])) {
                continue;
            }

            $shipping_id = (int) $shipping['shipping_id'];
            $shipping_info = fn_get_shipping_info($shipping_id, $lang_code);

            if($shipping_info['express_hide_icon'] == 'Y') {
                unset($product_group['shippings'][$shipping_id]['image']);
            }
            
            // Получаем express категории
            $express_category_ids = !empty($shipping_info['express_categories'])
                ? array_map('intval', explode(',', $shipping_info['express_categories']))
                : [];

            if (empty($express_category_ids)) {
                $remove_shipping_ids[] = $shipping_id;
                continue;
            }

            $all_express_categories = fn_get_all_child_categories($express_category_ids);

            // Проверка категорий
            $product_category_ids = $product['category_ids'] ?? fn_get_product_category_ids($product_id);
            $in_categories = !empty($product_category_ids) &&
                             !empty(array_intersect($product_category_ids, $all_express_categories));

            // В админке при редактировании/сохранении заказа НЕ проверяем наличие на складе
            $store_location_match = true;
            if (!$is_admin_order_edit) {
                // Проверка складов (только для фронтенда)
                $express_store_location_ids = !empty($shipping_info['express_store_locations'])
                    ? array_map('intval', explode(',', $shipping_info['express_store_locations']))
                    : [];

                if (!empty($express_store_location_ids)) {
                    $product_store_ids = db_get_fields(
                        "SELECT warehouse_id FROM ?:warehouses_products_amount WHERE product_id = ?i AND amount > 0",
                        $product_id
                    );
                    $store_location_match = !empty(array_intersect($product_store_ids, $express_store_location_ids));
                }
            }

            // Если товар не удовлетворяет условиям — исключаем доставку
            if (!$in_categories || !$store_location_match) {
                $remove_shipping_ids[] = $shipping_id;
            }
        }

        // Удаляем все неподходящие доставки из группы
        foreach (array_unique($remove_shipping_ids) as $sid) {
            unset($product_group['shippings'][$sid]);
        }
    }
}

function fn_express_delivery_get_products_post(&$products, $params, $lang_code)
{
    // Получаем список всех экспресс доставок
    $shippings = db_get_hash_array(
        "SELECT * 
         FROM ?:shippings AS s
         WHERE s.is_express_delivery = 'Y' AND s.status = 'A'",
        'company_id'
    );

    foreach ($products as &$product) 
    {
        $product_id = (int) $product['product_id'];
        $company_id = $product['company_id'];
        $shipping_id = $shippings[$company_id]['shipping_id'] ?? 0;

        $product['express_delivery_available'] = false;
       
        if (!$shipping_id) {
            continue;
        }

        // Получаем данные доставки
        $shipping_info = fn_get_shipping_info($shipping_id, $lang_code);

        $express_category_ids = !empty($shipping_info['express_categories'])
            ? array_map('intval', explode(',', $shipping_info['express_categories']))
            : [];

        if (empty($express_category_ids)) {
            continue;
        }

        // Получаем вложенные категории
        $all_express_categories = fn_get_all_child_categories($express_category_ids);

         // Получаем разрешённые магазины
        $express_store_location_ids = !empty($shipping_info['express_store_locations'])
        ? array_map('intval', explode(',', $shipping_info['express_store_locations']))
        : [];

        $available_store_locations = [];
        if (!empty($express_store_location_ids)) {
            $available_store_locations = fn_get_store_locations([
                'store_location_id' => $express_store_location_ids,
            ]);
        }
        
        $store_location_match = false;

         // Если магазины указаны, проверим соответствие
         if (!empty($available_store_locations)) {
            $product_store_ids = db_get_fields(
                "SELECT warehouse_id FROM ?:warehouses_products_amount WHERE product_id = ?i AND amount > 0",
                $product['product_id']
            );

            $store_location_match = !empty(array_intersect($product_store_ids, $express_store_location_ids));
        }

        // Проверяем пересечение категорий товара
        $in_categories = !empty($product['category_ids']) &&
                         !empty(array_intersect($product['category_ids'], $all_express_categories));

        if ($in_categories && $store_location_match) {
            $product['express_delivery_available'] = true;
        }

        // Доп. информация (иконка, заголовок)
        if (!empty($shipping_info['shipping'])) {
            $product['express_delivery_shipping_title'] = $shipping_info['shipping'];
        }

        if (!empty($shipping_info['icon']['icon']['image_path'])) {
            $product['express_delivery_icon'] = $shipping_info['icon']['icon']['image_path'];
        }
       
    }
}

function fn_express_delivery_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    // Получаем список всех экспресс доставок
    $shippings = db_get_hash_array(
        "SELECT * 
            FROM ?:shippings AS s
            WHERE s.is_express_delivery = 'Y' AND s.status = 'A'",
        'company_id'
    );

    $product_id = (int) $product_data['product_id'];
    $company_id = $product_data['company_id'];
    $shipping_id = $shippings[$company_id]['shipping_id'] ?? 0;

    $product_data['express_delivery_available'] = false;
    
    if (!$shipping_id) {
        return;
    }

    // Получаем данные доставки
    $shipping_info = fn_get_shipping_info($shipping_id, $lang_code);

    $express_category_ids = !empty($shipping_info['express_categories'])
        ? array_map('intval', explode(',', $shipping_info['express_categories']))
        : [];

    if (empty($express_category_ids)) {
        return;
    }

    // Получаем вложенные категории
    $all_express_categories = fn_get_all_child_categories($express_category_ids);

        // Получаем разрешённые магазины
    $express_store_location_ids = !empty($shipping_info['express_store_locations'])
    ? array_map('intval', explode(',', $shipping_info['express_store_locations']))
    : [];

    $available_store_locations = [];
    if (!empty($express_store_location_ids)) {
        $available_store_locations = fn_get_store_locations([
            'store_location_id' => $express_store_location_ids,
        ]);
    }
    
    $store_location_match = false;

    // Если магазины указаны, проверим соответствие
    if (!empty($available_store_locations)) {
        $product_store_ids = db_get_fields(
            "SELECT warehouse_id FROM ?:warehouses_products_amount WHERE product_id = ?i AND amount > 0",
            $product_data['product_id']
        );

        $store_location_match = !empty(array_intersect($product_store_ids, $express_store_location_ids));
    }

    // Проверяем пересечение категорий товара
    $in_categories = !empty($product_data['category_ids']) &&
                        !empty(array_intersect($product_data['category_ids'], $all_express_categories));

    if ($in_categories && $store_location_match) {
        $product_data['express_delivery_available'] = true;
    }

    // Доп. информация (иконка, заголовок)
    if (!empty($shipping_info['shipping'])) {
        $product_data['express_delivery_shipping_title'] = $shipping_info['shipping'];
    }

    if (!empty($shipping_info['icon']['icon']['image_path'])) {
        $product_data['express_delivery_icon'] = $shipping_info['icon']['icon']['image_path'];
    }

    if(!empty($shipping_info['description'])) {
        $product_data['express_notification'] = $shipping_info['description'];
    }
}

function fn_get_all_child_categories(array $parent_category_ids)
{
    $all_category_ids = $parent_category_ids;
    do {
        $child_categories = db_get_fields(
            "SELECT category_id FROM ?:categories WHERE parent_id IN (?n)",
            $parent_category_ids
        );

        $new_category_ids = array_diff($child_categories, $all_category_ids);

        $all_category_ids = array_merge($all_category_ids, $new_category_ids);

        $parent_category_ids = $new_category_ids;

    } while (!empty($new_category_ids));

    return array_unique($all_category_ids);
}