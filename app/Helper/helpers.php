<?php
use Illuminate\Support\Carbon;

function encryptId($string)
{
    // Store the cipher method
    $ciphering = "AES-128-CTR";

    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

    // Non-NULL Initialization Vector for encryption
    $encryption_iv = '1828301288182938';

    // Store the encryption key
    $encryption_key = "%83pIWJmSs^&";

    return openssl_encrypt(
        $string,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
}
function numberDelimited($nilaiUang, $decimalplace = 0)
{
    return number_format((float) $nilaiUang, $decimalplace, ",", ".");
}
function convertTime($dec)
{
    // start by converting to seconds
    $seconds = ($dec * 3600);
    // we're given hours, so let's get those the easy way
    $hours = floor($dec);
    // since we've "calculated" hours, let's remove them from the seconds variable
    $seconds -= $hours * 3600;
    // calculate minutes left
    $minutes = floor($seconds / 60);
    // remove those from seconds as well
    $seconds -= $minutes * 60;
    // return the time formatted HH:MM:SS
    return str_pad($hours, 2, 0, STR_PAD_LEFT) . ":" . str_pad($minutes, 2, 0, STR_PAD_LEFT);
}



function normalizeNumber($number)
{
    $number = str_replace(" ", "", $number);
    $number = str_replace("Rp", "", $number);
    $number = str_replace(".", "", $number);
    $number = str_replace("+", "", $number);
    $number = str_replace(",", ".", $number);
    if (!is_numeric($number)) {
        $number = 0;
    }
    return $number;
}

function defaultDraftStatusId()
{
    return 284;
}
function defaultClossedStatusId()
{
    return 359;
}

function getAuthAcc()
{
    // $aolraw = Cache::remember('aolconfig', 600, function () {
    //     $date = Carbon::now()->format('d/m/Y H:i:s');
    //     $hash = hash_hmac('SHA256', $date, (config('app.ACC_KEY')));
    //     $response = Http::withToken((config('app.ACC_TOKEN')))
    //         ->withHeaders([
    //             'X-Api-Timestamp' => $date,
    //             'X-Api-Signature' => $hash
    //         ])->post('https://account.accurate.id/api/api-token.do');
    //     return $response;
    // });
    // log::info('Response AUTH ' . $aolraw);
    $data['d']['database']['host'] = 'https://zeus.accurate.id';

    // if (isset($aolraw['d']['database']['host'])) {
    //     $data['d']['database']['host'] = $aolraw['d']['database']['host'];
    //     $data['d']['application']['appKey'] = $aolraw['d']['application']['appKey'];
    // }

    return $data;
}
function decryptId($string)
{
    // Store the cipher method
    $ciphering = "AES-128-CTR";

    // Use OpenSSl Encryption method
    $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

    // Non-NULL Initialization Vector for encryption
    $encryption_iv = '1828301288182938';

    // Store the encryption key
    $encryption_key = "%83pIWJmSs^&";

    return openssl_decrypt(
        $string,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
}

function decryptForNumber($string)
{
    try {
        $menuid = decryptId($string);
        if (!is_numeric($menuid)) {
            $menuid = 0;
        }
    } catch (\Throwable $th) {
        $menuid = 0;
    }
    return $menuid;
}

function getLastDelimiterToInt($string, $delimter)
{
    try {
        return (int) substr($string, strripos($string, $delimter) + 1, strlen($string) - strripos($string, "."));
    } catch (\Throwable $th) {
        return 0;
    }
}
function setIndentDelimiter($string, $delimter, $indent = " ", $indentstart = 2)
{
    try {
        // dd([strlen($string),count(explode($string, $delimter))]);
        $countdelimiter = count(explode($delimter, $string)) - 1;
        if ($countdelimiter <= $indentstart) {
            $countdelimiter = 0;
        }
        return str_pad($string, (($countdelimiter) * strlen($indent)) + strlen($string), $indent, STR_PAD_LEFT);
    } catch (\Throwable $th) {
        return $string;
    }

}

function generatemenu()
{
    $rawData = session('menu');
    $htmlcontent = '';
    if (count($rawData) > 0) {
        $sub_children_array = getChildren($rawData);
        $htmlcontent = createmenu($sub_children_array);
    }
    return $htmlcontent;
}

function getChildren($result)
{
    $sub_children = array();
    $sub_array2 = array();

    $itemsByReference = array();

    // Build array of item references:
    foreach ($result as $item) {
        if ($item->parent_menu_id == 0) {
            $itemsByReference[$item->pk_menu_id] = generate_menu_tree($item);
        }
    }

    // Set items as children of the relevant parent item.
    foreach ($result as $item) {
        if ($item->parent_menu_id > 0) {
            $itemsByReference = where_parent_is($itemsByReference, $item);
        }
    }

    // Remove items that were added to parents elsewhere:
    foreach ($result as $item) {
        if ($item->pk_menu_id && isset($itemsByReference[$item->parent_menu_id])) {
            unset($itemsByReference[$item->pk_menu_id]);
        }
    }
    foreach ($itemsByReference as $row) {
        $data[] = $row;
    }
    // Check if they havent child, remove them
    $response = [];
    foreach ($data as $val) {
        //$res_data=clear_child_menu($val);
        if (count($val['children']) > 0) {
            $temp = clear_child_menu($val, 0);
            if (count($temp['children']) > 0) {
                $response[] = $temp;
            }

        } else {
            if ($val['url'] != '') {
                $response[] = clear_child_menu($val, 0);
            }
        }
    }
    // Encode:
    return $response;
}
function generate_menu_tree($row)
{

    $sub_children = array();

    $sub_children["id"] = $row->pk_menu_id;

    $sub_children["text"] = $row->name;

    $sub_children["icon"] = true;
    $sub_children["url"] = $row->url;
    $sub_children["children"] = array();

    return $sub_children;
}
function where_parent_is($arr, $items)
{
    foreach ($arr as $key => &$item) {
        if (isset($item['id']) && $item['id'] == $items->parent_menu_id) {
            $item['children'][] = generate_menu_tree($items);
        }
        if (isset($item['children']) && is_array($item['children']) && count($item['children']) > 0) {
            $item['children'] = where_parent_is($item['children'], $items);
        }
    }
    return $arr;
}

function createmenu($result, $is_child = false, $is_root = true)
{
    $data = '';
    foreach ($result as $vals) {
        $link = '#';
        $text = $vals['text'];
        /*Get URL*/
        if (isset($vals['url']) && $vals['url'] != "") {
            $dataurl = explode('|', $vals['url']);
            if (is_array($dataurl) && count($dataurl) > 0) {
                $link = route($dataurl[0]);
            } else {
                $link = route($vals['url']);
            }
        }
        /*Get Children*/
        if (isset($vals['children']) && is_array($vals['children']) && count($vals['children']) > 0) {
            $data .= '<li class="nav-item">
                <a href="' . $link . '" class="nav-link">
                <i class="nav-icon fas fa-circle"></i>
                    <p>' . $text . '<i class="right fas fa-angle-left"></i></p></a>';
            $data .= '<ul class="nav nav-treeview">';
            $data .= createmenu($vals['children'], true, false);
        } else {
            if ($is_root == true) {
                $data .= '<li class="nav-item">
                <a href="' . $link . '" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                    <p>' . $text . '<i class="right fas fa-angle-left"></i></p></a></li>';

            } else {
                $data .= '<li class="nav-item">
                <a href="' . $link . '" class="nav-link">
                <i class="far fa-dot-circle nav-icon"></i>
                    <p>' . $text . '</p></a></li>';
            }
        }

        if (isset($vals['children']) && is_array($vals['children']) && count($vals['children']) > 0) {
            $data .= '</li>';
            $data .= '</ul>';
        }

    }
    return $data;
}

function validateDateFormat(string $date, string $format): bool
{
    $d = \DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function ConvertDateFormat(string $date)
{
    return date('Y-m-d', strtotime($date));
}

function clear_child_menu($data, $level = 0)
{
    // Check if they havent child, remove them
    if ($level == 1) {

    }
    $i = 0;
    if (isset($data['children']) && count($data['children']) > 0) {

        foreach ($data['children'] as $val) {
            if (count($val['children']) > 0) {
                $data['children'][$i] = clear_child_menu($val, 1);
                if (count($data['children'][$i]['children']) == 0) {
                    if (isset($data['children'][$i]['url']) && $data['children'][$i]['url'] != '') {

                    } else {
                        unset($data['children'][$i]);
                    }
                }
            } else {
                if (isset($val['url']) && $val['url'] != '') {

                } else {
                    unset($data['children'][$i]);
                }
            }
            $i += 1;
        }

    } else {
        if (isset($data['url']) && $data['url'] != '') {

        } else {
            unset($data[$i]);
        }
    }
    return $data;
}
