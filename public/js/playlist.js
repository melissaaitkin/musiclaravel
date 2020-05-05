$(document).ready(function() {

	var base_url = APP_URL + '/song/play/';

	$("a[name='play']").click(function() {

		let playlist = $(this).parent().prev('td').find('div').text();

		let url = '/api/playlist/songs?playlist=' + encodeURIComponent(playlist);

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
						songs = Object.keys(data.data).map((key) => [key, data.data[key]]);
						// display_playlist(title, base_url, songs);
						display_playlist(playlist, songs);
					});
				}
			)
			.catch(function(err) {
				console.log('Fetch Error: ', err);
		});

	});

	function display_playlist(playlist, songs) {
		let playlist_form = '<div class="audio">';
		playlist_form += '<figure>';
		playlist_form += '<figcaption id="current-song">' + songs[0][1] + '</figcaption>';
		playlist_form += '<audio controls src="' + base_url + songs[0][0] + '">Your browser does not support the<code>audio</code> element.</audio>';
		playlist_form += '</figure>';
		
		playlist_form += '<div>';
		for (i = 0; i < songs.length; i++) {
			playlist_form += '<span id="song-' + songs[i][0] + '">' + songs[i][1] + '</span><br>';
		}
		playlist_form += '</div>';
		playlist_form += '</div>';

		$(playlist_form).dialog({
		  title: playlist,
		  close: function() { 
		  	$(this).remove()
		  },
		  modal: false,
		  width: 500,
		  open : function() {
		  	// Remove song that is already set
		  	song = songs.shift();
			// Add css styling
			let previous_id = song[0];
			$("#song-" + previous_id).addClass('font-weight-bold');
			// Play
			let audio = $(this).find('audio').get(0);
			audio.play();
 
			audio.addEventListener('ended',function() {
				// Get next song
				song = songs.shift();
				if (song !== undefined) {
					audio.src = base_url + song[0];
					$("#current-song").text(song[1]);
					$("#song-" + previous_id).removeClass('font-weight-bold');
					previous_id = song[0];
					$("#song-" + previous_id).addClass('font-weight-bold');
					audio.pause();
					audio.load();
					audio.play();
				}
			});

		  }
		})
	}

});
