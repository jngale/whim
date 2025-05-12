console.log("🚀 whim.js has been loaded and is running.");

const StatusModal = {
    get modal() {
      return document.getElementById("status-modal");
    },
    get title() {
      return document.getElementById("status-modal-title");
    },
    get log() {
      return document.getElementById("status-modal-log");
    },
  
    show(title, lines = []) {
      this.clear(title);
      lines.forEach(line => this.append(line));
      this.setSpinner(false);
      this.modal?.classList.add("active");
    },
  
    append(line) {
      if (this.log) {
        this.log.textContent += line + "\n";
        this.log.scrollTop = this.log.scrollHeight;
      }
    },
  
    clear(title = "Status") {
      if (this.title) this.title.textContent = title;
      if (this.log) this.log.textContent = "";
    },
  
    setSpinner(visible) {
      const spinner = document.getElementById("status-modal-spinner");
      if (spinner) spinner.style.display = visible ? "inline-block" : "none";
    },
  
    hide() {
      this.modal?.classList.remove("active");
    }
  };
  


document.addEventListener('DOMContentLoaded', () => {

    // User Switch Handling
    const userSelect = document.getElementById('user-select');
    if (userSelect) {
        userSelect.addEventListener('change', async (e) => {
            const selected = e.target.value;

            try {
                const response = await fetch('ajax/switch_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: selected })
                });

                const result = await response.json();

                if (!result.success) {
                    alert("❌ Error switching user: " + (result.error ?? "Unknown error"));
                    return;
                }

                // 🧹 Manually update cookie immediately (precaution)
                document.cookie = `whim_active_user=${selected}; path=/; max-age=31536000; SameSite=Strict`;

                console.log("✅ User switched, reloading...");
                window.location.href = window.location.href; // full reload to re-trigger PHP

            } catch (err) {
                console.error("❌ Failed to switch user:", err);
                alert("❌ Failed to switch user: " + err.message);
            }
        });
    }

    // Tab Behavior
    const tabs = document.querySelectorAll(".tablink");
    const contents = document.querySelectorAll(".tabcontent");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            const targetId = tab.dataset.tab;

            contents.forEach(c => c.style.display = "none");
            tabs.forEach(t => t.classList.remove("active"));

            const content = document.getElementById(targetId);
            if (content) content.style.display = "block";

            tab.classList.add("active");

            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', targetId);
            history.replaceState(null, '', newUrl);
        });
    });

    const initiallyActive = document.querySelector(".tablink.active");
    if (initiallyActive) {
        const tab = initiallyActive.dataset.tab;
        const content = document.getElementById(tab);
        if (content) content.style.display = "block";
    } else {
        const fallback = document.querySelector(".tablink[data-tab='projects']");
        if (fallback) fallback.click();
    }
});

// Modal Open/Close
window.openModal = function() {
    const modal = document.getElementById('projectModal');
    if (modal) modal.classList.add('active');
};

window.closeModal = function() {
    const modal = document.getElementById('projectModal');
    if (modal) modal.classList.remove('active');
};

// Open VS Code Handler
window.openVSCode = function(projectName) {
    fetch('ajax/open_vscode.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: projectName })
    })
    .then(res => {
        if (!res.ok) throw new Error('Error: ' + res.status);
        return res.text();
    })
    .catch(err => {
        alert("Failed to open VS Code: " + err.message);
    });
};

// Run Deploy Actions
function runProjectAction(project, action) {
    const messageBox = document.getElementById(`message-${project}`);
    if (messageBox) {
        messageBox.textContent = "Running " + action + "...";
    }

    fetch('tabs/deploy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `project=${encodeURIComponent(project)}&action=${encodeURIComponent(action)}`
    })
    .then(response => response.text())
    .then(result => {
        if (messageBox) {
            messageBox.innerHTML = `<pre>${result}</pre>`;
        }
    })
    .catch(error => {
        if (messageBox) {
            messageBox.textContent = "Error: " + error;
        }
    });
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);

    // Trigger animation
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => container.removeChild(toast), 400);
    }, 10000);
}





