
<?php
require_once "../session_check.php";

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Unauthorized access.");
}

if (empty($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Events | Student HUB</title>

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{min-height:100vh;background:linear-gradient(135deg,#87ceeb 0%,#dff6ff 100%);font-family:"Segoe UI",Arial,sans-serif;color:#202124;display:flex;flex-direction:column}
.header{background:rgba(255,255,255,.97);display:flex;align-items:center;justify-content:space-between;padding:10px 5%;gap:25px;position:sticky;top:0;z-index:1000;box-shadow:0 4px 15px #0002}
.logo img{width:100px;height:auto;display:block}
nav{flex:1;background:#fffde7;border-radius:40px;padding:10px 18px;margin:0 10px;border:1px solid #0a1d8f10}
nav ul{display:flex;justify-content:space-evenly;align-items:center;list-style:none;gap:10px}
nav a{display:block;text-decoration:none;color:#0a1d8f;font-size:16px;font-weight:700;padding:10px 12px;border-radius:20px;transition:.2s}
nav a:hover{color:white;background:#0a1d8f}
main{flex:1;width:min(1450px,96%);margin:0 auto;padding:35px 20px 45px}
.page-heading{margin-bottom:24px}
.eyebrow{color:#3158d8;font-size:13px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:8px}
.page-heading h1{color:#172d4d;font-size:clamp(28px,3vw,38px);margin-bottom:10px}
.page-heading p,.panel-heading p{color:#667b99;line-height:1.6}
.notice{margin-bottom:22px;padding:14px 18px;border-radius:10px;font-weight:600}
.notice.success{color:#17643a;background:#e9f8ef;border:1px solid #a7e4bb}
.notice.error{color:#a32637;background:#fff0f1;border:1px solid #f2c2c8}
.notice.info{color:#234a8c;background:#eaf2ff;border:1px solid #bcd2ff}
.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin-bottom:26px}
.stat-card{background:#fffffffa;padding:22px;border-radius:16px;border:1px solid #e0e8f3;box-shadow:0 8px 25px #172d4d14}
.stat-card span{display:block;color:#667b99;font-size:14px;margin-bottom:9px}
.stat-card strong{color:#172d4d;font-size:32px}
.panel{background:#fffffffa;border:1px solid #e0e8f3;border-radius:18px;padding:28px;margin-bottom:26px;box-shadow:0 10px 30px #172d4d17;min-width:0}
.panel-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:23px}
.panel-heading h2{color:#0a1d8f;font-size:23px;margin-bottom:8px}
.panel-heading p{font-size:14px}
.mode-badge{background:#eaf0ff;color:#3158d8;border-radius:30px;padding:8px 12px;font-size:12px;font-weight:800;white-space:nowrap}
.event-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 24px}
.form-group{min-width:0}
.full-width,.form-actions{grid-column:1/-1}
label{display:block;margin-bottom:8px;color:#253b58;font-size:14px;font-weight:700}
input[type=text],input[type=search],input[type=date],input[type=file],select,textarea{display:block;width:100%;max-width:100%;min-width:0;padding:12px 14px;border:1px solid #d6e0ef;border-radius:10px;background:white;color:#263750;font-family:inherit;font-size:14px;outline:none}
input[type=text],input[type=search],input[type=date],select{min-height:46px}
textarea{min-height:115px;resize:vertical}
input:focus,select:focus,textarea:focus{border-color:#3158d8;box-shadow:0 0 0 3px #3158d81f}
small{display:block;color:#7c8ba1;font-size:12px;margin-top:7px}
.current-poster{display:flex;align-items:center;gap:14px;margin-top:12px;color:#667b99;font-size:13px}
.current-poster img{width:100px;height:75px;object-fit:cover;border:1px solid #e0e8f3;border-radius:8px}
button,.button-link{display:inline-flex;justify-content:center;align-items:center;border:0;border-radius:9px;padding:12px 19px;font-family:inherit;font-size:14px;font-weight:700;text-decoration:none;cursor:pointer;transition:.2s}
.primary,button[type=submit]{background:#0a1d8f;color:white}
.primary:hover,button[type=submit]:hover{background:#071568}
.secondary{background:#e9eef3;color:#263750}
.button-link{background:#edf2fa;color:#233b60}
.form-actions{display:flex;flex-wrap:wrap;gap:10px}
.filters{display:grid;grid-template-columns:minmax(180px,1fr) minmax(140px,190px) auto auto;align-items:center;gap:12px;margin-bottom:20px}
.filters button,.filters .button-link{min-height:46px;white-space:nowrap}
.results-label{color:#71829b;font-size:13px;margin-bottom:14px}
.table-wrap{width:100%;overflow-x:auto;border:1px solid #e0e8f3;border-radius:12px}
table{width:100%;min-width:1050px;border-collapse:collapse;background:white}
thead{background:#f0f4fc}
th{color:#425675;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.4px}
th,td{padding:16px 14px;border-bottom:1px solid #e8edf5;text-align:left;vertical-align:middle}
td{color:#526681;font-size:13px;line-height:1.5}
tbody tr:last-child td{border-bottom:0}
tbody tr:hover{background:#fbfdff}
.event-name{color:#203b60;font-size:14px;font-weight:800}
.poster{display:block;width:76px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #e0e8f3}
.no-poster{display:inline-block;padding:12px 9px;border:1px dashed #ccd5e4;border-radius:8px;color:#8591a5;background:#f8faff;font-size:11px;white-space:nowrap}
.status-badge{display:inline-block;padding:6px 10px;border-radius:30px;font-size:11px;font-weight:800}
.status-open{background:#e5f7ec;color:#177245}
.status-closed{background:#fff2dc;color:#99620c}
.actions{display:flex;align-items:center;flex-wrap:wrap;gap:7px}
.actions button,.actions .button-link{padding:9px 12px;font-size:12px}
.actions .delete-btn{background:#fff0f1;color:#b42332}
.empty-state,.loading{padding:36px 20px;color:#71829b;text-align:center}
.loading{color:#3158d8;font-weight:600}
button:disabled{opacity:.6;cursor:wait}
footer{background:white;color:#555;text-align:center;padding:18px 10px;margin-top:auto;border-top:1px solid #e7edf5}
@media(max-width:1100px){.header{flex-wrap:wrap;justify-content:center}nav{flex-basis:100%;margin:5px 0}nav ul{flex-wrap:wrap}.filters{grid-template-columns:minmax(0,1fr) minmax(140px,190px)}.filters button{width:100%}}
@media(max-width:700px){.header{padding:12px 15px;gap:12px}.logo img{width:85px}nav{border-radius:18px;padding:8px}nav ul{gap:5px}nav a{font-size:13px;padding:9px 10px}main{width:100%;padding:24px 14px 32px}.page-heading h1{font-size:29px}.stats{gap:9px}.stat-card{padding:14px 10px}.stat-card span{font-size:11px}.stat-card strong{font-size:25px}.panel{padding:19px 15px;border-radius:14px}.event-form{grid-template-columns:minmax(0,1fr);gap:17px}.form-group,.full-width,.form-actions{grid-column:1}.filters{grid-template-columns:minmax(0,1fr)}.filters button{width:100%}}
@media(max-width:400px){.stats{grid-template-columns:1fr}.panel-heading{align-items:flex-start;flex-direction:column}.form-actions button,.form-actions .button-link{width:100%}}
</style>
</head>
<body>

<header class="header">
    <div class="logo">
        <img src="../../images/charusat.png" alt="CHARUSAT Logo">
    </div>
    <nav>
        <ul>
            <li><a href="../Admin.php">Admin Dashboard</a></li>
            <li><a href="events.php">Manage Events</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <section class="page-heading">
        <div class="eyebrow">Admin Panel</div>
        <h1>Event Management</h1>
        <p>Create, update and manage your campus events without reloading the page.</p>
    </section>

    <div id="notice" class="notice" role="status" aria-live="polite" hidden></div>

    <section class="stats">
        <article class="stat-card"><span>Total Events</span><strong id="totalCount">—</strong></article>
        <article class="stat-card"><span>Open Events</span><strong id="openCount">—</strong></article>
        <article class="stat-card"><span>Closed Events</span><strong id="closedCount">—</strong></article>
    </section>

    <section class="panel" id="event-form">
        <div class="panel-heading">
            <div>
                <h2 id="formTitle">Add Event</h2>
                <p id="formSubtitle">Fill in the details to create a campus event.</p>
            </div>
            <span class="mode-badge" id="modeBadge">ADD MODE</span>
        </div>

        <form class="event-form" id="eventForm">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION["event_csrf"]) ?>">
            <input type="hidden" name="event_id" id="event_id">

            <div class="form-group">
                <label for="event_name">Event Name *</label>
                <input type="text" id="event_name" name="event_name" maxlength="150" required placeholder="Enter event name">
            </div>

            <div class="form-group">
                <label for="event_date">Event Date *</label>
                <input type="date" id="event_date" name="event_date" required>
            </div>

            <div class="form-group">
                <label for="category">Event Category</label>
                <input type="text" id="category" name="category" maxlength="100" placeholder="Technical, Cultural, Sports...">
            </div>

            <div class="form-group">
                <label for="status">Event Status</label>
                <select id="status" name="status">
                    <option value="Open">Open</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="description">Event Description</label>
                <textarea id="description" name="description" maxlength="5000" rows="4" placeholder="Describe the event, venue and activities..."></textarea>
            </div>

            <div class="form-group full-width">
                <label for="poster">Event Poster</label>
                <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <small>Allowed formats: JPG, PNG and WebP. Maximum size: 2 MB.</small>
                <div class="current-poster" id="currentPoster" hidden>
                    <img id="currentPosterImage" alt="Current event poster">
                    <span>Current poster. Upload a new image to replace it.</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" id="saveButton">Add Event</button>
                <button type="button" id="resetButton" class="secondary">Reset</button>
                <button type="button" id="cancelButton" class="secondary" hidden>Cancel Edit</button>
            </div>
        </form>
    </section>

    <section class="panel" id="event-list">
        <div class="panel-heading">
            <div>
                <h2>All Events</h2>
                <p>View, search, edit and delete existing events.</p>
            </div>
            <span class="mode-badge" id="recordCount">Loading...</span>
        </div>

        <div class="filters">
            <input type="search" id="searchInput" placeholder="Search event or category..." aria-label="Search events">
            <select id="statusFilter" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="Open">Open events</option>
                <option value="Closed">Closed events</option>
            </select>
            <button type="button" id="searchButton">Search / Filter</button>
            <button type="button" id="clearButton" class="secondary">Clear</button>
        </div>

        <p class="results-label" id="resultsLabel">Loading events...</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Poster</th><th>Event Details</th>
                        <th>Date</th><th>Category</th><th>Status</th>
                        <th>Description</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="eventsBody">
                    <tr><td colspan="8"><div class="loading">Loading events...</div></td></tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer><p>&copy; <?= date("Y") ?> Student HUB Portal</p></footer>

<script>
"use strict";

const API_URL = "events_api.php";
const CSRF_TOKEN = <?= json_encode($_SESSION["event_csrf"]) ?>;

const form = document.getElementById("eventForm");
const body = document.getElementById("eventsBody");
const notice = document.getElementById("notice");
const saveButton = document.getElementById("saveButton");

let events = [];
let searchTimer;
let noticeTimer;
let requestSequence = 0;

function escapeHTML(value) {
    return String(value ?? "").replace(/[&<>"']/g, c => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    })[c]);
}

function showNotice(message, type = "success") {
    clearTimeout(noticeTimer);
    notice.textContent = message;
    notice.className = "notice " + type;
    notice.hidden = false;
    noticeTimer = setTimeout(() => notice.hidden = true, 5000);
}

/* Centralized Fetch and JSON error handling. */
async function apiRequest(url, options = {}) {
    let response;

    try {
        response = await fetch(url, {
            ...options,
            headers: {
                "Accept": "application/json",
                ...(options.headers || {})
            }
        });
    } catch {
        throw new Error("Network error. Check Apache and your connection.");
    }

    const raw = await response.text();
    let data;

    try {
        data = JSON.parse(raw);
    } catch {
        console.error("API URL:", url);
        console.error("HTTP status:", response.status);
        console.error("Raw API response:", raw);

        throw new Error(
            "Server returned non-JSON content. Open F12 Console for details."
        );
    }

    if (!response.ok || data.success !== true) {
        throw new Error(
            data.message || `Request failed (HTTP ${response.status}).`
        );
    }

    return data;
}

function renderEvents(items) {
    if (!items.length) {
        body.innerHTML = `<tr><td colspan="8">
            <div class="empty-state">No events found. Try another search or add an event.</div>
        </td></tr>`;
        return;
    }

    body.innerHTML = items.map(event => {
        const id = Number(event.event_id);
        const closed = event.status === "Closed";

        const poster = event.poster_url
            ? `<img class="poster" src="${escapeHTML(event.poster_url)}" alt="Event poster" loading="lazy">`
            : `<span class="no-poster">No poster</span>`;

        return `<tr>
            <td>${id}</td>
            <td>${poster}</td>
            <td><span class="event-name">${escapeHTML(event.event_name)}</span></td>
            <td>${escapeHTML(event.event_date)}</td>
            <td>${escapeHTML(event.category || "—")}</td>
            <td><span class="status-badge ${closed ? "status-closed" : "status-open"}">${escapeHTML(event.status || "Open")}</span></td>
            <td>${escapeHTML(event.description || "—")}</td>
            <td><div class="actions">
                <button type="button" class="button-link edit-btn" data-id="${id}">Edit</button>
                <button type="button" class="delete-btn" data-id="${id}">Delete</button>
            </div></td>
        </tr>`;
    }).join("");
}

async function loadEvents() {
    const sequence = ++requestSequence;
    const params = new URLSearchParams();
    const search = document.getElementById("searchInput").value.trim();
    const status = document.getElementById("statusFilter").value;

    if (search) params.set("search", search);
    if (status) params.set("status", status);

    body.innerHTML = `<tr><td colspan="8"><div class="loading">Loading events...</div></td></tr>`;
    document.getElementById("resultsLabel").textContent = "Loading events...";

    try {
        const data = await apiRequest(`${API_URL}?${params.toString()}`);

        if (sequence !== requestSequence) return;

        events = data.events;
        renderEvents(events);

        document.getElementById("totalCount").textContent = data.stats.total;
        document.getElementById("openCount").textContent = data.stats.open;
        document.getElementById("closedCount").textContent = data.stats.closed;
        document.getElementById("recordCount").textContent = `${events.length} RECORDS`;
        document.getElementById("resultsLabel").textContent =
            `Showing ${events.length} matching events`;
    } catch (error) {
        if (sequence !== requestSequence) return;

        body.innerHTML = `<tr><td colspan="8"><div class="empty-state">${escapeHTML(error.message)}</div></td></tr>`;
        document.getElementById("resultsLabel").textContent = "Could not load events.";
        showNotice(error.message, "error");
    }
}

function resetForm() {
    form.reset();
    document.getElementById("event_id").value = "";
    document.getElementById("formTitle").textContent = "Add Event";
    document.getElementById("formSubtitle").textContent =
        "Fill in the details to create a campus event.";
    document.getElementById("modeBadge").textContent = "ADD MODE";
    saveButton.textContent = "Add Event";
    document.getElementById("currentPoster").hidden = true;
    document.getElementById("currentPosterImage").removeAttribute("src");
    document.getElementById("cancelButton").hidden = true;
}

function startEdit(id) {
    const item = events.find(event => Number(event.event_id) === id);

    if (!item) {
        showNotice("Event not found in the current results. Refreshing.", "error");
        loadEvents();
        return;
    }

    document.getElementById("event_id").value = item.event_id;
    document.getElementById("event_name").value = item.event_name || "";
    document.getElementById("event_date").value = item.event_date || "";
    document.getElementById("category").value = item.category || "";
    document.getElementById("status").value = item.status || "Open";
    document.getElementById("description").value = item.description || "";
    document.getElementById("poster").value = "";

    document.getElementById("formTitle").textContent = "Update Event";
    document.getElementById("formSubtitle").textContent = "Update the event information below.";
    document.getElementById("modeBadge").textContent = "EDIT MODE";
    saveButton.textContent = "Update Event";
    document.getElementById("cancelButton").hidden = false;

    const box = document.getElementById("currentPoster");

    if (item.poster_url) {
        document.getElementById("currentPosterImage").src = item.poster_url;
        box.hidden = false;
    } else {
        document.getElementById("currentPosterImage").removeAttribute("src");
        box.hidden = true;
    }

    document.getElementById("event-form").scrollIntoView({
        behavior: "smooth",
        block: "start"
    });
}

form.addEventListener("submit", async event => {
    event.preventDefault();
    if (!form.reportValidity()) return;

    const id = document.getElementById("event_id").value;
    const data = new FormData(form);
    data.set("action", id ? "update" : "add");

    saveButton.disabled = true;
    saveButton.textContent = id ? "Updating..." : "Saving...";
    showNotice("Saving event. Please wait...", "info");

    try {
        const result = await apiRequest(API_URL, {
            method: "POST",
            headers: { "X-CSRF-Token": CSRF_TOKEN },
            body: data
        });

        showNotice(result.message);
        resetForm();
        await loadEvents();
    } catch (error) {
        showNotice(error.message, "error");
    } finally {
        saveButton.disabled = false;
        saveButton.textContent =
            document.getElementById("event_id").value
                ? "Update Event" : "Add Event";
    }
});

body.addEventListener("click", async event => {
    const editButton = event.target.closest(".edit-btn");
    const deleteButton = event.target.closest(".delete-btn");

    if (editButton) {
        startEdit(Number(editButton.dataset.id));
        return;
    }

    if (!deleteButton) return;

    const id = Number(deleteButton.dataset.id);

    if (!Number.isInteger(id) || id < 1) {
        showNotice("Invalid event ID.", "error");
        return;
    }

    if (!confirm("Are you sure you want to delete this event?")) return;

    deleteButton.disabled = true;
    deleteButton.textContent = "Deleting...";

    const data = new FormData();
    data.set("action", "delete");
    data.set("event_id", String(id));
    data.set("csrf_token", CSRF_TOKEN);

    try {
        const result = await apiRequest(API_URL, {
            method: "POST",
            headers: { "X-CSRF-Token": CSRF_TOKEN },
            body: data
        });

        showNotice(result.message);

        if (Number(document.getElementById("event_id").value) === id) {
            resetForm();
        }

        await loadEvents();
    } catch (error) {
        showNotice(error.message, "error");
        deleteButton.disabled = false;
        deleteButton.textContent = "Delete";
    }
});

/* Debounced AJAX search. */
document.getElementById("searchInput").addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadEvents, 350);
});

document.getElementById("statusFilter").addEventListener("change", loadEvents);
document.getElementById("searchButton").addEventListener("click", loadEvents);

document.getElementById("clearButton").addEventListener("click", () => {
    document.getElementById("searchInput").value = "";
    document.getElementById("statusFilter").value = "";
    loadEvents();
});

document.getElementById("resetButton").addEventListener("click", resetForm);
document.getElementById("cancelButton").addEventListener("click", resetForm);

loadEvents();
</script>
</body>
</html>
