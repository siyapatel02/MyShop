<?php

function uploadImage(

    $file,

    $folder
){

    $filename =
        time() .
        '_' .
        basename(
            $file['name']
        );

    $target =
        '../uploads/' .
        $folder .
        '/' .
        $filename;

    move_uploaded_file(

        $file['tmp_name'],

        $target
    );

    return $filename;
}