<?php

$botToken = '7595641297:AAFoFEPAZvJdsyYBccAPdtVzmaZiN4FJjZI';
$apiURL = "https://api.telegram.org/bot$botToken/";
$adminGroupId = '-1002788756551';
$groupId = '-1002509155667'; // قروب التقديمات/الترقيات

function sendMessage($chat_id, $text, $keyboard = null) {
    global $apiURL;
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    if ($keyboard) {
        $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
    }
    file_get_contents($apiURL . "sendMessage?" . http_build_query($data));
}

function saveUserStep($userId, $data) {
    if (!is_dir("sessions")) mkdir("sessions");
    file_put_contents("sessions/$userId.json", json_encode($data));
}

function getUserStep($userId) {
    $file = "sessions/$userId.json";
    return file_exists($file) ? json_decode(file_get_contents($file), true) : null;
}

function deleteUserStep($userId) {
    $file = "sessions/$userId.json";
    if (file_exists($file)) unlink($file);
}



$questionsMap = [
    "مشرف" => [
        "1️⃣ ⌯ <b>اسمك الكامل؟</b>",
        "2️⃣ ⌯ <b>كم عمرك؟</b>",
        "3️⃣ ⌯ <b>هل أنت مشرف في قروبات أخرى؟</b>",
        "4️⃣ ⌯ <b>هل سبق وكنت مشرف؟ اذكر التفاصيل:</b>",
        "5️⃣ ⌯ <b>كيف تصف خبرتك في الإشراف؟</b>",
        "6️⃣ ⌯ <b>هل ستكون متفاعل يوميًا؟ كم ساعة تقريبًا؟</b>",
        "7️⃣ ⌯ <b>هل لديك خبرة سابقة؟ وما نوعها؟</b>",
        "8️⃣ ⌯ <b>كم عدد رسايلك في القروب تقريبًا؟</b>",
        "9️⃣ ⌯ <b>أقسم بأن لا أسبب تخريب وأن ألتزم بالقوانين. هل توافق؟</b>"
    ],
    "مميز" => [
        "📝 شسمك؟",
        "🎂 كم عمرك؟",
        "💬 كم عدد رسايلك في القروب؟",
        "📆 كم تفاعلك اليومي تقريبا؟"
    ],
    "مدي1ر" => [
        "📝 اكتب اسمك الكامل:",
        "🎂 كم عمرك؟",
        "📚 هل عندك خبرة بالإدارة؟",
        "🧩 كم مره كنت مدير أو مالك قروب؟",
        "⏰ هل تقدر تتفرغ يوميًا؟"
    ],
    "مدير" => [
        "التقديم مغلق حاليا اضغط /start",
    ],
    "مميز مؤقت" => [
    "📝 شسمك؟",
    "🎂 كم عمرك؟",
    "💬 كم عدد رسايلك في القروب؟",
    "📆 كم تفاعلك اليومي تقريبا؟"
],

];

// تعريف رتب الترقية وتفاصيلها
$upgradeRanks = [
    'عضو متفاعل ⛀' => [
        'next_rank' => 'عضو سبشل ⛁',
        'required_msgs' => 5300,
        'custom_title' => 'عضو سبشل ⛁',
    ],
    'عضو سبشل ⛁' => [
        'next_rank' => 'عضو جــــبّار ⛂',
        'required_msgs' => 7500,
        'custom_title' => 'عضو جــــبّار ⛂',
    ],
    '𝑯𝒆𝒍𝒑𝒆𝒓' => [
        'next_rank' => '🧹 Message Cleaner',
        'required_msgs' => 3600,
        'custom_title' => '【★】ｍｓｇ ｃｌｎ【★】',
        'command' => '/gestore'
    ],
    '🧹 Message Cleaner' => [
        'next_rank' => '🔇 The Silencer',
        'required_msgs' => 7200,
        'custom_title' => '𝓂𝑢𝗍𝐞𝘳',
        'command' => '/muter'
    ],
    '🔇 The Silencer' => [
        'next_rank' => '𝖠𝖣𝖬𝖨𝖭',
        'required_msgs' => 12300,
        'custom_title' => '𝖠𝖣𝖬𝖨𝖭',
        'command' => '/mod'
    ]
];

// استقبال التحديثات
$update = json_decode(file_get_contents('php://input'), true);
$message = $update['message'] ?? null;
$callback = $update['callback_query'] ?? null;

// منع استقبال أوامر من القروبات (مشاركة القروبات)


