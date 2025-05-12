document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ create_project.js loaded");

    const form = document.getElementById("create-project-form");
    if (!form) {
        console.warn("⚠️ create-project-form not found.");
        return;
    }

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const button = document.getElementById("create-project-button");
        const spinner = button.querySelector(".button-spinner");

        if (!button || !spinner) {
            console.error("❌ Button or spinner not found");
            return;
        }

        console.log("🌀 Before submit: Spinner classes", spinner.classList);
        button.disabled = true;
        spinner.classList.remove("hidden");
        console.log("🌀 After removing hidden: Spinner classes", spinner.classList);

        // Convert form data to plain object
        const formData = new FormData(this);
        const values = {};
        for (const [key, val] of formData.entries()) {
            if (!key) continue;
            if (typeof val === 'string') {
                values[key] = val.trim();
            } else {
                values[key] = val;
            }
        }

        console.log("📤 Collected values", values);

        const projectName = values.name;
        if (!projectName) {
            showToast("❌ Project name is required.", 'error');
            spinner.classList.add("hidden");
            button.disabled = false;
            return;
        }

        try {
            // Check if project already exists
            const checkRes = await fetch(`ajax/check_project_exists.php?name=${encodeURIComponent(projectName)}`);
            const checkJson = await checkRes.json();

            if (checkJson.exists) {
                showToast(`❌ A project named "${projectName}" already exists.`, 'error');
                spinner.classList.add("hidden");
                button.disabled = false;
                return;
            }

            // Create project
            const res = await fetch("ajax/create_project.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(values)
            });

            const json = await res.json();

            if (!json.success) {
                console.error("❌ Error creating project", json.error ?? "(no error message)");
                showToast("❌ Project creation failed: " + (json.error || "Unknown error"), 'error');
                spinner.classList.add("hidden");
                button.disabled = false;
                return;
            }

            console.log("✅ Project created successfully!");
            showToast("✅ Project created successfully", 'success');

            closeModal();
            window.location.reload();

        } catch (err) {
            console.error("❌ Network error:", err);
            showToast("❌ Request failed: " + err.message, 'error');
        } finally {
            spinner.classList.add("hidden");
            button.disabled = false;
        }
    });
});
