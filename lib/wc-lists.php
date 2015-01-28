<?php
use Sunra\PhpSimple\HtmlDomParser;

function get_lists($username, $show_items = false) {
    $out = array();
    $body = HtmlDomParser::file_get_html(get_url($username));
    $lists = $body->find('.table-results-lists tbody tr');
    $i = 0;
    foreach($lists as $list) {
        $name_raw = $list->find('.list a');
        $name = !empty($name_raw) ? $name_raw[0]->plaintext : "";
        $id = !empty($name_raw) ? extract_list_id($name_raw[0]->attr['href']) : "";
        
        $desc_raw = $list->find('.list .description');
        $desc = !empty($desc_raw) ? trim($desc_raw[0]->plaintext) : "";

        $out[$i] = array(
            "name" => $name,
            "id" => $id,
            "description" => $desc
        );
        
        if ( $show_items ) {
            $out[$i]['items'] = !empty($id) ? get_list_items($username, $id) : array();
        }
        $i++;
    }

    return $out;
}

function get_list_items($username, $listID) {
    $out = array();
    $list = HtmlDomParser::file_get_html(get_url($username, $listID));
    $items = $list->find('.table-results-list tbody tr');
    
    foreach($items as $item) {
        $name = $item->find('.name a')[0];
        $oclc_no = extract_oclc_number($name->attr['href']);
        
        $title = $name->plaintext;

        $author_raw = $item->find('.author');
        $author = !empty($author_raw) 
                ? trim(preg_replace("/^\s*by/i", "", $author_raw[0]->plaintext))
                : "";
        
        // I'm having a heck of a time removing the trailing space,
        // I think it's an encoding issue?
        $type_raw = $item->find('.type');
        $format = !empty($type_raw) 
                ? trim(str_replace('\u00a0', "", (html_entity_decode($type_raw[0]->plaintext))))
                : "";

        $notes_raw = $item->find('div.description');
        $notes = !empty($notes_raw)
               ? trim(preg_replace("/^\s*notes\:/i", "", $notes_raw[0]->plaintext))
               : ""
               ;
        
        $added = $item->find('.social .date');
        $date = !empty($added) ? trim(preg_replace("/^\s*added/i", "", $added[0]->plaintext)) : "";

        $out[] = array(
            "title" => $title,
            "author" => $author,
            "format" => $format,
            "oclc_number" => $oclc_no,
            "notes" => $notes,
            "date_added" => $date
        );
    }
    return $out;
}

// taken from: http://magp.ie/2011/01/06/remove-non-utf8-characters-from-string-with-php/
function clean_str($str) {
    $str = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
                 '|[\x00-\x7F][\x80-\xBF]+'.
                 '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
                 '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
                 '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
                 '', $str );

    $str = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
                        '|\xED[\xA0-\xBF][\x80-\xBF]/S','', $str);

    return $str;
}

/**
 *  pulls the list
 *
 */

function extract_list_id($path) {
    $list_regex = "|/lists/(\d+)|";

    if ( preg_match($list_regex, $path, $m) ) {
        return isset($m[1]) ? $m[1] : null;
    } else {
        return null;
    }
}

function extract_oclc_number($url) {
    $oclc_regex = "|/oclc/(\d+)|";

    preg_match($oclc_regex, $url, $m);
    return isset($m[1]) ? $m[1] : null;
}

function get_url($username, $listID = null) {
    $url = "http://www.worldcat.org/profiles/{$username}/lists";
    if ( !is_null($listID) ) { $url .= "/{$listID}"; }

    return $url;
}