// التحكم في ضغط الأزرار من الخاص أو قروب الادمن
if ($callback) {
    $callbackChatId = $callback['message']['chat']['id'];
    if ($callback['message']['chat']['type'] !== 'private' && $callbackChatId != $adminGroupId) {
        return;
    }
}

// ==== إضافة دعم /start=upgrade ====

if ($message) {
    $chat_id = $message['chat']['id'];
    $user_id = $message['from']['id'];
    $text = $message['text'] ?? '';

    if ($message && isset($message['text']) && $message['chat']['id'] == $adminGroupId) {
    $text = trim($message['text']);

    if (preg_match('/^تنزيل مشرف (\d{5,20})$/u', $text, $match)) {
        $targetId = $match[1];

        // إزالة صلاحيات المشرف
        file_get_contents($apiURL . "promoteChatMember?" . http_build_query([
            'chat_id' => $groupId,
            'user_id' => $targetId,
            'can_change_info' => false,
            'can_post_messages' => false,
            'can_edit_messages' => false,
            'can_delete_messages' => false,
            'can_invite_users' => false,
            'can_restrict_members' => false,
            'can_pin_messages' => false,
            'can_promote_members' => false,
            'can_manage_chat' => false,
            'can_manage_video_chats' => false,
            'is_anonymous' => false
        ]));

        // أوامر إزالة الرتب
        $commands = "/unhelper $targetId\n/ungestore $targetId\n/unmuter $targetId\n/unmod $targetId";
        sendMessage($adminGroupId, "🗑️ تم إزالة المشرف صاحب ID: <code>$targetId</code>\n\nاستخدم الأوامر التالية لحذف الرتب:\n<code>$commands</code>");
    }
}


    // تعامُل مع /start مع باراميتر
    if (strpos($text, "/start") === 0) {
$parts = explode('=', $text);
$param = $parts[1] ?? null;


        if ($param === "upgrade") {
            $welcome = "✨ أهلاً بك في بوابة تطوير الرتب ✨\n\nاختر رتبتك الحالية👇";

            // بناء أزرار الرتب من $upgradeRanks
            $keyboard = [];
            foreach ($upgradeRanks as $currentRank => $info) {
                $keyboard[] = [['text' => $currentRank, 'callback_data' => "upgrade_current_$currentRank"]];
            }
            // أزرار أسفل
            $keyboard[] = [
                ['text' => '🔙 رجوع', 'callback_data' => 'back_to_start'],
                ['text' => 'أنا متأكد', 'callback_data' => 'upgrade_confirm']
            ];

            sendMessage($chat_id, $welcome, $keyboard);
            return;
        }

        // ... الكود القديم للتقديمات ...
        if ($param === "tos") {
            $tosText = "شروط المميزين ‼️

١- تضيف ٢٠ شخص للقروب
٢- تكون متفاعل بشكل مستمر
٣- تحط شعار القروب

المميزات:
- ارسال الفيديو 📺
- ارسال صوتیات 🔊
- ارسال الملصقات و GIF 🏷
- تقدر تتكلم والشات مقفل 💌

اي استفسار عن الرتب : @YYV_l

=======================================

👉 <a href='https://t.me/fx2role/5'>شروط المميزين من هنا — اضغط</a>";

            sendMessage($chat_id, $tosText, [
                [['text' => '🔙 رجوع', 'callback_data' => 'back_to_start']]
            ]);
            return;
        }

        // start الافتراضية
        $welcome = "✨ أهلاً بك في بوابة التقديمات الرسمية ✨\n\nاختر الرتبة التي ترغب بالتقديم عليها 👇";
sendMessage($chat_id, $welcome, [
    [
        ['text' => "🎖️ ⌯ التقديم على 𝑴𝒖𝒔𝒉𝒓𝒊𝒇", 'callback_data' => "apply_mod"]
    ],
    [
        ['text' => "💎 ⌯ التقديم على 𝑴𝒖𝒎𝒂𝒚𝒚𝒂𝒛", 'callback_data' => "apply_vip"]
    ],
    [
        ['text' => "♛ ⌯ التقديم على عضو مميز مؤقت", 'callback_data' => "apply_vip_temp"]
    ],
    [
        ['text' => "👑 ⌯ التقديم على 𝑴𝒖𝒅𝒊𝒓", 'callback_data' => "apply_admin"]
    ],
    [
        ['text' => "📜 الشروط والفوائد", 'callback_data' => "show_tos"]
    ]
]);

        return;
    }
}

