<?php
include "header.php";
include "config.php";

if(empty($_POST['id']) || empty($_POST['country']) || empty($_POST['mail'])) {
    echo "One or more required parameters have not been sent.";
    include "footer.php";
    exit();
}

// setting variables, provided all three available
$id = sanitize_id($_POST['id'], get_access_token($spotify_auth_token, $spotify_refresh_token));
$country = $_POST['country'];
$mail = $_POST['mail'];

// functions begin
function get_access_token($spotify_auth_token, $spotify_refresh_token) {
    $url = "https://accounts.spotify.com/api/token";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic '.$spotify_auth_token
        ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'grant_type' => 'refresh_token',
        'refresh_token' => $spotify_refresh_token
        )));
    $result = curl_exec($ch);
    curl_close($ch);

    $obj = json_decode($result, true);
    $accessToken = $obj['access_token'];

    return array("Authorization: Bearer ".$accessToken, "Accept: application/json");
}

function get_song($id, $spotify_headers) {
    $url = "https://api.spotify.com/v1/tracks/".$id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $spotify_headers);
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

function get_first_song_of_album($id, $spotify_headers) {
        $url = "https://api.spotify.com/v1/albums/".$id."/tracks?offset=0&limit=1";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $spotify_headers);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true)["items"][0]["id"];
}

function get_countries() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'http://country.io/names.json');
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

function is_track_relinked($id, $country, $spotify_headers) {
    $url = "https://api.spotify.com/v1/tracks/".$id."?market=".$country;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $spotify_headers);
    $result = curl_exec($ch);
    curl_close($ch);

    $obj = json_decode($result, true);

    if($obj["is_playable"]) {
        return $obj["uri"];
    } else {
        return false;
    }
}

function sanitize_id($input, $spotify_headers) {
        $retval = "";
        if(preg_match("/https?:\/\/open.spotify.com\/(album|track)\/(.+?)(?=\?|\n|$)/", $input, $matches)) {
                if($matches[1] == "track") {
                        $retval = $matches[2];
                } else {
                        $retval = get_first_song_of_album($matches[2], $spotify_headers);
                }
        } else {
                $retval = $input;
        }

        if(!preg_match("/^(\w*)$/", $retval)) {
                echo "Song ID is not valid.";
                include "footer.php";
                exit();
        }

        return $retval;
}

// functions end

// checking if track relinked
if(($linked_track_id = is_track_relinked($id, $country, get_access_token($spotify_auth_token, $spotify_refresh_token))) != false) {
    echo "The track you're searching for is available with a different ID in your country. Search <code>".escapeshellarg($linkedTrackID)."</code> in Spotify client to play it.";
    include "footer.php";
    exit();
}

// getting song if existent
$obj = get_song($id, get_access_token($spotify_auth_token, $spotify_refresh_token));
// getting countries
$countries = get_countries();

// starting output
if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format.";
    include "footer.php";
    exit();
}

if(isset($obj["error"])) {
    echo "Song by ID ".$id." is not found.";
    include "footer.php";
    exit();
}

if(!isset($countries[$country])) {
    echo "Country is not present in ISO 3166-1 alpha-2 list.";
    include "footer.php";
    exit();
}

foreach($obj["available_markets"] as $item) {
    if($item == $country) {
        echo "Song is already available in specified country.";
        include "footer.php";
        exit();
    }
}

exec('python /var/www/spotify.cagir.me/add.py '.escapeshellarg($id).' '.escapeshellarg($country).' '.escapeshellarg($mail), $output, $return_var);

if($return_var == 0) {
    echo "Song added to the check list. It will be checked every hour.";
} else {
    echo "Duplicate checker.";
}

include "footer.php";

?>
