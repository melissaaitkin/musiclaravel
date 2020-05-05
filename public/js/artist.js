$(document).ready(function() {

	$("#album").change(function() {

		var url = '/api/songs?album=' + encodeURIComponent($('#album').val());

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
						var ol = '<ol id="artist-songs">';
						$.each(data.songs, function(k, song) {
							ol += '<li><a href="/song/' + song.id + '">' + song.title + '</a></li>';
						});
						ol += '</ol>';
						$("#artist-songs").replaceWith(ol);
					});
				}
			)
		  	.catch(function(err) {
		    	console.log('Fetch Error: ', err);
		});

  	});

});