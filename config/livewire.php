<?php

return [
    /*
    |---------------------------------------------------------------------------
    | Temporary File Uploads
    |---------------------------------------------------------------------------
    |
    | Store temporary uploads on the PUBLIC disk so previews are served as plain
    | static files (https://APP_URL/storage/livewire-tmp/...). The default
    | (private disk) serves previews through a signed /livewire/preview-file
    | route, which Cloudflare challenges/blocks — leaving FilePond stuck on
    | "loading" and never showing the image. Requires `php artisan storage:link`.
    |
    */
    'temporary_file_upload' => [
        'disk'            => 'public',
        'rules'           => ['required', 'file', 'max:12288'], // 12MB
        'directory'       => 'livewire-tmp',
        'middleware'      => null,
        'preview_mimes'   => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4', 'mov', 'avi',
            'wmv', 'mp3', 'm4a', 'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5,
        'cleanup'         => true,
    ],
];
