$(document).ready(function() {

	var base_url = APP_URL + '/song/play/';

	$("a[name='play']").click(function() {

		let playlist = $(this).parent().prev('td').find('div').text();

		let url = '/api/playlists/songs?playlist=' + encodeURIComponent(playlist);

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
						display_jukebox(playlist, data.songs);
					});
				}
			)
			.catch(function(err) {
				console.log('Fetch Error: ', err);
		});

	});

});
