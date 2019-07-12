<?php

if (defined("PUSH_PUBLIC_KEY") && !empty(PUSH_PUBLIC_KEY))
    require_once 'inc/sendpush.php';