/* Native form usability and authenticated dashboard table interactions. */
(() => {
    'use strict';
    document.querySelectorAll('[data-reveal]').forEach(button => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.reveal);
        input.type = input.type === 'password' ? 'text' : 'password';
        button.textContent = input.type === 'password' ? 'Show' : 'Hide';
    }));
    document.querySelectorAll('form').forEach(form => form.addEventListener('submit', event => {
        if (!event.submitter?.formNoValidate && !form.checkValidity()) return;
        if (event.submitter) window.setTimeout(() => { event.submitter.disabled = true; event.submitter.textContent = 'Please wait…'; }, 0);
    }));
    const timer = document.querySelector('[data-otp-expires]');
    if (timer) {
        const tick = () => {
            const seconds = Math.max(0, Number(timer.dataset.otpExpires) - Math.floor(Date.now() / 1000));
            timer.textContent = seconds ? `${String(Math.floor(seconds / 60)).padStart(2,'0')}:${String(seconds % 60).padStart(2,'0')}` : 'OTP request expired. Reload to request another OTP.';
        };
        tick(); window.setInterval(tick, 1000);
    }
    const table = document.querySelector('[data-dashboard-table]');
    if (!table) return;
    const rows = () => [...table.tBodies[0].rows];
    document.querySelector('[data-table-search]').addEventListener('input', event => {
        const query = event.target.value.toLocaleLowerCase();
        rows().forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase().includes(query); });
        document.querySelector('[data-row-count]').textContent = `${rows().filter(row => !row.hidden).length} records`;
    });
    table.querySelectorAll('[data-sort-column]').forEach((button, index) => {
        let ascending = true;
        button.addEventListener('click', () => {
            rows().sort((a,b) => a.cells[index].textContent.localeCompare(b.cells[index].textContent, undefined, { numeric:true }) * (ascending ? 1 : -1)).forEach(row => table.tBodies[0].append(row));
            table.querySelectorAll('th').forEach(th => th.removeAttribute('aria-sort'));
            button.closest('th').setAttribute('aria-sort', ascending ? 'ascending' : 'descending');
            ascending = !ascending;
        });
    });
    document.querySelector('[data-export-table]').addEventListener('click', () => {
        const quote = value => `"${(/^[=+@\-\t\r]/.test(value) ? "'" : '') + value.replaceAll('"','""')}"`;
        const csv = [table.tHead.rows[0], ...rows().filter(row => !row.hidden)].map(row => [...row.cells].map(cell => quote(cell.textContent.trim())).join(',')).join('\r\n');
        const url = URL.createObjectURL(new Blob(['\ufeff',csv], { type:'text/csv;charset=utf-8' }));
        const link = document.createElement('a'); link.href = url; link.download = 'micada-dashboard.csv'; link.click();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
    document.querySelector('[data-print-table]').addEventListener('click', () => window.print());
})();
