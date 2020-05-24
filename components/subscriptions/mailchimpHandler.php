<?php
/**
 * Execute some action using the subscriptions API
 * Thanks to https://rudrastyh.com/mailchimp-api/get-lists.html
 * @param $url - full URL which you want to get data from
 * - eg https://us18.api.mailchimp.com/3.0/lists/043e7cb483/members
 * @param $request_type - GET or POST
 * @param $api_key - including the -lldd
 * - eg 7b2cdec9bed23b53e124ad6ddefb9b08-us18
 * @param array $data - either
 * - an associative array of key value pairs
 * - a single string
 * - not a required parameter
 * @return bool|string -
 * - false on failure to make the call
 * - otherwise, a string that is JSON (so json_decode it)
 */
function mailchimpHandler($url, $request_type, $api_key, $data = array())
{
    if ($request_type == 'GET' && isset($data))
        $url .= '?' . http_build_query($data);

    error_log("mailchimphandler asking for :" . $url . ":");

    $mch = curl_init();
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode('user:' . $api_key)
    );
    curl_setopt($mch, CURLOPT_URL, $url);
    curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); // do not echo the result, write it into variable
    curl_setopt($mch, CURLOPT_CUSTOMREQUEST, $request_type); // according to MailChimp API: POST/GET/PATCH/PUT/DELETE
    curl_setopt($mch, CURLOPT_TIMEOUT, 10);
    curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); // certificate verification for TLS/SSL connection

    if ($request_type != 'GET') {
        curl_setopt($mch, CURLOPT_POST, true);
        curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data)); // send data in json
    }

    return curl_exec($mch);
}
/**
 * Responses to different calls
 * Subscribing to the list
 *  * successful subscription:
 * [status] => subscribed
 * the title and detail nodes do not exist when the call succeeds in subscribing this email address
 *
 *  * member already know (subscribed, or not)
 * [title] => Member Exists
 * [status] => 400
 * [detail] => flemmishElephant@bazookas.com is already a list member. Use PUT to insert or update list members.
 *
 *  *  given address looks (to subscriptions) fake or invalid
 * [title] => Invalid Resource
 * [status] => 400
 * [detail] => a@b.com looks fake or invalid, please enter a real email address.
 * An archived contact can rign-up again - they will become unarchived (by the looks of it)
 */
