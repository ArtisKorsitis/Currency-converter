<?php

/**
 * This is the main file which receives and analyzes data, 
 * generates response data and finally calls the template.
 */

// show all warnings and errors on the screen
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currencies = array ("USD"=>"US dollar",
"JPY"=>"Japanese yen",
"BGN"=>"Bulgarian lev",
"CZK"=>"Czech koruna",
"DKK"=>"Danish krone",
"GBP"=>"Pound sterling",
"HUF"=>"Hungarian forint",
"PLN"=>"Polish zloty",
"RON"=>"Romanian leu",
"SEK"=>"Swedish krona",
"CHF"=>"Swiss franc",
"ISK"=>"Icelandic krona",
"NOK"=>"Norwegian krone",
"HRK"=>"Croatian kuna",
"RUB"=>"Russian rouble",
"TRY"=>"Turkish lira",
"AUD"=>"Australian dollar",
"BRL"=>"Brazilian real",
"CAD"=>"Canadian dollar",
"CNY"=>"Chinese yuan renminbi",
"HKD"=>"Hong Kong dollar",
"IDR"=>"Indonesian rupiah",
"ILS"=>"Israeli shekel",
"INR"=>"Indian rupee",
"KRW"=>"South Korean won",
"MXN"=>"Mexican peso",
"MYR"=>"Malaysian ringgit",
"NZD"=>"New Zealand dollar",
"PHP"=>"Philippine peso",
"SGD"=>"Singapore dollar",
"THB"=>"Thai baht",
"ZAR"=>"South African rand");

// DO NOT EDIT BEFORE THIS LINE

/* Functions and classes You might want to use (you have to study function descriptions and examples)
 * Note: You can easily solve this task without using any regular expressions
file_get_contents() http://lv1.php.net/file_get_contents
file_put_contents() http://lv1.php.net/file_put_contents
file_exists() http://lv1.php.net/file_exists
round() http://lv1.php.net/round
SimpleXMLElement http://php.net/manual/en/simplexml.examples-basic.php http://php.net/manual/en/class.simplexmlelement.php 
date() http://lv1.php.net/manual/en/function.date.php or Date http://lv1.php.net/manual/en/class.datetime.php
Multiple string functions (choose by studying descriptions) http://lv1.php.net/manual/en/ref.strings.php
Multiple variable handling functions (choose by studying descriptions) http://lv1.php.net/manual/en/ref.var.php
Optionally you can use some array functions (with $_GET, $target_currencies) http://lv1.php.net/manual/en/ref.array.php
*/

// Your code goes here 

// *AUTORS* - Artis Korsîtis (ak20015)

$result = ""; //valid values: empty string, "OK", "ERROR"
$result_message = "";
$date = "";
$date_friday = "";
$cur_rate = "";
//Form validation
if ($_SERVER["REQUEST_METHOD"] == "GET")
{
    $current_date = date('Y-m-d');
    if (empty($_GET["date"]))
    {
     $result = "ERROR";
     $result_message = "Date not entered!";
    }
    else if ($_GET["date"]>$current_date)
    {
     $result = "ERROR";
     $result_message = "Date entered must not be in the future!";
    }
    else if ($_GET["date"]<'1999-01-04')
    {
     $result = "ERROR";
     $result_message = "Date entered is too far in the past! (MUST BE >= 04/01/1999)";
    }
    else if (($_GET['amount'])<'1')
    {
     $result = "ERROR";
     $result_message = "Amount entered must be larger than 0!";
    }
    else if (!is_numeric(($_GET['amount'])))
    {
     $result = "ERROR";
     $result_message = "Amount must be numeric!";
    }
    else if ( empty($_GET['currency']) )
    {
     $result = "ERROR";
     $result_message = "Currency not entered!";
    }
    else if (($_GET['amount'])>'1000000000')
    {
     $result = "ERROR";
     $result_message = "Currency entered too large! (DO NOT EXCEED 1'000'000'000)";
    }
     //Finding data and calculating currencies
    else
    {
    $cur = ($_GET['currency']);
    $cur = strtolower($cur);
        if (!file_exists($cur . ".xml"))
        {
            $cur_url = file_get_contents("https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/" . $cur . ".xml");
            $url_headers = @get_headers($cur_url);
            if($url_headers || $url_headers[0] == 'HTTP/1.1 404 Not Found') 
            {
                $result = "ERROR";
                $result_message = "Rates not found!";
            }
            else 
            {
                file_put_contents(".\xml\ " . $cur . ".xml", $cur_url);
            }

        if ( !empty($_GET['currency']) )
        {
        $cur = ($_GET['currency']);
        $cur = strtolower($cur);
            if (file_exists(".\xml\ " . $cur . ".xml"))
            {
                //Check if date is on weekend and make it Friday
                if (isWeekend($_GET["date"]))
                {
                    $dt=($_GET["date"]);
                    $dt1 = strtotime($dt);
                    $dt2 = date("l", $dt1);
                    $dt3 = strtolower($dt2);
                    if(($dt3 == "saturday" ))
                    {
                        $date_back = date_create($dt);
                        date_modify($date_back, '-1 day');
                        $date_friday = date_format($date_back, 'Y-m-d');
                    }
                    else if(($dt3 == "sunday"))
                    {
                        $date_back = date_create($dt);
                        date_modify($date_back, '-2 day');
                        $date_friday = date_format($date_back, 'Y-m-d');
                    }

                }
 
                    //Searching for rates/Making results
                    $xml = simplexml_load_file(".\xml\ " . $cur . ".xml");
                    $date = ($_GET['date']);
                    foreach($xml->DataSet->Series->Obs as $rate) 
                    {
                        if($rate['TIME_PERIOD'] == $date) 
                        {
                        $cur_rate = $rate['OBS_VALUE'];
                        }
                        else if ($rate['TIME_PERIOD'] == $date_friday)
                        {
                            $cur_rate = $rate['OBS_VALUE'];
                            $date = $date_friday;
                        }
                    }
                    if($cur_rate != 0)
                    {
                        $result = "OK";
                        $result_message = round(($_GET['amount']) / $cur_rate, 2);
                    }
                    else
                    {
                        $result = "ERROR";
                        $result_message = "No rates on this date!";
                    }
                

            }
        }
        }
    }
}
    function isWeekend($date) 
    {
        return (date('N', strtotime($date)) >= 6);
    }

// DO NOT EDIT AFTER THIS LINE

require("view.php");