<?php

function get_country_names() 
{
    // TODO add to cache
    $countries = json_decode( file_get_contents( "https://restcountries.eu/rest/v2/all") );
    $country_names = [];
    foreach( $countries as $country ) {
        $country_names[] = $country->name;
    }
    $country_names[] = 'England';
    $country_names[] = 'Scotland';
    $country_names[] = 'Wales';
    $country_names[] = 'Northern Ireland';
    asort($country_names);
    array_unshift($country_names, 'Please Select');
    return $country_names;
}
