@props(['name' => 'circle', 'class' => ''])

@php
    $icons = [
        'dashboard' => '<path d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z"/>',
        'settings' => '<path d="M19.4 13.5c.1-.5.1-1 .1-1.5s0-1-.1-1.5l2-1.5-2-3.5-2.4 1a7.6 7.6 0 0 0-2.6-1.5L14 2h-4l-.4 2.5A7.6 7.6 0 0 0 7 6L4.6 5l-2 3.5 2 1.5c-.1.5-.1 1-.1 1.5s0 1 .1 1.5l-2 1.5 2 3.5 2.4-1a7.6 7.6 0 0 0 2.6 1.5L10 22h4l.4-2.5a7.6 7.6 0 0 0 2.6-1.5l2.4 1 2-3.5-2-1.5ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z"/>',
        'users' => '<path d="M16 11a4 4 0 1 0-3.4-6.1A5 5 0 0 1 16 11ZM8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8 2c-2.7 0-8 1.3-8 4v2h16v-2c0-2.7-5.3-4-8-4ZM8 14c-3.1 0-8 1.5-8 4v2h6v-2c0-1.4.8-2.7 2.2-3.7L8 14Z"/>',
        'user-check' => '<path d="M10 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-8 2.2-8 5v3h12v-2.1c0-1.4.5-2.8 1.4-3.8A11.6 11.6 0 0 0 10 14Zm11.7 2.3-1.4-1.4-3.8 3.8-1.8-1.8-1.4 1.4 3.2 3.2 5.2-5.2Z"/>',
        'user-clock' => '<path d="M10 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-8 2.2-8 5v3h10.4A6.8 6.8 0 0 1 12 20c0-2 .8-3.8 2-5.1A12.2 12.2 0 0 0 10 14Zm7.5 0a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11Zm.5 2.5v3.2l2.4 1.4-.8 1.3-3.1-1.8v-4.1H18Z"/>',
        'user-x' => '<path d="M10 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-8 2.2-8 5v3h11.6a7 7 0 0 1 .6-6.5A12 12 0 0 0 10 14Zm11.4 2-1.4-1.4-2.1 2.1-2.1-2.1-1.4 1.4 2.1 2.1-2.1 2.1 1.4 1.4 2.1-2.1 2.1 2.1 1.4-1.4-2.1-2.1 2.1-2.1Z"/>',
        'pages' => '<path d="M6 2h9l5 5v15H6V2Zm8 1.5V8h4.5L14 3.5ZM8 12h8v2H8v-2Zm0 4h8v2H8v-2Z"/>',
        'download' => '<path d="M11 3h2v9l3.3-3.3 1.4 1.4L12 15.8l-5.7-5.7 1.4-1.4L11 12V3ZM5 19h14v-4h2v6H3v-6h2v4Z"/>',
        'file-csv' => '<path d="M6 2h9l5 5v15H6V2Zm8 1.5V8h4.5L14 3.5ZM8 12h8v2H8v-2Zm0 4h5v2H8v-2Zm8 0h2v2h-2v-2Z"/>',
        'plus' => '<path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5Z"/>',
        'upload' => '<path d="M11 16h2V7.8l3.3 3.3 1.4-1.4L12 4 6.3 9.7l1.4 1.4L11 7.8V16ZM5 19h14v-4h2v6H3v-6h2v4Z"/>',
        'tag' => '<path d="M3 4h9l9 9-8 8-9-9V4Zm2 2v5.2l8 8 5.2-5.2-8-8H5Zm3.5 4A1.5 1.5 0 1 0 8.5 7a1.5 1.5 0 0 0 0 3Z"/>',
        'folder' => '<path d="M3 5h7l2 2h9v14H3V5Zm2 4v10h16V9H5Z"/>',
        'file-plus' => '<path d="M6 2h9l5 5v15H6V2Zm8 1.5V8h4.5L14 3.5ZM11 11h2v3h3v2h-3v3h-2v-3H8v-2h3v-3Z"/>',
        'rotate-ccw' => '<path d="M7 7.8V4H5v7h7V9H8.5A6 6 0 1 1 6 14H4a8 8 0 1 0 3-6.2Z"/>',
        'funnel' => '<path d="M3 5h18l-7 8v6l-4 2v-8L3 5Zm4.4 2 4.6 5.3 4.6-5.3H7.4Z"/>',
        'x' => '<path d="m6.4 5 5.6 5.6L17.6 5 19 6.4 13.4 12l5.6 5.6-1.4 1.4-5.6-5.6L6.4 19 5 17.6l5.6-5.6L5 6.4 6.4 5Z"/>',
        'edit' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15.9 8 17l1.1-4 9.4-10.5Z"/>',
        'trash' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 6 18 20a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6M14 11v6"/>',
        'eye' => '<path d="M12 5c5 0 8.7 4.2 10 7-1.3 2.8-5 7-10 7S3.3 14.8 2 12c1.3-2.8 5-7 10-7Zm0 2c-3.5 0-6.3 2.6-7.7 5 1.4 2.4 4.2 5 7.7 5s6.3-2.6 7.7-5C18.3 9.6 15.5 7 12 7Zm0 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z"/>',
        'printer' => '<path d="M7 3h10v5H7V3Zm-2 7h14a3 3 0 0 1 3 3v5h-4v3H6v-3H2v-5a3 3 0 0 1 3-3Zm3 7v2h8v-4H8v2Zm11-4a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/>',
        'chevron' => '<path d="m8.6 8.6 3.4 3.4 3.4-3.4 1.4 1.4-4.8 4.8L7.2 10l1.4-1.4Z"/>',
        'chevron-right' => '<path d="m9.3 5.3 1.4-1.4 6.1 6.1-6.1 6.1-1.4-1.4 4.7-4.7-4.7-4.7Z"/>',
        'chevron-left' => '<path d="m14.7 5.3-1.4-1.4-6.1 6.1 6.1 6.1 1.4-1.4-4.7-4.7 4.7-4.7Z"/>',
        'chevrons-right' => '<path d="m6.3 5.3 1.4-1.4 6.1 6.1-6.1 6.1-1.4-1.4 4.7-4.7-4.7-4.7Zm6 0 1.4-1.4 6.1 6.1-6.1 6.1-1.4-1.4 4.7-4.7-4.7-4.7Z"/>',
        'chevrons-left' => '<path d="m17.7 5.3-1.4-1.4-6.1 6.1 6.1 6.1 1.4-1.4-4.7-4.7 4.7-4.7Zm-6 0-1.4-1.4-6.1 6.1 6.1 6.1 1.4-1.4L7 10l4.7-4.7Z"/>',
        'search' => '<path d="M10.5 4a6.5 6.5 0 1 0 4.1 11.5l4 4 1.4-1.4-4-4A6.5 6.5 0 0 0 10.5 4Zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z"/>',
        'menu' => '<path d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h18v2H3v-2Z"/>',
        'moon' => '<path d="M21 14.7A8 8 0 0 1 9.3 3 9 9 0 1 0 21 14.7Z"/>',
        'sun' => '<path d="M6.8 4.4 5.4 3 4 4.4l1.4 1.4 1.4-1.4ZM13 1h-2v3h2V1Zm7 3.4L18.6 3l-1.4 1.4 1.4 1.4L20 4.4ZM12 6a6 6 0 1 0 0 12A6 6 0 0 0 12 6Zm0 10a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm8 1.6-1.4-1.4-1.4 1.4 1.4 1.4 1.4-1.4ZM13 20h-2v3h2v-3Zm-7.6-3.8L4 17.6 5.4 19l1.4-1.4-1.4-1.4ZM1 11v2h3v-2H1Zm19 0v2h3v-2h-3Z"/>',
        'user' => '<path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-8 2.2-8 5v3h16v-3c0-2.8-3.6-5-8-5Z"/>',
        'logout' => '<path d="M10 3H4v18h6v-2H6V5h4V3Zm6.6 5.6L15.2 10H9v2h6.2l1.4 1.4L20 10l-3.4-3.4Z"/>',
        'palette' => '<path d="M12 3a9 9 0 0 0 0 18h1.5a2.5 2.5 0 0 0 1.8-4.2 1 1 0 0 1 .7-1.8h1a5 5 0 0 0 5-5c0-3.9-4-7-10-7ZM6.5 11A1.5 1.5 0 1 1 8 9.5 1.5 1.5 0 0 1 6.5 11Zm3-4A1.5 1.5 0 1 1 11 5.5 1.5 1.5 0 0 1 9.5 7Zm5 0A1.5 1.5 0 1 1 16 5.5 1.5 1.5 0 0 1 14.5 7Zm3 4A1.5 1.5 0 1 1 19 9.5 1.5 1.5 0 0 1 17.5 11Z"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
    {!! $icons[$name] ?? $icons['dashboard'] !!}
</svg>
