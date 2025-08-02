<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'calculate_cart_content_after_shipping_calculation',
    'get_products_post',
    'get_product_data_post',
);