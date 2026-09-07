<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubmissionException extends Exception
{
    public function __construct(string $message = "", int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception into an HTTP response.
     * When requested from an Inertia or standard web page, redirect back with flash error message.
     */
    public function render(Request $request): Response
    {
        if ($request->header('X-Inertia') || !$request->expectsJson()) {
            return back()->with('error', $this->getMessage());
        }

        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 403);
    }
}
