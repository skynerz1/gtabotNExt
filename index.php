<?php

$bot_token = '7537566063:AAEZRguQYeJ9ZQ3zDJTOll_HXtuYdKdM8ds';

// معرف القروبين
$group1 = '-1002566159762';
$group2 = '-1002876941832';

// مسارات الملفات لتخزين آخر فهرس تم إرساله
$indexFile1 = 'last_index_group1.txt';
$indexFile2 = 'last_index_group2.txt';

// رسائل قروب 1
$group1_messages = [
    [
        'text' => "قوانين القروب الرجاء الالتزام فيها",
        'keyboard' => [
            'inline_keyboard' => [
                [['text' => '📢 بوت الدعم', 'url' => 'https://t.me/itddbot']],
                [['text' => '📜 قوانين', 'url' => 'https://t.me/fx2link/3']]
            ]
        ]
    ],
    [
        'text' => "قناه الوصوف الخاصه بنا - الرسبونات خذ لك اطلاله عليها",
        'keyboard' => [
            'inline_keyboard' => [
                [['text' => '💬 القناه', 'url' => 'https://t.me/fx2gta5']],
            ]
        ]
    ],

];

// رسائل قروب 2
$group2_messages = [
    [
        'text' => "🚀 [قروب 2] الرسالة الأولى",
        'keyboard' => [
            'inline_keyboard' => [
                [['text' => '📢 قناة 2', 'url' => 'https://t.me/group2_channel']],
                [['text' => '📜 قوانين 2', 'url' => 'https://t.me/group2_rules']]
            ]
        ]
    ],
    [
        'text' => "✅ [قروب 2] الرسالة الثانية",
        'keyboard' => [
            'inline_keyboard' => [
                [['text' => '💬 شات 2', 'url' => 'https://t.me/group2_chat']],
                [['text' => '📝 الرتب', 'url' => 'https://t.me/group2_roles']]
            ]
        ]
    ],
    [
        'text' => "📌 [قروب 2] الرسالة الثالثة",
        'keyboard' => [
            'inline_keyboard' => [
                [['text' => '🎮 المهام', 'url' => 'https://t.me/group2_tasks']],
                [['text' => '📊 نقاط', 'url' => 'https://t.me/group2_points']]
            ]
        ]
    ]
];

// دالة للحصول على الفهرس التالي بالتسلسل
function getNextIndex($file, $max) {
    $index = 0;
    if (file_exists($file)) {
        $index = (int) file_get_contents($file);
        $index = ($index + 1) % $max;
    }
    file_put_contents($file, $index);
    return $index;
}

// إرسال الرسالة لقروب 1
$index1 = getNextIndex($indexFile1, count($group1_messages));
$msg1 = $group1_messages[$index1];

file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?" . http_build_query([
    'chat_id' => $group1,
    'text' => $msg1['text'],
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode($msg1['keyboard'], JSON_UNESCAPED_UNICODE)
]));

// إرسال الرسالة لقروب 2
$index2 = getNextIndex($indexFile2, count($group2_messages));
$msg2 = $group2_messages[$index2];

file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?" . http_build_query([
    'chat_id' => $group2,
    'text' => $msg2['text'],
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode($msg2['keyboard'], JSON_UNESCAPED_UNICODE)
]));