// التعامل مع الجلسات الحالية للتقديمات (كودك القديم)
$session = getUserStep($user_id);
if ($session && isset($session['step'], $session['role'])) {
    $role = $session['role'];
    $questions = $questionsMap[$role];
    $step = $session['step'];

    $session['answers'][$step] = $text;
    $step++;

    if ($step < count($questions)) {
        $session['step'] = $step;
        saveUserStep($user_id, $session);
        sendMessage($chat_id, $questions[$step]);
    } else {
        $msg = "📥 <b>طلب تقديم جديد:</b>\n\n";
        foreach ($questions as $i => $q) {
            $answer = $session['answers'][$i] ?? "❓غير معروف";
            $msg .= "🔹 <b>$q</b>\n$answer\n\n";
        }
        $msg .= "📌 الرتبة المطلوبة: <b>$role</b>\n";
        $msg .= "🆔 ID: <code>$user_id</code>";

        $inlineKeyboard = [[
            ['text' => "✅ قبول", 'callback_data' => "accept_{$user_id}_$role"],
            ['text' => "❌ رفض", 'callback_data' => "reject_{$user_id}"]
        ]];

        sendMessage($adminGroupId, $msg, $inlineKeyboard);
        sendMessage($chat_id, "✅ تم إرسال طلبك بنجاح! سيتم الرد عليك قريبًا.");
    }
}




// ==== التعامل مع الأزرار ====

