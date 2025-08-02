{assign var="placeholder_id" value="{$ldelim}store_location_id{$rdelim}"}

{if $store_location_id != $placeholder_id}
    {* Отрисовываем реальный элемент *}
    {assign var="store_data" value=fn_get_store_location($store_location_id)}
    <tr id="{$holder}_{$store_location_id}"
        class="cm-js-item"
        data-ca-picker-value="{$store_location_id}">
        <td>
            <a href="{"store_locator.update?store_location_id=`$store_location_id`"|fn_url}">{$store_data.name}</a>
        </td>
        <td class="right">
            <a onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$store_location_id}', 'a'); return false;"
               class="icon-trash cm-tooltip hand"
               title="{__("remove")}"></a>
        </td>
    </tr>
{else}
    {* Клон для динамического добавления *}
    <tr id="{$holder}_{$placeholder_id}"
        class="cm-js-item cm-clone hidden"
        data-ca-picker-value="{$placeholder_id}">
        <td>{$ldelim}store_name{$rdelim}</td>
        <td class="right">
            <a onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$placeholder_id}', 'a'); return false;"
               class="icon-trash cm-tooltip hand"
               title="{__("remove")}"></a>
        </td>
    </tr>
{/if}