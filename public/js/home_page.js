$(document).ready(function() {

	$("a[name='shuffle_songs']").click(function() {
		var url = '/internalapi/songs';

		fetch(url)
			.then(
				function(response) {
					if (response.status !== 200) {
						console.log('Looks like there was a problem. Status Code: ' + response.status);
						return;
					}
					response.json().then(function(data) {
						play_songs("EVERYBODY SHUFFLIN...", data.songs);
					});
				}
			)
			.catch(function(err) {
				console.log('Fetch Error: ', err);
		});

	});
});

function play_songs(title, songs) {
	let song_url = APP_URL + '/song/play/';

	songs = shuffle(songs);

	let jukebox_form = '<div class="audio">';
	jukebox_form += '<figure>';
	jukebox_form += '<figcaption id="song_title">' +  songs[0].title + '</figcaption>';
	jukebox_form += '<audio controls src="' + song_url + songs[0].id + '">Your browser does not support the<code>audio</code> element.</audio>';
	jukebox_form += '</figure>';
	jukebox_form += '<button class="next">Next</button>';
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
		// Play
		let audio = $(this).find('audio').get(0);
		audio.play();

		audio.addEventListener('ended',function() {
			next_song(audio);
		});

		let next = $(this).find('button.next').get(0);
		next.addEventListener('click', function() {
			next_song(audio);
		});

		function next_song(audio) {
			// Get next song
			song = songs.shift();
			if (song !== undefined) {
				audio.src = song_url + song.id;
				$("#song_title").text(song.title);
				audio.pause();
				audio.load();
				audio.play();
			}
		}

	  }
	})
}

function shuffle(array) {
	var currentIndex = array.length, temporaryValue, randomIndex;

	// While there remain elements to shuffle
	while(0 !== currentIndex) {
		// Pick a remaining element...
		randomIndex = Math.floor(Math.random() * currentIndex);
		currentIndex -= 1;

		// And swap it with the current element
		temporaryValue = array[currentIndex];
		array[currentIndex] = array[randomIndex];
		array[randomIndex] = temporaryValue;
	}

	return array;
}