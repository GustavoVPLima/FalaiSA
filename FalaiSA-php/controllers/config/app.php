<?php
// Configurações gerais da aplicação

return [
    'name' => 'FalaiSA',
    'timezone' => 'America/Sao_Paulo',
    
    'upload_folders' => [
        'usuarios' => 'static/uploads/usuarios',
        'comunidades' => 'static/uploads/comunidades',
        'chat' => 'static/uploads/chat'
    ],
    
    'max_file_size' => 10 * 1024 * 1024, // 10MB
    
    'allowed_extensions' => [
        'imagem' => ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
        'documento' => ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar'],
        'video' => ['mp4', 'avi', 'mov', 'mkv']
    ]
];
