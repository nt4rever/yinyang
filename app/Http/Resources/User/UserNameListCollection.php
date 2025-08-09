<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

class UserNameListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
