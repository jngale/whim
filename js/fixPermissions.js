function fixPermissions(project) {
    if (!confirm(`Fix permissions for ${project}?`)) return;

    fetch(`/ajax/fix_permissions.php?project=${encodeURIComponent(project)}`, {
        method: 'POST'
    })
    .then(res => res.text())
    .then(text => alert(text))
    .catch(err => alert("❌ Failed to fix permissions: " + err));
}