if ($callback) {
    $data = $callback['data'];
    $chat_id = $callback['message']['chat']['id'];
    $user_id = $callback['from']['id'];

    // *** أزرار التقديمات القديمة ***
    if (strpos($data, "apply_") === 0) {
        $role = match ($data) {
        'apply_mod' => "مشرف",
        'apply_vip' => "مميز",
        'apply_vip_temp' => "مميز مؤقت",  // هنا
        'apply_admin' => "مدير",
        default => "غير معروف"  
        };

        $from = $callback['from'];
        $username = isset($from['username']) ? '@' . $from['username'] : 'لا يوجد';
        $firstName = $from['first_name'] ?? '';
        $lastName = $from['last_name'] ?? '';

        $questions = $questionsMap[$role];

        saveUserStep($callback['from']['id'], [
            'group' => null,
            'role' => $role,
            'step' => 0,
            'answers' => [],
            'username' => $username,
            'first_name' => $firstName,
            'last_name' => $lastName
        ]);

        sendMessage($user_id, $questions[0]);
        return;
    }

if (strpos($data, "accept_") === 0) {
    // قبول طلب التقديم
    $parts = explode("_", $data);
    $targetId = $parts[1];
    $roleName = $parts[2];
    $msg_id = $callback['message']['message_id'];
    $moderatorUsername = isset($callback['from']['username']) ? '@' . $callback['from']['username'] : 'مشرف';

    $session = getUserStep($targetId);
    $answersText = "";
    if ($session && isset($questionsMap[$roleName])) {
        $questions = $questionsMap[$roleName];
        foreach ($questions as $i => $q) {
            $answer = $session['answers'][$i] ?? "❓غير معروف";
            $answersText .= "🔹 <b>$q</b>\n$answer\n\n";
        }
    }

    $targetUsername = $session['username'] ?? 'لا يوجد';
    $fullName = trim(($session['first_name'] ?? '') . ' ' . ($session['last_name'] ?? ''));

    $text = "✅ <b>تم قبول هذا الطلب.</b>\n\n📋 <b>بيانات المقدم:</b>\n\n"
        . "$answersText"
        . "📌 الرتبة المطلوبة: <b>$roleName</b>\n"
        . "🆔 ID: <code>$targetId</code>\n"
        . "👤 الاسم: <b>$fullName</b>\n"
        . "🔗 يوزره: $targetUsername\n\n"
        . "☑️ بواسطة: $moderatorUsername";

    $editData = [
        'chat_id' => $chat_id,
        'message_id' => $msg_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => []])
    ];
    file_get_contents($apiURL . "editMessageText?" . http_build_query($editData));

    sendMessage($targetId, "🎉 <b>تم قبولك!</b>\n\n✅ تمت ترقيتك إلى: <b>$roleName</b>");

    // تنفيذ ترقية صلاحيات القروب (إذا مميز أو مشرف)
    if (in_array($roleName, ['مميز', 'مميز مؤقت', 'مشرف'])) {
        // ترقية بإعطاء صلاحية دعوة فقط
        $promoteData = [
            'chat_id' => $groupId,
            'user_id' => $targetId,
            'can_change_info' => false,
            'can_post_messages' => false,
            'can_edit_messages' => false,
            'can_delete_messages' => false,
            'can_invite_users' => true,
            'can_restrict_members' => false,
            'can_pin_messages' => false,
            'can_promote_members' => false,
            'can_manage_chat' => false,
            'can_manage_video_chats' => false,
            'is_anonymous' => false
        ];
        file_get_contents($apiURL . "promoteChatMember?" . http_build_query($promoteData));

        // تعيين اللقب حسب الرتبة
        $title = match ($roleName) {
            'مميز' => "عضو متفاعل ⛀",
            'مميز مؤقت' => "♛عضو مميز مؤقت♛",
            'مشرف' => "𝑯𝒆𝒍𝒑𝒆𝒓",
            default => ""
        };

        if ($title !== "") {
            file_get_contents($apiURL . "setChatAdministratorCustomTitle?" . http_build_query([
                'chat_id' => $groupId,
                'user_id' => $targetId,
                'custom_title' => $title
            ]));
        }

        // إرسال أمر الترقية المناسب في قروب التقديمات
        $command = null;
        if ($roleName === 'مميز' || $roleName === 'مميز مؤقت') {
            $command = '/free';
        } elseif ($roleName === 'مشرف') {
            $command = '/helper';
        }

        if ($command) {
            $cmdMessage = "لتكملة إعطاء الرتبة الرجاء اكتب في قروب قراند الأمر التالي:\n\n<code>$command $targetId</code>\n\nاضغط على الأمر للنسخ.";
            file_get_contents($apiURL . "sendMessage?" . http_build_query([
                'chat_id' => $adminGroupId,
                'text' => $cmdMessage,
                'parse_mode' => 'HTML'
            ]));
}

    }

    deleteUserStep($targetId);
    return;
}


    if (strpos($data, "reject_") === 0) {
        // رفض طلب التقديم
        $parts = explode("_", $data);
        $targetId = $parts[1];
        $msg_id = $callback['message']['message_id'];
        $moderatorUsername = isset($callback['from']['username']) ? '@' . $callback['from']['username'] : 'مشرف';

        $session = getUserStep($targetId);
        $roleName = $session['role'] ?? 'غير معروف';
        $answersText = "";
        if ($session && isset($questionsMap[$roleName])) {
            $questions = $questionsMap[$roleName];
            foreach ($questions as $i => $q) {
                $answer = $session['answers'][$i] ?? "❓غير معروف";
                $answersText .= "🔹 <b>$q</b>\n$answer\n\n";
            }
        }

        $targetUsername = $session['username'] ?? 'لا يوجد';
        $fullName = trim(($session['first_name'] ?? '') . ' ' . ($session['last_name'] ?? ''));

        $text = "❌ <b>تم رفض هذا الطلب.</b>\n\n📋 <b>بيانات المقدم:</b>\n\n"
            . "$answersText"
            . "📌 الرتبة المطلوبة: <b>$roleName</b>\n"
            . "🆔 ID: <code>$targetId</code>\n"
            . "👤 الاسم: <b>$fullName</b>\n"
            . "🔗 يوزره: $targetUsername\n\n"
            . "❎ بواسطة: $moderatorUsername";

        $editData = [
            'chat_id' => $chat_id,
            'message_id' => $msg_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ];
        file_get_contents($apiURL . "editMessageText?" . http_build_query($editData));
        sendMessage($targetId, "❌ <b>تم رفض طلبك.</b>\n\nبالتوفيق لك في المرات القادمة.");
        deleteUserStep($targetId);
        return;
    }

    // ======= هنا نبدأ بأزرار الترقية =======
    if (strpos($data, "upgrade_current_") === 0) {
        // المستخدم يختار رتبته الحالية للترقية
        $currentRank = urldecode(substr($data, strlen("upgrade_current_")));

        // حفظ الجلسة للترقية
        saveUserStep($user_id, [
            'upgrade_current_rank' => $currentRank
        ]);

        // نبحث بيانات الترقية المطلوبة
        if (isset($upgradeRanks[$currentRank])) {
            $nextRank = $upgradeRanks[$currentRank]['next_rank'];
            $requiredMsgs = $upgradeRanks[$currentRank]['required_msgs'];

            $msg = "رتبتك الحالية: <b>$currentRank</b>\n";
            $msg .= "لترقية إلى: <b>$nextRank</b>\n";
            $msg .= "المطلوب: <b>$requiredMsgs رسالة</b> في القروب\n\n";
            $msg .= "هل أنت مستعد للتقديم؟ اضغط 'أنا متأكد'.";

            sendMessage($chat_id, $msg, [
                [['text' => '🔙 رجوع', 'callback_data' => 'back_to_start']],
                [['text' => 'أنا متأكد', 'callback_data' => 'upgrade_confirm']]
            ]);
        } else {
            sendMessage($chat_id, "خطأ: الرتبة غير موجودة.");
        }
        return;
    }

    if ($data === 'upgrade_confirm') {
        $chat_id = $callback['message']['chat']['id'];
        $user_id = $callback['from']['id'];

        $session = getUserStep($user_id);
        if (!$session || !isset($session['upgrade_current_rank'])) {
            sendMessage($chat_id, "يرجى اختيار رتبتك الحالية أولاً.");
            return;
        }

        $currentRank = $session['upgrade_current_rank'];
        if (!isset($upgradeRanks[$currentRank])) {
            sendMessage($chat_id, "خطأ في بيانات الرتبة.");
            return;
        }

        $nextRank = $upgradeRanks[$currentRank]['next_rank'];
        $requiredMsgs = $upgradeRanks[$currentRank]['required_msgs'];

        // بيانات المستخدم
        $userInfo = $callback['from'];
        $username = isset($userInfo['username']) ? '@' . $userInfo['username'] : 'لا يوجد';
        $fullName = trim(($userInfo['first_name'] ?? '') . ' ' . ($userInfo['last_name'] ?? ''));

        $text = "📥 <b>طلب ترقية رتبة جديد:</b>\n\n";
        $text .= "👤 الاسم: <b>$fullName</b>\n";
        $text .= "🔗 يوزر: $username\n";
        $text .= "🆔 ID: <code>$user_id</code>\n";
        $text .= "🏷️ الرتبة الحالية: <b>$currentRank</b>\n";
        $text .= "🎯 الترقية إلى: <b>$nextRank</b>\n";
        $text .= "📊 الرسائل المطلوبة: <b>$requiredMsgs</b>\n\n";
        $text .= "✅ اضغط 'موافقة' للترقية أو 'رفض' لعدم الموافقة.";

        $inlineKeyboard = [[
            ['text' => "✅ موافقة", 'callback_data' => "upgrade_accept_{$user_id}_$currentRank"],
            ['text' => "❌ رفض", 'callback_data' => "upgrade_reject_{$user_id}_$currentRank"]
        ]];

        sendMessage($adminGroupId, $text, $inlineKeyboard);
        sendMessage($chat_id, "✅ تم إرسال طلب الترقية للادارة، سيتم الرد عليك قريبًا.");

        // حذف زر "أنا متأكد" حتى لا يمكن تكرار الطلب
        $editData = [
            'chat_id' => $chat_id,
            'message_id' => $callback['message']['message_id'],
            'text' => "✅ تم إرسال طلب الترقية للادارة، سيتم الرد عليك قريبًا.",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ];
        file_get_contents($apiURL . "editMessageText?" . http_build_query($editData));

        return;
    }

    if (strpos($data, "upgrade_accept_") === 0) {

        // الادمن وافق على الترقية
        $parts = explode("_", $data, 4);
        $targetId = $parts[2];
        $currentRank = $parts[3];
sendMessage($targetId, "🎉 <b>تهانينا!</b>\nتم ترقيتك من <b>$currentRank</b> إلى <b>$nextRank</b>.", null);

// تنفيذ الأمر البرمجي (إن وجد)
if (!empty($upgradeRanks[$currentRank]['command'])) {
    $command = $upgradeRanks[$currentRank]['command'];
    $formattedCommand = "$command $targetId";
    sendMessage($adminGroupId, "<b>📥 أمر الترقية التلقائي:</b>\n<code>$formattedCommand</code>", null);
}

        if (!isset($upgradeRanks[$currentRank])) return;

        $nextRank = $upgradeRanks[$currentRank]['next_rank'];
        $customTitle = $upgradeRanks[$currentRank]['custom_title'];

        // تعديل رسالة الادمن
        $msg_id = $callback['message']['message_id'];
        $moderatorUsername = isset($callback['from']['username']) ? '@' . $callback['from']['username'] : 'مشرف';

        $text = "✅ <b>تمت الموافقة على الترقية.</b>\n\n";
        $text .= "👤 ID: <code>$targetId</code>\n";
        $text .= "🏷️ من <b>$currentRank</b> إلى <b>$nextRank</b>\n";
        $text .= "☑️ بواسطة: $moderatorUsername";

        $editData = [
            'chat_id' => $chat_id,
            'message_id' => $msg_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ];
        file_get_contents($apiURL . "editMessageText?" . http_build_query($editData));

        // ترقية المستخدم في القروب
        $promoteData = [
            'chat_id' => $groupId,
            'user_id' => $targetId,
            'can_change_info' => false,
            'can_post_messages' => false,
            'can_edit_messages' => false,
            'can_delete_messages' => false,
            'can_invite_users' => true,
            'can_restrict_members' => false,
            'can_pin_messages' => false,
            'can_promote_members' => false,
            'can_manage_chat' => false,
            'can_manage_video_chats' => false,
            'is_anonymous' => false
        ];
        file_get_contents($apiURL . "promoteChatMember?" . http_build_query($promoteData));

        // تعيين اللقب
        file_get_contents($apiURL . "setChatAdministratorCustomTitle?" . http_build_query([
            'chat_id' => $groupId,
            'user_id' => $targetId,
            'custom_title' => $customTitle
        ]));

        // ارسال رسالة للمستخدم
        sendMessage($targetId, "🎉 <b>تهانينا!</b>\nتم ترقيتك من <b>$currentRank</b> إلى <b>$nextRank</b>.");

        deleteUserStep($targetId);
        return;
    }

    if (strpos($data, "upgrade_reject_") === 0) {
        // الادمن رفض الترقية
        $parts = explode("_", $data, 4);
        $targetId = $parts[2];
        $currentRank = $parts[3];

        $msg_id = $callback['message']['message_id'];
        $moderatorUsername = isset($callback['from']['username']) ? '@' . $callback['from']['username'] : 'مشرف';

        $text = "❌ <b>تم رفض طلب الترقية.</b>\n\n";
        $text .= "👤 ID: <code>$targetId</code>\n";
        $text .= "🏷️ الرتبة الحالية: <b>$currentRank</b>\n";
        $text .= "❎ بواسطة: $moderatorUsername";

        $editData = [
            'chat_id' => $chat_id,
            'message_id' => $msg_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ];
        file_get_contents($apiURL . "editMessageText?" . http_build_query($editData));

        sendMessage($targetId, "❌ <b>تم رفض طلب الترقية.</b>\n\nبالتوفيق في المرات القادمة.");
        deleteUserStep($targetId);
        return;
    }

    // باقي أزرارك القديمة:
    if ($data === "show_tos") {
        $tosText = "شروط المميزين ‼️

١- تضيف ٢٠ شخص للقروب
٢- تكون متفاعل بشكل مستمر
٣- تحط شعار القروب

المميزات:
- ارسال الفيديو 📺
- ارسال صوتیات 🔊
- ارسال الملصقات و GIF 🏷
- تقدر تتكلم والشات مقفل 💌

اي استفسار عن الرتب : @wgggk

===========================================================

👉 <a href='https://t.me/fx2role/5'>شروط المميزين من هنا — اضغط هنا</a>";

        file_get_contents($apiURL . "editMessageText?" . http_build_query([
            'chat_id' => $callback['message']['chat']['id'],
            'message_id' => $callback['message']['message_id'],
            'text' => $tosText,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🔙 رجوع', 'callback_data' => 'back_to_start']]
                ]
            ])
        ]));
        return;
    }

    if ($data === "back_to_start") {
        $welcome = "✨ أهلاً بك في بوابة التقديمات الرسمية ✨\n\nاختر الرتبة التي ترغب بالتقديم عليها 👇";

        file_get_contents($apiURL . "editMessageText?" . http_build_query([
            'chat_id' => $callback['message']['chat']['id'],
            'message_id' => $callback['message']['message_id'],
            'text' => $welcome,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => "🎖️ ⌯ التقديم على 𝑴𝒖𝒔𝒉𝒓𝒊𝒇", 'callback_data' => "apply_mod"]],
                    [['text' => "💎 ⌯ التقديم على 𝑴𝒖𝒎𝒂𝒚𝒚𝒂𝒛", 'callback_data' => "apply_vip"]],
                    [['text' => "👑 ⌯ التقديم على 𝑴𝒖𝒅𝒊𝒓", 'callback_data' => "apply_admin"]],
                    [['text' => "📜 الشروط والفوائد", 'callback_data' => "show_tos"]]
                ]
            ])
        ]));
        return;
    }
}
