$(document).ready(function() {

    $("#album").change(function() {

        var url = '/internalapi/songs?album=' + encodeURIComponent($('#album').val());

        fetch(url)
            .then(
                function(response) {
                    if (response.status !== 200) {
                        console.log('Looks like there was a problem. Status Code: ' + response.status);
                        return;
                    }
                    response.json().then(function(data) {
                        var ol = '<ol id="songs" style="list-style-type:none">';
                        $.each(data.songs, function(k, song) {
                            ol += '<li><a href="/song/' + song.id + '">' + song.title + '</a></li>';
                        });
                        ol += '</ol>';
                        $("#songs").replaceWith(ol);
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

        let url = '/internalapi/artist/songs/' + artist_id;

        fetch(url)
            .then(
                function(response) {
                    if (response.status !== 200) {
                        console.log('Looks like there was a problem. Status Code: ' + response.status);
                        return;
                    }
                    response.json().then(function(songs) {
                        display_jukebox(artist, songs);
                    });
                }
            )
            .catch(function(err) {
                console.log('Fetch Error: ', err);
        });

    });

});