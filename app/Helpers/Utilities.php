<?php

function getCountryNames() 
{
    if (! Cache::store('redis')->has('country_names')) {
        $countries = executeCurlRequest("https://restcountries.eu/rest/v2/all");
        $country_names = [];
        foreach($countries as $country) {
            $country_names[] = $country->name;
        }
        $country_names[] = 'England';
        $country_names[] = 'Scotland';
        $country_names[] = 'Wales';
        $country_names[] = 'Northern Ireland';
        $country_names[] = 'Multiple';
        $country_names[] = 'Not Applicable';
        $country_names[] = 'Unknown';
        asort($country_names);
        array_unshift($country_names, 'Please Select');
        Cache::store('redis')->put('country_names', $country_names, 10080);
    }
    return Cache::store('redis')->get('country_names');
}

function executeCurlRequest($url) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    if ($err):
        throw new Exception($err);
    endif;

    return json_decode($response);
}

/**
 * Replaces characters file systems cannot process
 */
function replaceSpecialFileSystemChars(String $s) {
    $s = str_replace(
        [":", "/", ";", "?", "[", "]", '"'],
        [" - ", "-", " ", "", "(", ")", "'"],
        $s
    );
    // Trim whitespace
    $s = trim($s);
    // Strip periods if they are at the end of the string
    return rtrim($s, '.');
}

/**
 * Only allow read queries
 *
 * @param String $query SQL query
 */
function isValidReadQuery(String $query) {
    if (stripos($query, 'DELETE') !== false
        || stripos( $query, 'UPDATE') !== false
        || stripos( $query, 'INSERT') !== false
        || stripos( $query, 'ALTER') !== false
        || stripos( $query, 'DROP') !== false) {
        return false;
    }
    return true;
}
