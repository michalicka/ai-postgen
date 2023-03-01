# Wordpres AI post generator

A Wordpress plugin which helps you to generate post content for empty posts.

Could be used with this [openai-client](https://github.com/michalicka/openai-client)

## Installation

1. Download and unpack code repository to your Wordpress's `wp-content/plugins/ai-postgen/` folder.
2. Enable plugin from admin's Wordpress **Plugins** page.
3. Open **Options** section in admin menu and AI Postgen page to configure plugin.
4. Configure:
    - Enable plugin
    - Enter API endpoint
    - Define query prefix (optional) which helps to generate content
    - Define WP user ID to assign authorship (use 1 when only admin exists)

## Usage 

1. Create a post with **Title** only
2. Visit the post and:
    - content will be generated automatically
    - stored as post excerpt
3. Open admin Tools > **AI Moderate** to:
    - correct generated text
    - publish it as post content
    - or delete post when not good
