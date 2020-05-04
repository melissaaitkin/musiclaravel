$(document).ready(function() {

	$("a[name='play']").click(function() {

		let title = $(this).parent().prev('td').find('div').text();
		let base_url = APP_URL + '/song/play/';
		let songs_obj = JSON.parse($(this).next('input').val());
		let songs = Object.keys(songs_obj).map((key) => [key, songs_obj[key]]);

		let playlist = '<div class="audio">';
		playlist += '<figure>';
		playlist += '<figcaption id="current-song">' + songs[0][1] + '</figcaption>';
		playlist += '<audio controls src="' + base_url + songs[0][0] + '">Your browser does not support the<code>audio</code> element.</audio>';
		playlist += '</figure>';
		
		playlist += '<div>';
		for (i = 0; i < songs.length; i++) {
			playlist += '<span>' + songs[i][1] + '</span><br>';
		}
		playlist += '</div>';
		playlist += '</div>';

		$(playlist).dialog({
		  title: title,
		  close: function() { 
		  	$(this).remove()
		  },
		  modal: false,
		  width: 500,
		  open : function() {
		  	// Remove song that is already set
		  	song = songs.shift();
			var audio = $(this).find('audio').get(0);  	
			audio.play();
 
			audio.addEventListener('ended',function() {
				// Get next song
				song = songs.shift();
				if (song !== undefined) {
					audio.src = base_url + song[0];
					$("#current-song").text(song[1]);
					audio.pause();
					audio.load();
					audio.play();
				}
			});

		  }
		})


	});

});
