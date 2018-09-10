<?php

namespace Kyslik\LaravelFilterable\Exceptions;

use Exception;
use Illuminate\Http\Request;

class InvalidSettingsException extends Exception
{

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return \Illuminate\Http\Response|null
     */
    public function render(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $this->getMessage(), 'code' => 'not specified'], 400);
        }

        abort(400, $this->getMessage());
    }

}
