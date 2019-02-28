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
        return $check ? \Illuminate\Support\Str::replaceFirst($prefix, '', $subject) : substr($subject, strlen($prefix));
    }
}

if ( ! function_exists('force_assoc_array')) {
    /**
     * Transforms array to be associative.
     *
     * @param array $array
     * @param null  $empty
     *
     * @return array
     */
    function force_assoc_array(array $array, $empty = null)
    {
        $new = [];
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $new[$value] = $empty;
            } else {
                // ?? $empty, if we want to make null -> ''
                $new[$key] = $value ?? $empty;
            }
        }

        return $new;
    }
}
