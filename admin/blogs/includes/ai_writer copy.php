<?php

function generateAIArticle($title, $apiKey = null)
{
    // If API key exists → try AI first
    if (!empty($apiKey)) {

        $ai = tryOpenAI($title, $apiKey);

        // If AI worked, return it
        if ($ai !== false) {
            return $ai;
        }
    }

    // 🔥 FALLBACK SYSTEM (OFFLINE MODE)
    return generateOfflineArticle($title);
}


/* =========================
   OPENAI ATTEMPT
========================= */
function tryOpenAI($title, $apiKey)
{
    $prompt = "Write a professional SEO blog in HTML format about: $title. Use <h2>, <h3>, <p>. No markdown.";

    $payload = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "You are an expert SEO writer."],
            ["role" => "user", "content" => $prompt]
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
        CURLOPT_TIMEOUT => 40
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    // ❌ If quota / error → fallback
    if (!isset($result['choices'][0]['message']['content'])) {
        return false;
    }

    return cleanAIOutput($result['choices'][0]['message']['content']);
}


/* =========================
   OFFLINE BLOG GENERATOR
========================= */
function generateOfflineArticle($title)
{
    $safeTitle = htmlspecialchars($title);

    $content = "";

    $content .= "<h2>Introduction</h2>";
    $content .= "<p>{$safeTitle} is an important topic in modern engineering and development. Understanding it helps improve knowledge and practical skills.</p>";

    $content .= "<h2>Key Overview</h2>";
    $content .= "<p>This topic covers essential concepts, basic principles, and real-world applications that are useful in daily operations and technical work.</p>";

    $content .= "<h2>Importance in Practice</h2>";
    $content .= "<p>In real field scenarios, {$safeTitle} plays a critical role in improving efficiency, safety, and performance of systems.</p>";

    $content .= "<h2>Technical Insight</h2>";
    $content .= "<p>From an engineering perspective, this subject requires attention to detail, proper planning, and regular maintenance practices.</p>";

    $content .= "<h2>Conclusion</h2>";
    $content .= "<p>Overall, {$safeTitle} is a valuable topic that helps professionals improve both theoretical understanding and practical execution.</p>";

    return $content;
}


/* =========================
   CLEAN AI OUTPUT
========================= */
function cleanAIOutput($html)
{
    $html = preg_replace('/```html|```/', '', $html);
    $html = trim($html);
    return $html;
}