$(document).ready(function() {

	var playlist_url = '/api/playlists';
	var song_url = APP_URL + '/song/play/';

	$("input[name='playlist']").click(function() {

		let song_id = $(this).attr('id');
		song_id = song_id.replace("playlist-", "");

		fetch(playlist_url, {
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
						display_playlist_form(song_id, data.playlists);
					});
				}
			)
			.catch(function(err) {
				console.log('Fetch Error: ', err);
		});

	});

	function display_playlist_form(song_id, playlists) {
		let playlist_form = '<div>';

		playlist_form += '<div id="error_message" class="d-none alert alert-danger alert-dismissible fade show">';
		playlist_form += 'Please select a playlist.';
		playlist_form += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
		playlist_form += '</div>';

		playlist_form += '<div>Add to Existing Playlist</div>';
		playlist_form += '<select id="existing_playlist">';
		playlist_form += '<option value="">Please Select</option>';
		$.each(playlists, function(index, playlist) {
			playlist_form += '<option value="' + playlist + '">' + playlist + '</option>';
		});
		playlist_form += '</select>';
		playlist_form += '<div>Add to New Playlist</div>';
		playlist_form += '<input id="new_playlist"/>';

		playlist_form += '<input type="hidden" id="song_id" value="' + song_id + '"/>';
		playlist_form += '</div>';

		$(playlist_form).dialog({
			title: 'Playlists',
			modal: false,
			width: 500,
			buttons: {
				"Add": function() {
					let playlist = $("#existing_playlist option:selected").val();
					if (playlist == '') {
						playlist = $('#new_playlist').val();
					}
					if (playlist == '') {
						$('#error_message').removeClass('d-none');
					} else {
						const data = {playlist: playlist, id: $('#song_id').val()};
						fetch(playlist_url, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
							},
								body: JSON.stringify(data),
							})
							.then(response => response.json())
							.then(data => {
								$(this).dialog("close");
							})
							.catch((error) => {
								$('#error_message').removeClass('d-none').text("An error occurring adding the song");
								console.error('Error:', error);
							});
					}
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		})
	}

	$("input[name='play_album']").click(function() {
		let song_id = $(this).attr('id');
		song_id = song_id.replace("play-album-", "");

		var url = '/api/songs?id=' + song_id + '&album=true&authentication_token=fdsafsf';

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
						display_album(data.songs[0].album, data.songs);
					});
				}
			)
			.catch(function(err) {
				console.log('Fetch Error: ', err);
		});

	});

	function display_album(album, songs) {
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
		  title: album,
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