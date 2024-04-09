jQuery(document).ready( function($) {
    $('#currency-converter-form').on('submit', function() {
        plnAmount = '';
        submit = true;
        $('#error').hide();
        if( ! $('input[name=pln_amount]').val() ){
            submit = false;
        }else{
            plnAmount = $('input[name=pln_amount]').val();
        }
        if( submit ){
            $('input[name=eur_amount]').val('');
            $.ajax({
                url: window.wp_data.ajax_url,
                type: 'POST',
                data: {
                    'action': 'send_ajax',
                    'secure_custom_form_nonce' : $('input[name=secure_custom_form_nonce]').val(),
                    'pln_amount': plnAmount,
                },
                success: function (data) {
                    if( data.eur_amount ){
                        eur_amount = data.eur_amount;
                        $('input[name=eur_amount]').val( eur_amount.toFixed(2) );
                        $('#error').hide();
                    }else if( ! data.success ){
                        $('#error').html( data.data ).show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    $('#error').html('Error occurred. Please try again.').show();
                }
            })
        }
        return false;
    })
});