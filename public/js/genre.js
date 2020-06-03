$(document).ready(function() {

	$("a[name='play']").click(function() {

		let genre = $(this).parent().prev('td').find('div').text();

		let url = '/internalapi/genres/songs?genre=' + encodeURIComponent(genre);

		fetch(url)
			.then(
				function(response) {
					if (response.status !== 200) {
						console.log('Looks like there was a problem. Status Code: ' + response.status);
						return;
					}
					response.json().then(function(data) {
						display_jukebox(genre, data.songs);
					});
				}
			)
			.catch(function(err) {
				console.log('Fetch Error: ', err);
		});

	});

});
