<?php

return [
    'supported_locales' => [
        'en' => [
            'name' => 'English',
            'flag' => '🇬🇧',
            'native' => 'English',
            'rtl' => false,
        ],
        'bg' => [
            'name' => 'Bulgarian',
            'flag' => '🇧🇬',
            'native' => 'Български',
            'rtl' => false,
        ],
        'es' => [
            'name' => 'Spanish',
            'flag' => '🇪🇸',
            'native' => 'Español',
            'rtl' => false,
        ],
        'fr' => [
            'name' => 'French',
            'flag' => '🇫🇷',
            'native' => 'Français',
            'rtl' => false,
        ],
        'de' => [
            'name' => 'German',
            'flag' => '🇩🇪',
            'native' => 'Deutsch',
            'rtl' => false,
        ],
    ],
    
    'default_locale' => 'en',
    
    'translation_locales' => ['bg', 'es', 'fr', 'de'], // Locales available for translation (excluding default)
];