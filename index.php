<?php

$bot_token = '7537566063:AAEZRguQYeJ9ZQ3zDJTOll_HXtuYdKdM8ds';

$group1 = '-1002509155667';
$group2 = '-1002876941832';

$indexFile1 = 'last_index_group1.txt';
$indexFile2 = 'last_index_group2.txt';

// قروب 1
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
    ]
];

// قروب 2
$group2_messages = [
    [
        'type' => 'photo',
        'photo' => 'https://t.me/fx2data/48',
        'caption' => "📛 في حال وجود أشخاص مخالفين القوانين\nكل اللي عليك ترسل كلمة: *أدمن*\n\n[@malke711s]"
    ]
];

// دالة للحصول على الفهرس التالي
function getNextIndex($file, $max) {
    $index = 0;
    if (file_exists($file)) {
        $index = (int) file_get_contents($file);
        $index = ($index + 1) % $max;
    }
    file_put_contents($file, $index);
    return $index;
}

// 🟢 إرسال لقروب 1
$index1 = getNextIndex($indexFile1, count($group1_messages));
$msg1 = $group1_messages[$index1];

$params1 = [
    'chat_id' => $group1,
    'text' => $msg1['text'],
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode($msg1['keyboard'], JSON_UNESCAPED_UNICODE)
];

file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?" . http_build_query($params1));

// 🔵 إرسال لقروب 2
$index2 = getNextIndex($indexFile2, count($group2_messages));
$msg2 = $group2_messages[$index2];

if (isset($msg2['type']) && $msg2['type'] === 'photo') {
    $params2 = [
        'chat_id' => $group2,
        'photo' => $msg2['photo'],
        'caption' => $msg2['caption'],
        'parse_mode' => 'Markdown'
    ];
    file_get_contents("https://api.telegram.org/bot$bot_token/sendPhoto?" . http_build_query($params2));
} else {
    $params2 = [
        'chat_id' => $group2,
        'text' => $msg2['text'],
        'parse_mode' => 'HTML'
    ];
    file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?" . http_build_query($params2));
}
