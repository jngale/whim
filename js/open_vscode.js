// open_vscode.js
document.addEventListener("DOMContentLoaded", () => {
    window.openVSCode = function(projectName) {
        console.log("🖥️ Attempting to open VS Code for project:", projectName);

        fetch('ajax/open_vscode.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: projectName })
        })
        .then(res => {
            console.log("📡 Received response:", res.status, res.statusText);
            if (!res.ok) throw new Error('Error: ' + res.status);
            return res.text();
        })
        .then(responseText => {
            console.log("✅ Server response:", responseText);
        })
        .catch(err => {
            console.error("❌ Fetch failed:", err);
            alert("❌ Failed to open VS Code: " + err.message);
        });
    };
});
