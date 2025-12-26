<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap seodevi-wrap">

    <?php require_once plugin_dir_path(__FILE__) . '../components/header.php'; ?>

    <div class="seodevi-dashboard">

        <!-- HEADER -->
        <div class="seodevi-dashboard-header">
            <h1>🚀 SEODevi Link Analyzer</h1>
            <p>Analyze internal & external links using Node.js crawler</p>
        </div>

        <!-- WEBSITE INPUT -->
        <div id="website-selection" class="seodevi-selection-card">
            <h2>Analyze Website</h2>
            <div class="seodevi-input-group">
                <input type="url" id="new-website-url" class="seodevi-input" placeholder="https://example.com">
                <button class="seodevi-btn seodevi-btn-primary" onclick="startAnalysis()">
                    Analyze
                </button>
            </div>
        </div>

        <!-- ANALYSIS -->
        <div id="website-analysis" style="display:none">

            <div class="seodevi-website-header">
                <div>
                    <h2 id="current-website-title"></h2>
                    <p id="current-website-url"></p>
                </div>
                <button class="seodevi-btn seodevi-btn-secondary" onclick="goBack()">← Back</button>
            </div>

            <!-- LOADING -->
            <div id="analysis-loading" class="seodevi-loading" style="display:none">
                <div class="seodevi-spinner"></div>
                <p>Analyzing website…</p>
            </div>

            <!-- RESULTS -->
            <div id="analysis-results" style="display:none">

                <!-- STATS -->
                <div class="seodevi-stats-overview" id="stats"></div>

                <div class="seodevi-dashboard-content">

                    <!-- INTERNAL -->
                    <div class="seodevi-dashboard-card">
                        <div class="seodevi-card-header">
                            <h2>🔗 Internal Links</h2>
                        </div>
                        <div class="seodevi-card-body" id="internal-links"></div>
                    </div>

                    <!-- EXTERNAL -->
                    <div class="seodevi-dashboard-card">
                        <div class="seodevi-card-header">
                            <h2>🌍 External Links</h2>
                        </div>
                        <div class="seodevi-card-body" id="external-links"></div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
const API_URL = "http://localhost:5000/api/fetchurl/links";

function startAnalysis() {
    const url = document.getElementById('new-website-url').value.trim();
    if (!url) return alert("Enter a valid URL");

    document.getElementById('website-selection').style.display = "none";
    document.getElementById('website-analysis').style.display = "block";
    document.getElementById('analysis-loading').style.display = "block";
    document.getElementById('analysis-results').style.display = "none";

    document.getElementById('current-website-title').innerText =
        "Analyzing: " + new URL(url).hostname;
    document.getElementById('current-website-url').innerText = url;

    fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ website: url })
    })
    .then(res => res.json())
    .then(renderResult)
    .catch(() => alert("Analysis failed"));
}

function renderResult(data) {
    document.getElementById('analysis-loading').style.display = "none";
    document.getElementById('analysis-results').style.display = "block";

    document.getElementById('stats').innerHTML = `
        <div class="seodevi-stat-card">
            <h3>${data.stats.pages_crawled}</h3>
            <p>Pages Crawled</p>
        </div>
        <div class="seodevi-stat-card">
            <h3>${data.stats.internal_links_found}</h3>
            <p>Internal Links</p>
        </div>
        <div class="seodevi-stat-card">
            <h3>${data.stats.external_links_found}</h3>
            <p>External Links</p>
        </div>
    `;

    renderLinks("internal-links", data.internal_links);
    renderLinks("external-links", data.external_links);
}

function renderLinks(id, links) {
    const container = document.getElementById(id);
    if (!links.length) {
        container.innerHTML = "<p>No links found</p>";
        return;
    }

    container.innerHTML = links.slice(0, 20).map(link => `
        <div class="seodevi-link-item">
            <a href="${link}" target="_blank">${link}</a>
        </div>
    `).join("");
}

function goBack() {
    document.getElementById('website-analysis').style.display = "none";
    document.getElementById('website-selection').style.display = "block";
}
</script>
