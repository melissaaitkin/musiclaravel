<?php

function get_country_names() 
{
    if (!Cache::store('redis')->has('country_names')) {
        $countries = json_decode(file_get_contents( "https://restcountries.eu/rest/v2/all"));
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
        asort($country_names);
        array_unshift($country_names, 'Please Select');
        Cache::store('redis')->put('country_names', $country_names, 10080);
    }
    return Cache::store('redis')->get('country_names');
}

/**
 * Replaces characters file systems cannot process
 */
function replace_special_file_system_chars(String $string) {
    return str_replace(
        ["'", ":", "/", ";", "?", "."],
        ["", " -", "-", " ", "", ""],
        $string
    );
}