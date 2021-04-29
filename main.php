add_action('woocommerce_after_checkout_validation', 'products_purchasable_once', 10, 2);
function products_purchasable_once( $data, $errors ) {
    if( ! is_user_logged_in() )
        return;
    $user = wp_get_current_user();

    // get current cart items
    global $woocommerce;
    $cartItems = $woocommerce->cart->get_cart();

    // get product ids
    $targeted_products = get_posts([
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
    ]);
    $notPurchasable = [];

    // check if product is in cart and user has purchased it
    foreach($cartItems as $item => $values) {
        if (in_array($values['data']->get_id(), $targeted_products)) {
            if (wc_customer_bought_product( $user->user_email, $user->ID, $values['data']->get_id() )) {
                $notPurchasable[] = get_the_title($values['data']->get_id());
            }
        }
    }

    // if purchased add error
    if (count($notPurchasable) !== 0) {
        foreach ($notPurchasable as $product) {
            $errorText = 'You cannot buy the product (' . $product . ') again.';
            $errors->add('validation',  $errorText);
        }
    }
}