document.addEventListener('DOMContentLoaded', () => {
    const saveBtn = document.getElementById('save-meta-btn');
    if (!saveBtn) return;

    saveBtn.addEventListener('click', async () => {
        const form = document.getElementById('meta-form');
        const formData = new FormData(form);

        const response = await fetch('ajax/metadata_save.php', {
            method: 'POST',
            body: formData
        });

        const status = document.getElementById('save-status');
        if (!response.ok) {
            status.innerText = '❌ Save failed';
            return;
        }

        const result = await response.json();
        if (result.success) {
            status.innerText = `✅ Metadata updated`;
        } else {
            status.innerText = '❌ Save error: ' + result.error;
        }
    });
});
