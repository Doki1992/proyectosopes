



setTimeout(callback, 200);

function callback()
{
	alert('200');
}

$.ajax({
			url: "worker.php",
			method: 'POST',
			success: function(response){
				alert(response);
			},
			error: function(error){
				alert(error.responseText);
			}
});
