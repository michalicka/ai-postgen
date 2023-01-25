<?php
function aipg_options() {
    echo strtr(
        file_get_contents(AIPG_PLUGIN_DIR.'/templates/ai-options.html'),
        [
            '{{ _wpnonce }}' => wp_create_nonce('update-options'),
            '{{ aipg_enabled_1 }}' => get_option('aipg_enabled') == "1" ? "selected" : "",
            '{{ aipg_enabled_0 }}' => get_option('aipg_enabled') == "0" ? "selected" : "",
            '{{ aipg_api_url }}' => get_option('aipg_api_url'),
            '{{ aipg_q_prefix }}' => get_option('aipg_q_prefix'),
            '{{ aipg_author_id }}' => get_option('aipg_author_id'),
            '{{ submit_label }}' => translate('Save Changes'),
        ]
    );
}
