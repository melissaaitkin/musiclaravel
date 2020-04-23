$(document).ready(function() {

	$("#album").change(function() {

		var url = '/api/song?album=' + encodeURIComponent($('#album').val());

		fetch(url, {
				headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    },
		    })
			.then(
				function(response) {
					if (response.status !== 200) {
						console.log('Looks like there was a problem. Status Code: ' + response.status);
						return;
					}
					response.json().then(function(data) {
						var songs = '<ol id="artist-songs">';
						$.each(data, function(k, v) {
							songs += '<li>' + v.title + '</li>';
						});
						songs += '</ol>';
						$("#artist-songs").replaceWith(songs);
					});
				}
			)
		  	.catch(function(err) {
		    	console.log('Fetch Error: ', err);
		});

  	});

});