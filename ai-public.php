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
    ob_start();
    ?>
    <div id="aipg_content"></div>
    <script type="application/javascript">//<![CDATA[

async function *parseJsonStream(readableStream) {
    const regexp = new RegExp('({.*})', 'gms');
    for await (const line of readLines(readableStream.getReader())) {
        let trimmedLine = line.trim().replace(/,$/, '');
        trimmedLine = regexp.test(trimmedLine) ? trimmedLine.match(regexp)[0] : trimmedLine;

        if (trimmedLine !== '[]' && trimmedLine !== '\n') {
            try {
                yield JSON.parse(trimmedLine);
            } catch (e) {
            }
        }
    }
}

async function *readLines(reader) {
    const textDecoder = new TextDecoder();
    let partOfLine = '';
    for await (const chunk of readChunks(reader)) {
        const chunkText = textDecoder.decode(chunk);
        const chunkLines = chunkText.split('\n');
        if (chunkLines.length === 1) {
            partOfLine += chunkLines[0];
        } else if (chunkLines.length > 1) {
            yield partOfLine + chunkLines[0];
            for (let i=1; i < chunkLines.length - 1; i++) {
                yield chunkLines[i];
            }
            partOfLine = chunkLines[chunkLines.length - 1];
        }
    }
}

function readChunks(reader) {
    return {
        async* [Symbol.asyncIterator]() {
            let readResult = await reader.read();
            while (!readResult.done) {
                yield readResult.value;
                readResult = await reader.read();
            }
        },
    };
}

function aipg_store(content, model) {
    jQuery(function ($) {
        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            'action' : 'aipg_store_content',
            'post_id' : '<?php echo $post->ID; ?>',
            'title' : `<?php echo $post->post_title; ?>`,
            'content' : content,
            'model' : model,
            '_ajax_nonce': '<?php echo $ajax_nonce; ?>',
        });
    });    
}

function aipg_process() {
    fetch('<?php echo $link; ?>')
        .then(async (response) => {
            let content = '';
            for await (const chunk of this.parseJsonStream(response.body)) {
                let model = chunk.model || '';
                if (!Array.isArray(chunk) && !chunk.choices[0].finish_reason) {
                    content = (content + (chunk.choices[0].text || '')).trimStart();
                    document.getElementById("aipg_content").innerHTML = content.replace(/(\r\n|\r|\n)/g, '<br />');
                } else {
                    aipg_store(content, model);
                }
            }
        });
}

jQuery(document).ready(function($) {
    setTimeout(aipg_process, 100);
});
//]]></script>
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
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
