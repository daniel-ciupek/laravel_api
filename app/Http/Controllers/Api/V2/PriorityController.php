<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Http\Resources\PriorityResource;


class PriorityController extends Controller
{
     public function __invoke()
    {
        return PriorityResource::collection(Priority::all());;
    }
}
