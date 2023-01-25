<?php

function aipg_insert_script($query) {
    global $post;
    $q = get_option('aipg_q_prefix') ? get_option('aipg_q_prefix') . ' ' . $query : $query;
    $link = sprintf(
        '%s/?q=%s&userid=%s',
        get_option('aipg_api_url'),
        urlencode($q),
        hash('sha256', $_SERVER['REMOTE_ADDR'])
    );
    $ajax_nonce = wp_create_nonce( 'aipg_store_content' );

    return strtr(
        file_get_contents(AIPG_PLUGIN_DIR.'/templates/ai-public.html'),
        [
            '{{ admin_url }}' => admin_url('admin-ajax.php'),
            '{{ post_id }}' => $post->ID,
            '{{ post_title }}' => $post->post_title,
            '{{ ajax_nonce }}' => $ajax_nonce,
            '{{ link }}' => $link,
        ]
    );    
}

function aipg_content_filter($content){  
    global $post;
    $ret = $content;
    if (get_option('aipg_enabled') && is_single() && !$post->post_content && !$post->post_excerpt) {
        $ret = aipg_insert_script($post->post_title) . $content;
    }
    return $ret;
}
 
function aipg_store_content_callback() {
    $post_id = $_POST['post_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $model = $_POST['model'];

    check_ajax_referer( 'aipg_store_content' );
    if (is_numeric($post_id) && $model === 'text-davinci-003' && ($post = get_post($post_id)) && ($post->post_title === $title)) {
        
        $parts = preg_split('/\r?\n/', $content, 2);
        if (count($parts) > 1 && strlen($parts[0]) && (strlen($parts[0]) < 10 || ucfirst($parts[0]) !== $parts[0])) {
            $content = trim($parts[1]);
        }

        $args = array(
          'ID'            => $post_id,
          'post_excerpt'  => trim(wp_kses_post($content)),
        );
        wp_update_post($args);
    }
    wp_die();
}

add_filter('the_content', 'aipg_content_filter', 10);
add_action( 'wp_ajax_aipg_store_content', 'aipg_store_content_callback' );
add_action( 'wp_ajax_nopriv_aipg_store_content', 'aipg_store_content_callback' );
