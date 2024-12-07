<?php
/*
Plugin Name: Gearmade Product Display
Description: A plugin to display Gearmade products using a shortcode.
Version: 1.2
Author: Marwa Renda
*/

function gearmade_fetch_products() {
    $api_url = 'https://apiuat.gearmadebd.com/api/v1/products?per_page=10&page=1';
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Unable to retrieve products';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data)) {
        return 'Invalid API response';
    }

    return $data;
}

function gearmade_display_products() {
    $products = gearmade_fetch_products();

    if (is_string($products)) {
        return $products; // Return error message
    }

    if (!isset($products['products']) || empty($products['products'])) {
        return 'No products found.';
    }

    ob_start();

    echo '<div class="gearmade-products-container">';
    foreach ($products['products'] as $product) {
        echo '<div class="gearmade-product">';
        
        // Display product image
        if (!empty($product['images']) && isset($product['images'][0]['src'])) {
            echo '<img src="' . esc_url($product['images'][0]['src']) . '" alt="' . esc_attr($product['name']) . '" />';
        }

        // Display product name
        echo '<h2>' . esc_html($product['name']) . '</h2>';
        
        // Display product price
        if (isset($product['sale_price']) && $product['sale_price'] < $product['regular_price']) {
            echo '<p class="price">Sale: ' . esc_html($product['sale_price']) . ' BDT</p>';
            echo '<p class="regular-price"><del>' . esc_html($product['regular_price']) . ' BDT</del></p>';
        } else {
            echo '<p class="price">Price: ' . esc_html($product['regular_price']) . ' BDT</p>';
        }

        echo '</div>';
    }
    echo '</div>';

    return ob_get_clean();
}

add_shortcode('gearmade_products', 'gearmade_display_products');

function gearmade_enqueue_styles() {
    wp_enqueue_style('gearmade-styles', plugins_url('/style.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'gearmade_enqueue_styles');

function gearmade_admin_notice() {
    if (get_current_screen()->id != 'plugins') {
        return;
    }
    ?>
    <div class="notice notice-success is-dismissible">
        <p><strong>Gearmade Product Display</strong> plugin has been activated. Use the shortcode <code>[gearmade_products]</code> to display products on any page or post.</p>
    </div>
    <?php
}

add_action('admin_notices', 'gearmade_admin_notice');
