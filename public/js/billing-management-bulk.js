document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('selectAll');
    const rowChecks = Array.from(document.querySelectorAll('.row-check'));
    const bulkButton = document.getElementById('bulkGenerateBtn');
    const bulkForm = document.getElementById('bulkGenerateForm');

    if (!bulkButton || !rowChecks.length) {
        return;
    }

    function syncButtonState() {
        const eligible = rowChecks.filter(cb => !cb.disabled && cb.checked);
        bulkButton.disabled = eligible.length === 0;
        if (bulkForm) {
            const existing = bulkForm.querySelectorAll('input[name="ids[]"]');
            existing.forEach(el => el.remove());
            eligible.forEach(cb => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'ids[]';
                hidden.value = cb.value;
                bulkForm.appendChild(hidden);
            });
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            rowChecks.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = selectAll.checked;
                }
            });
            syncButtonState();
        });
    }

    rowChecks.forEach(cb => cb.addEventListener('change', () => {
        if (!cb.checked && selectAll) {
            selectAll.checked = false;
        } else if (selectAll) {
            const allEligibleChecked = rowChecks.filter(item => !item.disabled).every(item => item.checked);
            selectAll.checked = allEligibleChecked;
        }
        syncButtonState();
    }));

    syncButtonState();
});
