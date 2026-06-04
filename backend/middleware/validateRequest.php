<?php

function validateFields(

    $fields
){

    foreach($fields as $field){

        if(empty($field)){

            return false;
        }
    }

    return true;
}