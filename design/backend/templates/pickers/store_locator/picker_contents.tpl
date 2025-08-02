{if $language_direction == "rtl"}
    {$direction = "right"}
{else}
    {$direction = "left"}
{/if}

{$form_id = "store_locations_form"}

{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');
    var display_type = '{$smarty.request.display|escape:javascript nofilter}';

    $.ceEvent('on', 'ce.formpost_store_locations_form', function(frm, elm) {
        var stores = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each(function() {
                var id = $(this).val();
                stores[id] = $('#store_' + id).text();
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), stores, 'a', {
                '{store_location_id}': '%id',
                '{store_name}': '%item'
            });
            {/literal}

            if (display_type !== 'radio') {
                $.ceNotification('show', {
                    type: 'N',
                    title: _.tr('notice'),
                    message: _.tr('text_items_added'),
                    message_state: 'I'
                });
            }

        }

        return false;
    });
}(Tygh, Tygh.$));
</script>
{/if}

<form id="{$form_id}"
      action="{$smarty.request.extra|fn_url}"
      method="post"
      name="store_locations_form"
      class="cm-ajax cm-form"
      data-ca-result-id="{$smarty.request.data_id}">
    <div class="items-container">
        {if $store_locations}
        <table class="table">
            <thead>
                <tr>
                    <th>{__("name")}</th>
                    <th>{__("select")}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $store_locations as $store}
                <tr>
                    <td id="store_{$store.store_location_id}">{$store.name}</td>
                    <td>
                        <input type="{$smarty.request.display}" class="cm-item" name="store_ids[]" value="{$store.store_location_id}" data-ca-picker-value="{$store.store_location_id}" data-ca-picker-text="{$store.name}" />
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        {else}
            <p class="no-items center">{__("no_data")}</p>
        {/if}
    </div>

    <div class="buttons-container buttons-container--hidden-cancel">
        {if $smarty.request.display == "radio"}
            {$but_close_text = __("choose")}
        {else}
            {$but_close_text = __("add")}
        {/if}
        {include file="buttons/add_close.tpl" is_js=true}
     </div>
</form>
