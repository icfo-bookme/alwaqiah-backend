
(function () {
    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('collapseBtn');
    const collapseIcon = document.getElementById('collapseIcon');
    const navTooltip = document.getElementById('navTooltip');
    let collapsed = false;

    /* ---- Collapse toggle ---- */
    collapseBtn.addEventListener('click', () => {
        collapsed = !collapsed;
        if (collapsed) {
            sidebar.classList.add('collapsed');
            sidebar.style.width = '60px';
            collapseIcon.className = 'fas fa-chevron-right text-[10px]';
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.style.width = '224px'; // w-56
            collapseIcon.className = 'fas fa-chevron-left text-[10px]';
        }
    });

    /* ---- Submenu toggles ---- */
    document.querySelectorAll('.has-sub').forEach(btn => {
        btn.addEventListener('click', function () {
            if (collapsed) return;
            const subId = this.dataset.sub;
            const sub = document.getElementById(subId);
            const isOpen = sub.classList.contains('open');

            // Close all
            document.querySelectorAll('.submenu').forEach(s => s.classList.remove('open'));
            document.querySelectorAll('.has-sub').forEach(b => b.classList.remove('open'));

            if (!isOpen) {
                sub.classList.add('open');
                this.classList.add('open');
            }
        });
    });

    /* ---- Tooltip on collapsed ---- */
    document.querySelectorAll('.nav-item[data-label], .has-sub[data-label]').forEach(el => {
        el.addEventListener('mouseenter', function (e) {
            if (!collapsed) return;
            navTooltip.textContent = this.dataset.label;
            const rect = this.getBoundingClientRect();
            navTooltip.style.top = (rect.top + rect.height / 2 - 11) + 'px';
            navTooltip.classList.add('show');
        });
        el.addEventListener('mouseleave', () => navTooltip.classList.remove('show'));
    });

    /* ---- Sidebar Search ---- */
    const searchInput = document.getElementById('sidebarSearch');
    const searchClear = document.getElementById('searchClear');
    const searchEmptyMsg = document.getElementById('searchEmptyMsg');

    // Build a flat list of all searchable nav items
    // Each entry: { el, label, parentGroup (optional) }
    function getAllNavItems() {
        const items = [];
        // Top-level single nav items (a.nav-item with data-label)
        document.querySelectorAll('#sideNav a.nav-item[data-label]').forEach(el => {
            items.push({
                el,
                label: el.dataset.label,
                group: null
            });
        });
        // Top-level submenu buttons
        document.querySelectorAll('#sideNav button.has-sub[data-label]').forEach(btn => {
            items.push({
                el: btn,
                label: btn.dataset.label,
                group: null,
                subId: btn.dataset.sub
            });
        });
        // Sub items — each a tag inside .submenu
        document.querySelectorAll('#sideNav .submenu a').forEach(el => {
            const label = el.querySelector('span') ? el.querySelector('span').textContent.trim() : '';
            const subEl = el.closest('.submenu');
            const parentBtn = subEl ? document.querySelector(`[data-sub="${subEl.id}"]`) : null;
            items.push({
                el,
                label,
                group: subEl,
                parentBtn
            });
        });
        return items;
    }

    let searchDebounce;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            const term = this.value.trim().toLowerCase();

            // Show/hide clear button
            searchClear.classList.toggle('hidden', !term);

            if (!term) {
                resetSearch();
                return;
            }

            const allItems = getAllNavItems();
            let anyMatch = false;

            // Hide everything first
            document.querySelectorAll('#sideNav > *').forEach(el => el.style.display = 'none');
            document.querySelectorAll('#sideNav .submenu').forEach(s => {
                s.classList.remove('open');
                s.style.display = '';
            });

            allItems.forEach(item => {
                if (item.label.toLowerCase().includes(term)) {
                    anyMatch = true;
                    item.el.style.display = '';

                    if (item.group && item.parentBtn) {
                        // Show parent group wrapper div
                        const wrapper = item.parentBtn.closest('div');
                        if (wrapper) wrapper.style.display = '';
                        item.parentBtn.style.display = '';
                        item.group.style.display = '';
                        item.group.classList.add('open');
                    } else if (item.subId) {
                        // It's a parent submenu button — show it and its sub
                        const wrapper = item.el.closest('div');
                        if (wrapper) wrapper.style.display = '';
                        const sub = document.getElementById(item.subId);
                        if (sub) {
                            sub.style.display = '';
                            sub.classList.add('open');
                        }
                    }
                }
            });

            searchEmptyMsg.classList.toggle('hidden', anyMatch);
        }, 140);
    });

    searchClear.addEventListener('click', () => {
        searchInput.value = '';
        searchClear.classList.add('hidden');
        searchEmptyMsg.classList.add('hidden');
        resetSearch();
    });

    // Escape key clears
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && searchInput.value) {
            searchInput.value = '';
            searchClear.classList.add('hidden');
            searchEmptyMsg.classList.add('hidden');
            resetSearch();
        }
    });

    function resetSearch() {
        // Restore all items visibility
        document.querySelectorAll('#sideNav > *').forEach(el => el.style.display = '');
        document.querySelectorAll('#sideNav a, #sideNav button').forEach(el => el.style.display = '');
        // Collapse all submenus back
        document.querySelectorAll('.submenu').forEach(s => {
            s.classList.remove('open');
            s.style.display = '';
        });
        document.querySelectorAll('.has-sub').forEach(b => b.classList.remove('open'));
        searchEmptyMsg.classList.add('hidden');
    }
})();
