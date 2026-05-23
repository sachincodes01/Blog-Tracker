jQuery(document).ready(function ($) {

    $('#but-rescan').on('click', function () {

        const button = $(this);

        button.prop('disabled', true).text('Scanning...');

        $.post(but_ajax.ajax_url, {
            action: 'but_rescan',
            nonce: but_ajax.nonce
        }, function (response) {

            alert(response.data);

            location.reload();
        });
    });
});