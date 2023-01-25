<?php

function aipg_moderate() {
    global $wpdb;
    $processed  = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) c FROM $wpdb->posts WHERE post_content!=%s and post_excerpt!=%s and post_status=%s and post_type=%s", '', '', 'publish', 'post'));
    $unprocessed  = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) c FROM $wpdb->posts WHERE post_content=%s and post_excerpt!=%s and post_status=%s and post_type=%s", '', '', 'publish', 'post'));

    $form = $unprocessed ? strtr(
        file_get_contents(AIPG_PLUGIN_DIR.'/templates/ai-moderate-form.html'),
        [
            '{{ processed }}' => $processed,
            '{{ unprocessed }}' => $unprocessed,
            '{{ total }}' => $processed + $unprocessed,
            
            '{{ save_label }}' => translate('Save'),
            '{{ next_label }}' => translate('Next'),
            '{{ delete_label }}' => translate('Delete'),
            '{{ loader }}' => plugins_url('ai-postgen/images/loader.gif'),
            '{{ admin_url }}' => admin_url('admin-ajax.php'),
        ]
    ) : '';

    echo strtr(
        file_get_contents(AIPG_PLUGIN_DIR.'/templates/ai-moderate.html'),
        [
            '{{ processed }}' => $processed,
            '{{ unprocessed }}' => $unprocessed,
            '{{ total }}' => $processed + $unprocessed,
            '{{ form }}' => $form,
        ]
    );
}

function aipg_load_post_callback() {
    global $wpdb;
    if (current_user_can('administrator')) {
        $post = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_title, post_excerpt FROM $wpdb->posts WHERE post_content=%s and post_excerpt!=%s and post_status=%s and post_type=%s order by rand() limit 1", '', '', 'publish', 'post'));
        wp_send_json([
            'id' => $post->ID,
            'post_title' => $post->post_title,
            'post_excerpt' => $post->post_excerpt,
            'post_excerpt' => $post->post_excerpt,
            'ajax_nonce' => wp_create_nonce( 'aipg_moderate_post' ),
        ]);
    }
    wp_die();
}

function aipg_save_post_callback() {
    $post_id = $_POST['post_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    check_ajax_referer( 'aipg_moderate_post' );
    if (is_numeric($post_id) && ($post = get_post($post_id)) && current_user_can('administrator')) {
        $args = array(
          'ID'            => $post_id,
          'post_author'   => get_option('aipg_author_id'),
          'post_title'    => trim($title),
          'post_content'  => trim(wp_kses_post($content)),
          'post_excerpt'  => trim(wp_kses_data(preg_split('/\r?\n/', $content, 2)[0])),
        );
        wp_update_post($args);
    }
    wp_die();
}

function aipg_delete_post_callback() {
    $post_id = $_POST['post_id'];

    check_ajax_referer( 'aipg_moderate_post' );
    if (is_numeric($post_id) && ($post = get_post($post_id)) && current_user_can('administrator')) {
        $args = array(
          'ID'            => $post_id,
          'post_author'   => get_option('aipg_author_id'),
          'post_status'   => 'private',
        );
        wp_update_post($args);
    }
    wp_die();
}

add_action( 'wp_ajax_aipg_load_post', 'aipg_load_post_callback' );
add_action( 'wp_ajax_aipg_save_post', 'aipg_save_post_callback' );
add_action( 'wp_ajax_aipg_delete_post', 'aipg_delete_post_callback' );

