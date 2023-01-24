<?php
function aipg_options() {
  ?>
<div class="wrap">
<h2>AI Postgen Options</h2>
<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<table class="form-table">
    <tr valign="top">
        <th scope="row">Plugin Enabled</th>
        <td><select name="aipg_enabled">
            <option value="1" <?php echo get_option('aipg_enabled') == "1" ? "selected" : ""; ?> >Yes</option>
            <option value="0" <?php echo get_option('aipg_enabled') == "0" ? "selected" : ""; ?> >No</option>
        </select></td>
    </tr>
    <tr valign="top">
        <th scope="row">API Url</th>
        <td><input type="text" name="aipg_api_url" style="width: 600px;" value="<?php echo get_option('aipg_api_url'); ?>"></td>
    </tr>
    <tr valign="top">
        <th scope="row">Query prefix</th>
        <td><input type="text" name="aipg_q_prefix" style="width: 600px;" value="<?php echo get_option('aipg_q_prefix'); ?>"></td>
    </tr>
    <tr valign="top">
        <th scope="row">Author ID</th>
        <td><input type="text" name="aipg_author_id" style="width: 50px;" value="<?php echo get_option('aipg_author_id'); ?>"></td>
    </tr>
</table>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="aipg_enabled,aipg_api_url,aipg_q_prefix,aipg_author_id" />

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
</p>
</form>
</div>
  <?php
}
