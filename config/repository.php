<?php

return [
    'users' => [
        'ttl' => 60 * 60, // TODO: set fixed ttl, change to dynamic in the future
        'prefix' => 'repository.users',
    ],

    'personal_access_tokens' => [
        'ttl' => 60 * 5, // TODO: set fixed ttl, change to dynamic in the future
        'prefix' => 'repository.personal_access_tokens',
    ],

];
