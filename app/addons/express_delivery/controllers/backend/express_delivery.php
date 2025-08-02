<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode === 'store_locator_picker') {
    $params = $_REQUEST;

    $params['status'] = 'A';
    $params['items_per_page'] = 0;
    
    // Ограничим только складами текущего вендора, если это не админ
    if (!fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    list($store_locations, $search) = fn_get_store_locations($params);

    Tygh::$app['view']->assign([
        'store_locations' => $store_locations,
        'input_name'      => $_REQUEST['checkbox_name'] ?? 'store_location_ids',
        'display'         => $_REQUEST['display'] ?? 'checkbox',
        'select_mode'     => $_REQUEST['select_mode'] ?? 'multiple',
        'data_id'         => $_REQUEST['data_id'] ?? 'store_locator_list',
    ]);

    Tygh::$app['view']->display('pickers/store_locator/picker_contents.tpl');
    exit;
}