<?php

if ( ! function_exists('remove_prefix')) {
    /**
     * Simply removes prefix.
     *
     * @param      $prefix
     * @param      $subject
     * @param bool $check We can skip the "existence" of the prefix and make removing a bit optimized.
     *
     * @return bool|string
     */
    function remove_prefix($prefix, $subject, $check = true)
    {
        return ($check) ? str_replace_first($prefix, '', $subject) : substr($subject, strlen($prefix));
    }
}