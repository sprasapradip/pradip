<?php

function generateAIArticle($title, $apiKey = null)
{
    $title = trim($title);

    /* =========================
       1. TRY OPENAI (IF KEY EXISTS)
    ========================== */
    if (!empty($apiKey)) {

        $ai = tryOpenAI($title, $apiKey);

        if ($ai !== false) {
            return $ai;
        }
    }

    /* =========================
       2. TRY SIMPLE FREE WEB MODE (optional)
       (disabled by default for safety)
    ========================== */


    /* =========================
       3. LOCAL AI ENGINE (FREE CHATGPT STYLE)
    ========================== */
    return localChatGPTWriter($title);
}


/* =========================
   OPENAI ATTEMPT
========================= */
function tryOpenAI($title, $apiKey)
{
    $payload = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "You are a professional SEO blog writer."],
            ["role" => "user", "content" => "Write SEO blog in HTML about: $title"]
        ],
        "temperature" => 0.7,
        "max_tokens" => 1800
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        return false;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    return $result['choices'][0]['message']['content'] ?? false;
}


/* =========================
   🔥 LOCAL CHATGPT STYLE WRITER
========================= */
function localChatGPTWriter($title)
{
    $t = htmlspecialchars($title);

    return "
<h2>Introduction</h2>
<p>{$t} is an important topic widely used in modern systems and industries. It plays a key role in improving performance and understanding core principles.</p>

<h2>What is {$t}?</h2>
<p>{$t} refers to a structured concept that helps improve efficiency and system understanding in real-world applications.</p>

<h2>Key Features</h2>
<ul>
<li>Easy to understand structure</li>
<li>Widely used in practical systems</li>
<li>Improves operational efficiency</li>
<li>Helps in technical decision making</li>
</ul>

<h2>Applications</h2>
<p>{$t} is used in engineering, technology, infrastructure, and many other fields where optimization is important.</p>

<h2>Advantages</h2>
<p>It provides better performance, improved safety, and more reliable results in practical usage.</p>

<h2>Conclusion</h2>
<p>In conclusion, {$t} is a valuable concept that helps both beginners and professionals in real-world applications.</p>
";
}