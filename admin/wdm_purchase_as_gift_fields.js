jQuery(document).ready(function($) {
    $('#recipient_info_fields').hide();
    $('#purchase_as_gift').on('change', function() {
        if ($('#purchase_as_gift').is(":checked"))
        {
            $('#recipient_info_fields').show();
        }
        else {
            $('#recipient_info_fields').hide();
        }
    });
})