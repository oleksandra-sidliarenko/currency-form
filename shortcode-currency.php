<?php
/*
 * Plugin Name: Currency shortcode
 */
class CurrencyShortcode {
     
    public function __construct()
    {
        $this->access_key = 'API_KEY'; 
        add_action( 'wp_enqueue_scripts', array( $this,'shortcode_scripts' ) );
        add_shortcode( 'Currency', array( $this, 'currency_form' ) );
        add_action( 'wp_ajax_send_ajax', array( $this, 'send_ajax' ) );
        add_action( 'wp_ajax_nopriv_send_ajax', array( $this, 'send_ajax' ) );
    }

    public function shortcode_scripts() {
        wp_enqueue_script( 'main', plugins_url( 'assets/js/main.js', __FILE__ ), array( 'jquery' ), '1.0', true );
        wp_localize_script(
            'main',
            'wp_data',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ), 
                'nonce' => wp_create_nonce('ajax_post_validation') 
            )
        );
        wp_enqueue_style( 'bootstrap', plugins_url( 'assets/css/bootstrap.css', __FILE__ ), null, '1.0' );
        wp_enqueue_style( 'main-style', plugins_url( 'assets/css/style.css', __FILE__ ), null, '1.0' );
    }
     
    public function currency_form()
    {
        ob_start();
        ?>
        <div id="currency-converter">
            <form id="currency-converter-form" action="" method="POST">
                <?php wp_nonce_field('secure_custom_form_action', 'secure_custom_form_nonce'); ?>
                <p>
                    <label for="pln_amount">PLN:</label>
                    <input type="number" id="pln_amount" name="pln_amount" step="0.01" required>
                </p>
                <p>
                    <label for="eur_amount">EUR:</label>
                    <input type="text" id="eur_amount" name="eur_amount" readonly>
                
                </p>
                <p>
                    <button type="submit">Convert</button>
                </p>
            </form>
            <div id="error"></div>
        </div>
        <?php
        $form = ob_get_clean();
        ob_clean();
        return $form;
    }

    public function send_ajax()
    {
        if ( isset( $_POST['secure_custom_form_nonce'] ) && wp_verify_nonce( $_POST['secure_custom_form_nonce'], 'secure_custom_form_action' ) ) {
            if ( isset( $_POST['pln_amount'] ) && ! empty( $_POST['pln_amount'] ) ) {
                // Get PLN amount from POST data
                $pln_amount = floatval( $_POST['pln_amount'] );    
                $url = 'http://api.exchangeratesapi.io/v1/convert/?from=PLN&to=EUR&access_key=' . $this->access_key . '&amount =' . $pln_amount;
                $response = wp_remote_get( $url );   
                if ( ! is_wp_error( $response ) && $response['response']['message'] == 200 ) {
                    // Convert response to JSON
                    $data = json_decode( $response['body'], true );
                    // Check if conversion result is available
                    if ( isset( $data['result'] ) ) {
                        // Send JSON response
                        wp_send_json_success( array( 'eur_amount' => $data['result'] ) );
                    }
                }elseif( ! empty( $response['response']['message'] ) ){
                    wp_send_json_error( __( $response['response']['message'], 'shortcode-currency' ) );
                }
            }        
        }else{
            //  Nonce is invalid, possible CSRF attack
            wp_send_json_error( __( 'Error: CSRF validation failed. Please try again.', 'shortcode-currency' ) );
        }
            // Send JSON response in case of error or missing data
            wp_send_json_error( __( 'Error converting currency. Please try again.', 'shortcode-currency' ) );
    }
}
$CurrencyShortcode = new CurrencyShortcode();
?>