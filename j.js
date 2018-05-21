



setInterval(callback, 500);

function callback()
{
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
}


