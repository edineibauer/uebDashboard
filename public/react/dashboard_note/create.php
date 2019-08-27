<?php

if (defined("PUSH_PUBLIC_KEY") && !empty(PUSH_PUBLIC_KEY) && defined("PUSH_PRIVATE_KEY") && !empty(PUSH_PRIVATE_KEY))
    require_once 'inc/sendpush.php';