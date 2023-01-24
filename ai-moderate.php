<?php

function aipg_moderate() {
    global $wpdb;
    $processed  = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) c FROM $wpdb->posts WHERE post_content!=%s and post_excerpt!=%s and post_status=%s and post_type=%s", '', '', 'publish', 'post'));
    $unprocessed  = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) c FROM $wpdb->posts WHERE post_content=%s and post_excerpt!=%s and post_status=%s and post_type=%s", '', '', 'publish', 'post'));
  ?>
<div class="wrap">
<h2>Processed: <?php echo $processed; ?> - Unprocessed: <?php echo $unprocessed; ?> (of <?php echo $processed + $unprocessed; ?>)</h2>
<?php if ($unprocessed) { ?>
<table id="aipg_form" class="form-table">
    <tr valign="top">
        <th scope="row">ID</th>
        <td><input id="aipg_post_id" type="text" name="post_id" style="width: 100px;" disabled></td>
    </tr>
    <tr valign="top">
        <th scope="row">Title</th>
        <td><input id="aipg_post_title" type="text" name="post_title" style="width: 600px;"></td>
    </tr>
    <tr valign="top">
        <th scope="row">Content</th>
        <td><textarea rows="30" id="aipg_post_content" name="post_content" style="width: 600px;"></textarea></td>
    </tr>
</table>
<p class="submit">
<input id="aipg_submit_button" type="button" name="Submit" value="<?php _e('Save') ?>" />
<input id="aipg_reload_button" type="button" name="Reload" value="<?php _e('Next') ?>" />
<input id="aipg_delete_button" type="button" name="Delete" style="float:right" value="<?php _e('Delete') ?>" />
<img id="aipg_loader" style="height:20px;position:relative;top:4px;" src="<?php echo plugins_url('ai-postgen/loader.gif'); ?>" />
</p>
<script type="application/javascript">//<![CDATA[
jQuery(document).ready(function($) {
    let aipg_post_data = null;

    function aipg_toggle() {
        $('#aipg_loader').toggle();
    }

    function aipg_load_post() {
        $.ajax({
            type: 'GET',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'aipg_load_post',
            },
            success: function( data ) {
                aipg_post_data = data;
                if (data.id) {
                    $('#aipg_post_id').val(data.id);
                    $('#aipg_post_title').val(data.post_title);
                    $('#aipg_post_content').val(data.post_excerpt);
                    aipg_toggle();
                } else {
                    window.location.reload();
                }
            }
        })
    }

    $( "#aipg_submit_button" ).click(function() {
        aipg_toggle();
        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            'action' : 'aipg_save_post',
            'post_id' : $('#aipg_post_id').val(),
            'title' : $('#aipg_post_title').val(),
            'content' : $('#aipg_post_content').val(),
            '_ajax_nonce': aipg_post_data.ajax_nonce,
        }).then(() => aipg_load_post());
    });

    $( "#aipg_reload_button" ).click(function() {
        aipg_toggle();
        aipg_load_post();
    });

    $( "#aipg_delete_button" ).click(function() {
      aipg_toggle();
      $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            'action' : 'aipg_delete_post',
            'post_id' : $('#aipg_post_id').val(),
            '_ajax_nonce': aipg_post_data.ajax_nonce,
        }).then(() => aipg_load_post());
    });

    aipg_load_post();
});
//]]></script>
<?php } ?>
</div>
  <?php
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

