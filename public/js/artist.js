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

	$("input[name='play_songs']").click(function() {
		let artist_id = $(this).attr('id');
		artist_id = artist_id.replace("play-songs-", "");
		let artist = $(this).closest('tr').find('div[name="artist_name"]').text();

		let url = '/api/songs?artist_id=' + artist_id;


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
						display_songs(artist, data.songs);
					});
				}
			)
			.catch(function(err) {
				console.log('Fetch Error: ', err);
		});

	});

	function display_songs(artist, songs) {
		let song_url = APP_URL + '/song/play/';

		let playlist_form = '<div class="audio">';
		playlist_form += '<figure>';
		playlist_form += '<audio controls src="' + song_url + songs[0].id + '">Your browser does not support the<code>audio</code> element.</audio>';
		playlist_form += '</figure>';

		playlist_form += '<div>';
		for (i = 0; i < songs.length; i++) {
			playlist_form += '<span id="song-' + songs[i].id + '">' + songs[i].title + '</span><br>';
		}
		playlist_form += '</div>';
		playlist_form += '</div>';

		$(playlist_form).dialog({
		  title: artist,
		  close: function() {
			$(this).remove()
		  },
		  modal: false,
		  width: 500,
		  open : function() {
			// Remove song that is already set
			song = songs.shift();
			// Add css styling
			let previous_id = song.id;
			$("#song-" + previous_id).addClass('font-weight-bold');
			// Play
			let audio = $(this).find('audio').get(0);
			audio.play();

			audio.addEventListener('ended',function() {
				// Get next song
				song = songs.shift();
				if (song !== undefined) {
					audio.src = song_url + song.id;
					$("#current-song").text(song.title);
					$("#song-" + previous_id).removeClass('font-weight-bold');
					previous_id = song.id;
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