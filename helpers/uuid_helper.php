<?php
function get_uuid()
{
    $random = bin2hex(random_bytes(16));

    $random[12] = '4';

    $random[16] = chr(ord($random[16]) & 0x3 | 0x8);

    return sprintf(
        '%08s-%04s-%04x-%04x-%12s',
        substr($random, 0, 8),
        substr($random, 8, 4),
        hexdec(substr($random, 12, 4)),
        hexdec(substr($random, 16, 4)),
        substr($random, 20, 12)
    );
}
