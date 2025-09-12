<button class="menu-toggle" onclick="toggleSidebar()"><i class="bx bx-menu"></i></button>
<div class="sidebar hidden" id="sidebar">
    <button class="close-btn" onclick="toggleSidebar()"><i class="bx bx-x"></i></button>
    <div class="side-header">
        <a href="/admin/"><img src="/images/SAFE-logo.png" alt="SAFE logo"></a>
        <h2>Admin SAFE</h2>
    </div>
    <hr>
    <ul>
        <li><a href="/admin/termin/"><i class='bx  bx-calendar-star'></i>Termín</a></li>
        <li><a href="/admin/pozvanka/"><i class='bx  bx-party'></i>Pozvánka</a></li>
        <hr>
        <li><a href="/admin/udalosti/"><i class='bx bx-calendar-event'></i>Události</a></li>
        <li><a href="/admin/program/"><i class='bx bxs-spreadsheet'></i>Program</a></li>
        <hr>
        <li><a href="/admin/EN/udalosti/"><i class='bx bx-calendar-event'></i>EN - Události</a></li>
        <li><a href="/admin/EN/program/"><i class='bx bxs-spreadsheet'></i>EN - Program</a></li>
    </ul>
    <a href="/admin/logout/" class="logout" onclick="confirmLogout(event)">
        <i class='bx bx-user-x'></i>Odhlásit se
    </a>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.toggle('hidden');
        } else {
            console.error('Element #sidebar nebyl nalezen.');
        }
    }

    function confirmLogout(event) {
        event.preventDefault();
        const confirmAction = confirm("Opravdu se chcete odhlásit?");
        if (confirmAction) {
            window.location.href = "/admin/logout/";
        }
    }
</script>
