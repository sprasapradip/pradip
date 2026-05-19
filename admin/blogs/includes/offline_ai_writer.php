<?php

function generateBlog($title)
{
    $title = trim($title);

    return [
        "title" => $title,
        "meta_title" => generateMetaTitle($title),
        "meta_description" => generateMetaDescription($title),
        "keywords" => generateKeywords($title),
        "content" => generateArticle($title),
        "reading_time" => estimateReadingTime($title)
    ];
}


/* =========================
   META TITLE
========================= */
function generateMetaTitle($title)
{
    return $title . " | Complete Guide, Tips & Information";
}


/* =========================
   META DESCRIPTION
========================= */
function generateMetaDescription($title)
{
    return "Learn everything about " . $title . ". Complete guide with key points, explanation, and practical insights.";
}


/* =========================
   KEYWORDS GENERATOR
========================= */
function generateKeywords($title)
{
    $base = strtolower($title);

    $words = explode(" ", $base);

    $keywords = array_merge($words, [
        "guide",
        "tutorial",
        "explained",
        "importance",
        "uses",
        "benefits"
    ]);

    return implode(", ", array_unique($keywords));
}


/* =========================
   MAIN ARTICLE GENERATOR
========================= */
function generateArticle($title)
{
    $t = htmlspecialchars($title);

    return "
<h2>Introduction</h2>
<p>{$t} is an important topic that plays a key role in modern systems and daily applications. Understanding it helps improve knowledge and practical skills.</p>

<h2>What is {$t}?</h2>
<p>{$t} refers to a concept or system that is widely used in real-world scenarios. It helps improve efficiency and performance.</p>

<h2>Importance of {$t}</h2>
<p>The importance of {$t} lies in its ability to improve processes, safety, and productivity in various fields.</p>

<h2>Key Benefits</h2>
<ul>
<li>Improves efficiency</li>
<li>Enhances performance</li>
<li>Reduces operational issues</li>
<li>Supports better decision making</li>
</ul>

<h2>Practical Applications</h2>
<p>{$t} is used in many industries including engineering, technology, and infrastructure systems.</p>

<h2>Conclusion</h2>
<p>In conclusion, {$t} is a valuable topic that helps in both learning and practical implementation.</p>
";
}


/* =========================
   READING TIME
========================= */
function estimateReadingTime($title)
{
    return rand(3, 7) . " min read";
}