<?php
require_once "mailchimpHandler.php";

/**
 * @param $humanReadableEmailAddress
 * @return mixed
 * - if user exists, a JSON object
 * - else false
 */
function getSubscriberData($humanReadableEmailAddress)
{
    // see https://mailchimp.com/developer/reference/lists/list-members/#get_/lists/-list_id-/members/-subscriber_hash-
    $hashedAddress = md5(strtolower($humanReadableEmailAddress));

    $response = json_decode(mailchimpHandler(
        "https://us18.api.mailchimp.com/3.0/lists/043e7cb483/members/" . $hashedAddress,
        'GET',
        '7b2cdec9bed23b53e124ad6ddefb9b08-us18'
    ));

    if ($response->status == "404")
        /*
    stdClass Object
    (
        [type] => http://developer.mailchimp.com/documentation/mailchimp/guides/error-glossary/
        [title] => Resource Not Found
        [status] => 404
        [detail] => The requested resource could not be found.
        [instance] => 84ab5273-dac9-45bb-a685-fe1364d9786a
    )
         */
        return false;
    else
        return $response;
}

/**
 * @param $humanReadableEmailAddress - eg aKGSAHDG@LSJKHFkjf.com
 * @param $why mixed - out
 * - if the operation fails and we know why (eg, MC does not like the address)
 * - mostly a string, but could be just the response as a json object
 * @return bool
 * - true - the person does exist in the list when all is done
 * - if they were already there, this is treated as a success
 */
function subscribePerson($humanReadableEmailAddress, &$why = null)
{
    $why = "";

    // some of these parameters are here just to show the way
    $data = array(
//    'fields' => 'lists', // total_items, _links
        'email_address' => $humanReadableEmailAddress,
        'status' => 'subscribed' // i.e. they have subscribed to this list
//    'count' => 5, // the number of lists to return, default - all
//    'before_date_created' => '2016-01-01 10:30:50', // only lists created before this date
//    'after_date_created' => '2014-02-05' // only lists created after this date
    );

    $response = json_decode(mailchimpHandler(
        "https://us18.api.mailchimp.com/3.0/lists/043e7cb483/members",
        'POST',
        '7b2cdec9bed23b53e124ad6ddefb9b08-us18'
        , $data
    ));

    switch ($response->status) {
        case "subscribed":
            return true;
        case "400":
            switch ($response->title) {
                case "Member Exists":
//                    error_log("User " . $humanReadableEmailAddress . " exists already");
                    return true;
                default:
//                    error_log("Mailchimp rejected that email");
//                    error_log($response->detail);
                    $why = $response->detail;
                    return false;
            }
            break;
        default:
            error_log("Mailchimp barfed completely");
            error_log(print_r($response, true));
            $why = $response;
            return false;
    }
}
