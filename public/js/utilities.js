function display_jukebox(title, songs) {
	let song_url = APP_URL + '/song/play/';

	let jukebox_form = '<div class="audio">';
	jukebox_form += '<figure>';
	jukebox_form += '<audio controls src="' + song_url + songs[0].id + '">Your browser does not support the<code>audio</code> element.</audio>';
	jukebox_form += '</figure>';
	jukebox_form += '<button class="next">Next</button>';

	jukebox_form += '<div>';
	for (i = 0; i < songs.length; i++) {
		jukebox_form += '<span id="song-' + songs[i].id + '">' + songs[i].title + '</span><br>';
	}
	jukebox_form += '</div>';
	jukebox_form += '</div>';

	$(jukebox_form).dialog({
	  title: title,
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

		let next = $(this).find('button.next').get(0);

		audio.addEventListener('ended',function() {
			previous_id = next_song(audio, next, previous_id);
		});

		next.addEventListener('click', function() {
			previous_id = next_song(audio, next, previous_id);
		});

		function next_song(audio, next, previous_id) {
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
				return previous_id;
			} else {
				 next.disabled = true;
			}
		}

	  }
	})
}