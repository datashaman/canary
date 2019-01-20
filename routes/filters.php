<?php

return [
    'please-build' => function ($request) {
        return starts_with($request->input('comment.body'), 'please build');
    },
];
