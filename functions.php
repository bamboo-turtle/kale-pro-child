<?php
add_action( 'wp_enqueue_scripts', 'kale_child_enqueue_styles' );
function kale_child_enqueue_styles() {
    
    $parent_style = 'kale-style';
    $deps = array('bootstrap', 'bootstrap-select', 'font-awesome', 'owl-carousel');
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' , $deps);
    
    wp_enqueue_style( 'kale-style-child', get_stylesheet_directory_uri() . '/style.css', array( $parent_style ), wp_get_theme()->get('Version') );
}

function kale_get_option($key){
    global $kale_defaults;
    
    $parent_theme = get_template_directory();
    $parent_theme_slug = basename($parent_theme);
    $parent_theme_mods = get_option( "theme_mods_{$parent_theme_slug}");
    
    $value = '';
    $child_value = get_theme_mod($key);
    if(!empty($child_value)){
        $value = $child_value;
    }
    else if (!empty($parent_theme_mods) && isset($parent_theme_mods[$key])) {
        $value = $parent_theme_mods[$key];
    } else if (array_key_exists($key, $kale_defaults)) 
        $value = get_theme_mod($key, $kale_defaults[$key]); 
    return $value;
}

function zero_waste_modify_product_price($product, $price_html) {
  $display_price_quantity = $product->get_attribute('display_price_quantity');
  if ($display_price_quantity && $display_price_quantity > 1) {
    if ($product->get_type() == 'simple' && $product->get_price() != '' && !$product->is_on_sale()) {
      $price_html = wc_price( wc_get_price_to_display( $product ) * $display_price_quantity );
      $price_html .= ' <small class="woocommerce-price-suffix">per '. $display_price_quantity . 'g</small>';
    }
  }
  return $price_html;
}

// Modify price of products sold by weight on product page
function zero_waste_woocommerce_get_price_html($price_html, $product) {
  return zero_waste_modify_product_price($product, $price_html);
}

add_filter( 'woocommerce_get_price_html', 'zero_waste_woocommerce_get_price_html', 10, 2 );

// Modify price of products sold by weight on cart page
function zero_waste_woocommerce_cart_item_price($price_html, $cart_item, $cart_item_key) {
  $product = wc_get_product($cart_item['product_id']);
  return zero_waste_modify_product_price($product, $price_html);
}

add_filter( 'woocommerce_cart_item_price', 'zero_waste_woocommerce_cart_item_price', 10, 3 );

// Customise quantity selector
function zero_waste_woocommerce_quantity_input_args( $args, $product ) {
  $display_price_quantity = $product->get_attribute('display_price_quantity');
  if ($display_price_quantity && $display_price_quantity > 1) {
    if ( is_singular( 'product' ) ) {
      $args['input_value'] 	= 50;	// Starting value (we only want to affect product pages, not cart)
    }
    $args['min_value'] = 50;   	// Minimum value
    $args['step'] = 50;    // Quantity steps
  }
	return $args;
}

add_filter( 'woocommerce_quantity_input_args', 'zero_waste_woocommerce_quantity_input_args', 10, 2 ); 
?>
