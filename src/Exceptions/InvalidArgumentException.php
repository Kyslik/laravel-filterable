<?php

namespace Kyslik\LaravelFilterable\Exceptions;

use Exception;

class InvalidArgumentException extends Exception
{

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $this->getMessage(), 'code' => 'not specified'], 400);
        }

        abort(400, $this->getMessage());
    }

}