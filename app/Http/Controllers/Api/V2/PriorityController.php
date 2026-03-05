<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Priority;


class PriorityController extends Controller
{
     public function __invoke()
    {
        return response()->json([
            'data' => Priority::all()
        ]);
    }
}
