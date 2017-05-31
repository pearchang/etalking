$(document).on('ready', function() {
	if ($('.js-signature').length) {
		$('.js-signature').jqSignature();
	}
});

function clearCanvas() {
	// $('#signature').html('<p><em>Your signature will appear here when you click "Save Signature"</em></p>');
	$('.js-signature').jqSignature('clearCanvas');
	// $('#saveBtn').attr('disabled', true);
}

function saveSignature() {
	$('#signature').empty();
	var dataUrl = $('.js-signature').jqSignature('getDataURL');
	var img = $('<img>').attr('src', dataUrl);
	$('#signature').append($('<p>').text("Here's your signature:"));
	$('#signature').append(img);
}

$('.js-signature').on('jq.signature.changed', function() {
	$('#saveBtn').attr('disabled', false);
});