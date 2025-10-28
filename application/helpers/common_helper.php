<?php
function common_array_maker_for_key_id($id, $result, $col_name) {
    if($result['status']) {
        $result = $result['data'];
        $res_arr = array();
        foreach ($result as $value) {
            $res_arr[$value->$id] = $value->$col_name;
        }
        return $res_arr;
    }
    return '';
}
