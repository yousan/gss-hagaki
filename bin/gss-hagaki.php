<?php

main();

/**
 *
 */
function main()
{
    /**
     *
     */
    $shortopts = "u:";  // 値が必須

    $longopts = array(
        "url:",     // 値が必須
    );
    $options = getopt($shortopts, $longopts);
    if ( empty($options['url']) && empty($options['u'])) {
        var_dump('-u or --url option is required.');
    }


}