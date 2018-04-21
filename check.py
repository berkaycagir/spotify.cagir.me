#!/usr/bin/python
import requests, checker_library

input_list = checker_library.load_list()

for i in input_list:
    spotifyHeaders = checker_library.get_access_token()
    is_found = False

    song_id = i[0]
    country_id = i[1]
    recipient = i[2]

    r = requests.get("https://api.spotify.com/v1/tracks/" + song_id, headers=spotifyHeaders)
    data = r.json()

    if 'error' in data:
        if data['error']['status'] == 404:
            checker_library.send_mail('We\'re sorry, but the song ID "%s" provided by you can\'t be found in Spotify\'s database anymore.\n\nThanks!' % (song_id), 'Song can\'t be found', recipient)
            input_list = [x for x in input_list if x != i]
        continue

    for j in data['available_markets']:
        if(j.encode('utf-8') == country_id):
            is_found = True
            checker_library.send_mail('The song "%s" by %s is right now available in %s.\n\nJust search the string "%s" (between the quotes) on the Spotify client to play it.\n\nEnjoy!' % (data['name'].encode('utf-8'), data['artists'][0]['name'].encode('utf-8'), country_id, data["uri"].encode("utf-8")), 'w00t! Song is available now!', recipient)
            input_list = [x for x in input_list if x != i]
            continue

        if not is_found:
            r = requests.get("https://api.spotify.com/v1/tracks/" + song_id + "?market=" + country_id, headers=spotifyHeaders)
            newData = r.json()

            if newData["is_playable"]:
                checker_library.send_mail('The song "%s" by %s is right now available in %s with the song ID %s.\n\nJust search the string "%s" (between the quotes) on the Spotify client to play it.\n\nEnjoy!' % (data['name'].encode('utf-8'), data['artists'][0]['name'].encode('utf-8'), country_id, newData["id"].encode("utf-8"), newData["uri"].encode("utf-8")), 'w00t! Song is available now!', recipient)
                input_list = [x for x in input_list if x != i]
                continue

checker_library.save_list(input_list)

