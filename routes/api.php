<?php

// routes/api.php
Route::middleware('throttle:5,1')->get('/test', function () {
    return 'OK';
});
